<?php

class FactureController extends Controller
{
    /**
     * Page principale de facturation
     * Route : GET /facturation
     */
    public function index()
    {
        $this->requireLogin();
        date_default_timezone_set('Europe/Paris');

        $tous_les_proprios = Client::getAllProprietairesAvecAnimaux();

        $id_proprio = isset($_GET['client']) ? (int)$_GET['client'] : 0;
        $proprio = null;
        $animaux = [];
        $historique = [];

        if ($id_proprio > 0) {
            $proprio = Client::findProprietaire($id_proprio);
            if ($proprio) {
                $animaux = Client::getAnimauxByProprietaire($id_proprio);
                $historique = Soin::findByProprietaire($id_proprio);
            }
        }

        $this->view('facturation_view', compact('tous_les_proprios', 'id_proprio', 'proprio', 'animaux', 'historique'));
    }

    /**
     * API JSON : retourne les animaux d'un propriétaire
     * Route : GET /facturation/animaux/{id}
     */
    public function animaux($id = 0)
    {
        $this->requireLogin();

        $id = (int)$id;
        if ($id <= 0) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $animaux = Client::getAnimauxByProprietaire($id);

        header('Content-Type: application/json');
        echo json_encode($animaux);
        exit;
    }

    /**
     * Enregistre les prestations multi-animaux et génère la facture
     * Route : POST /facturation
     *
     * Format POST attendu :
     * - animaux[ID][id_animal] = ID
     * - animaux[ID][type_soin][] = "Bain Brush", "Coupe", ...
     * - animaux[ID][notes] = "..."
     * - ventes_globales[type][] = "Vente Jouet (12.50€)"
     * - ventes_globales[prix][] = "12.50"
     * - remises_globales[type][] = "Remise fidélité (-5.00€)"
     * - remises_globales[prix][] = "-5.00"
     * - prix = prix total global
     * - mode_paiement = CB / Chèque / Espèces
     * - date_soin = 2026-02-08
     */
    public function store()
    {
        $this->requireLogin();

        $animauxData = $_POST['animaux'] ?? [];
        if (empty($animauxData) || !is_array($animauxData)) {
            redirect('facturation.index');
            exit;
        }

        // Données globales
        $date         = $_POST['date_soin'] ?? date('Y-m-d');
        $modePaiement = trim($_POST['mode_paiement'] ?? '');

        // Ventes globales (ajoutées au premier animal)
        $ventesGlobales = $_POST['ventes_globales'] ?? [];
        $ventesTypes = $ventesGlobales['type'] ?? [];
        $ventesPrix  = $ventesGlobales['prix'] ?? [];
        $totalVentes = 0;
        foreach ((array)$ventesPrix as $vp) {
            $totalVentes += (float)str_replace(',', '.', (string)$vp);
        }

        // Remises globales (montants négatifs)
        $remisesGlobales = $_POST['remises_globales'] ?? [];
        $remisesTypes = $remisesGlobales['type'] ?? [];
        $remisesPrix  = $remisesGlobales['prix'] ?? [];
        $totalRemises = 0;
        foreach ((array)$remisesPrix as $rp) {
            $val = (float)str_replace(',', '.', (string)$rp);
            // Sécurité: une remise doit être négative
            if ($val > 0) {
                $val = -$val;
            }
            $totalRemises += $val;
        }

        $prestationIds = [];
        $id_proprio = 0;
        $firstAnimalId = 0;

        foreach ($animauxData as $animalId => $aData) {
            $idAnimal = (int)($aData['id_animal'] ?? 0);
            if ($idAnimal <= 0) continue;

            if ($id_proprio === 0) {
                $id_proprio = $this->getProprioFromAnimal($idAnimal);
            }
            if ($firstAnimalId === 0) {
                $firstAnimalId = $idAnimal;
            }

            // Soins de cet animal (sans les ventes)
            $types = !empty($aData['type_soin']) ? implode(", ", (array)$aData['type_soin']) : "Soin divers";
            $notes = trim((string)($aData['notes'] ?? ''));

            // Nouveau format recommandé : 2 champs séparés (heures + minutes)
            $dureeHeures = isset($aData['duree_heures']) ? (int)$aData['duree_heures'] : 0;
            $dureeMinutesPart = isset($aData['duree_minutes_part']) ? (int)$aData['duree_minutes_part'] : 0;
            if ($dureeHeures < 0) $dureeHeures = 0;
            if ($dureeMinutesPart < 0) $dureeMinutesPart = 0;
            if ($dureeMinutesPart > 59) $dureeMinutesPart = 59;

            $dureeMinutes = ($dureeHeures * 60) + $dureeMinutesPart;

            // Compatibilité: ancien champ texte (1h30 / minutes)
            if ($dureeMinutes <= 0) {
                $dureeRaw = trim((string)($aData['duree_minutes'] ?? ''));
                if ($dureeRaw !== '') {
                    if (preg_match('/^(\d+)\s*h\s*(\d{1,2})?$/i', $dureeRaw, $mDureeHeure)) {
                        $heures = (int)$mDureeHeure[1];
                        $minutes = isset($mDureeHeure[2]) ? (int)$mDureeHeure[2] : 0;
                        if ($minutes > 59) $minutes = 59;
                        $dureeMinutes = ($heures * 60) + $minutes;
                    } elseif (preg_match('/^(\d+)$/', $dureeRaw, $mDureeMin)) {
                        $dureeMinutes = (int)$mDureeMin[1];
                    }
                }
            }


            // Prix individuel de cet animal
            $prixAnimal = (float)str_replace(',', '.', (string)($aData['prix'] ?? '0'));

            if ($prixAnimal <= 0) continue;

            $lastId = Prestation::create([
                'id_animal'     => $idAnimal,
                'date_soin'     => $date,
                'type_soin'     => $types,
                'prix'          => $prixAnimal,
                'mode_paiement' => $modePaiement ?: null,
                'notes'         => $notes,
                'duree_minutes' => $dureeMinutes > 0 ? $dureeMinutes : null,
            ]);

            $prestationIds[] = $lastId;
        }

        // Créer une prestation séparée pour les ventes (rattachée au 1er animal pour la BDD)
        if (!empty($ventesTypes) && $totalVentes > 0 && $firstAnimalId > 0) {
            $ventesLabel = implode(", ", (array)$ventesTypes);
            $venteId = Prestation::create([
                'id_animal'     => $firstAnimalId,
                'date_soin'     => $date,
                'type_soin'     => $ventesLabel,
                'prix'          => $totalVentes,
                'mode_paiement' => $modePaiement ?: null,
                'notes'         => 'Vente produit',
            ]);
            $prestationIds[] = $venteId;
        }

        // Créer une prestation séparée pour les remises (rattachée au 1er animal)
        if (!empty($remisesTypes) && $totalRemises != 0.0 && $firstAnimalId > 0 && !empty($prestationIds)) {
            $remisesLabel = 'Remise commerciale';
            $remiseId = Prestation::create([
                'id_animal'     => $firstAnimalId,
                'date_soin'     => $date,
                'type_soin'     => $remisesLabel,
                'prix'          => $totalRemises,
                'mode_paiement' => $modePaiement ?: null,
                'notes'         => 'Remise commerciale',
            ]);
            $prestationIds[] = $remiseId;
        }

        if (empty($prestationIds)) {
            redirect('facturation.index', [], ['client' => $id_proprio]);
            exit;
        }

        // Générer la facture sur la première prestation (la facture PDF regroupe via le groupe)
        // On stocke le groupe en session pour que InvoiceController puisse les récupérer
        if (count($prestationIds) > 1) {
            $_SESSION['facture_groupe'] = $prestationIds;
        }

        redirect('invoices.generate', ['id' => $prestationIds[0]], ['from' => 'facturation']);
    }

    private function getProprioFromAnimal(int $idAnimal): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id_proprietaire FROM Animaux WHERE id_animal = ?");
        $stmt->execute([$idAnimal]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_proprietaire'] : 0;
    }
}
