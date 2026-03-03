<?php

class PrestationController extends Controller
{
    public function store($id = 0)
    {
        $this->requireLogin();

        $id_animal = (int)$id;
        if ($id_animal <= 0) {
            die("❌ Erreur : id_animal manquant.");
        }

        // Validation des champs
        $prix = str_replace(',', '.', (string)($_POST['prix'] ?? '0'));
        $prix = (float)$prix;

        // Ajouter le prix des ventes additionnelles
        $ventePrix = $_POST['vente_prix'] ?? [];
        foreach ((array)$ventePrix as $vp) {
            $prix += (float)str_replace(',', '.', (string)$vp);
        }

        if ($prix <= 0) {
            die("❌ Erreur : Le prix doit être supérieur à 0 pour générer une facture.");
        }

        $types          = !empty($_POST['type_soin']) ? implode(", ", (array)$_POST['type_soin']) : "Soin divers";
        $notes          = $_POST['notes'] ?? '';
        $date           = $_POST['date_soin'] ?? date('Y-m-d');
        $modePaiement   = trim($_POST['mode_paiement'] ?? '');

        // Appel au modèle pour l'insertion
        $lastId = Prestation::create([
            'id_animal'      => $id_animal,
            'date_soin'      => $date,
            'type_soin'      => $types,
            'prix'           => $prix,
            'mode_paiement'  => $modePaiement ?: null,
            'notes'          => $notes,
        ]);

        // Redirection vers la génération de facture (route MVC)
        redirect('invoices.generate', ['id' => $lastId, 'animal' => $id_animal]);
    }

    public function updateNotes($id = 0)
    {
        $this->requireLogin();

        $idPrestation = (int)$id;
        if ($idPrestation <= 0) {
            redirect('home');
            exit;
        }

        $notes = (string)($_POST['notes'] ?? '');
        Prestation::updateNotes($idPrestation, $notes);

        $back = $_SERVER['HTTP_REFERER'] ?? route('home');
        header('Location: ' . $back);
        exit;
    }
}
