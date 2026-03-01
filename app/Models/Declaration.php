<?php

class Declaration
{
    private const URSSAF_STATUS_EN_COURS = 'en_cours';
    private const URSSAF_STATUS_PRELEVE = 'preleve';

    /**
     * Récupère le chiffre d'affaires groupé par type de soin pour un mois donné
     * @param int $mois (1-12)
     * @param int $annee (ex: 2025)
     * @return array [['type' => 'Bain Brush', 'total' => 450.00], ...]
     */
    public static function getCAParType(int $mois, int $annee): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT type_soin, prix
                FROM Prestations
                WHERE MONTH(date_soin) = :mois
                  AND YEAR(date_soin)  = :annee
                ORDER BY date_soin";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['mois' => $mois, 'annee' => $annee]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Décomposer chaque prestation (une ligne peut contenir "Bain Brush, Tonte, Vente Jouet (8.50€)")
        $totaux = [];
        foreach ($rows as $row) {
            $types = explode(', ', $row['type_soin'] ?? '');
            $nbTypes = count($types);
            // Séparer les ventes (qui ont leur propre prix) des soins
            $ventes = [];
            $soins = [];
            foreach ($types as $t) {
                $t = trim($t);
                if ($t === '') continue;
                if (strpos($t, 'Vente') === 0) {
                    // Extraire le prix de la vente si présent ex: "Vente Jouet (8.50€)"
                    if (preg_match('/\((\d+[\.,]\d+)€?\)/', $t, $m)) {
                        $prixVente = (float)str_replace(',', '.', $m[1]);
                        // Nettoyer le label
                        $label = preg_replace('/\s*\(\d+[\.,]\d+€?\)/', '', $t);
                        $ventes[] = ['label' => trim($label), 'prix' => $prixVente];
                    } else {
                        $soins[] = $t;
                    }
                } else {
                    $soins[] = $t;
                }
            }

            // Répartir le prix de la prestation (hors ventes) entre les soins
            $prixTotal = (float)$row['prix'];
            $prixVentesTotal = 0;
            foreach ($ventes as $v) {
                $prixVentesTotal += $v['prix'];
                if (!isset($totaux[$v['label']])) $totaux[$v['label']] = 0;
                $totaux[$v['label']] += $v['prix'];
            }

            $prixSoins = $prixTotal - $prixVentesTotal;
            $nbSoins = count($soins);
            if ($nbSoins > 0 && $prixSoins > 0) {
                $prixParSoin = $prixSoins / $nbSoins;
                foreach ($soins as $s) {
                    if (!isset($totaux[$s])) $totaux[$s] = 0;
                    $totaux[$s] += $prixParSoin;
                }
            }
        }

        // Regrouper en 2 grandes catégories : Toilettage et Ventes
        $totalToilettage = 0;
        $totalVentes = 0;
        foreach ($totaux as $type => $total) {
            if (strpos($type, 'Vente') === 0) {
                $totalVentes += $total;
            } else {
                $totalToilettage += $total;
            }
        }

        $result = [];
        if ($totalToilettage > 0) {
            $result[] = ['type' => '✂️ Toilettage', 'total' => round($totalToilettage, 2)];
        }
        if ($totalVentes > 0) {
            $result[] = ['type' => '🛒 Ventes', 'total' => round($totalVentes, 2)];
        }

        return $result;
    }

    /**
     * Récupère le CA total pour un mois donné
     */
    public static function getCATotalMois(int $mois, int $annee): float
    {
        $pdo = Database::getConnection();

        $sql = "SELECT COALESCE(SUM(prix), 0) as total
                FROM Prestations
                WHERE MONTH(date_soin) = :mois
                  AND YEAR(date_soin)  = :annee";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['mois' => $mois, 'annee' => $annee]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Récupère le nombre de prestations pour un mois donné
     */
    public static function getNbPrestationsMois(int $mois, int $annee): int
    {
        $pdo = Database::getConnection();

        $sql = "SELECT COUNT(*) FROM Prestations
                WHERE MONTH(date_soin) = :mois
                  AND YEAR(date_soin)  = :annee";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['mois' => $mois, 'annee' => $annee]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Retourne le CA par mois pour une annee donnee.
     * Format: [1 => 120.50, 2 => 0.0, ..., 12 => 890.00]
     */
    public static function getCAMensuel(int $annee): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT MONTH(date_soin) AS mois, COALESCE(SUM(prix), 0) AS total
                FROM Prestations
                WHERE YEAR(date_soin) = :annee
                GROUP BY MONTH(date_soin)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['annee' => $annee]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totals = array_fill(1, 12, 0.0);
        foreach ($rows as $row) {
            $mois = (int)($row['mois'] ?? 0);
            if ($mois >= 1 && $mois <= 12) {
                $totals[$mois] = round((float)($row['total'] ?? 0), 2);
            }
        }

        return $totals;
    }

    /**
     * Lit les statuts de prelevement URSSAF pour une annee.
     * Format: [1 => 'en_cours', 2 => 'preleve', ...]
     */
    public static function getUrssafStatutsParAnnee(int $annee): array
    {
        self::ensureUrssafTableExists();

        $pdo = Database::getConnection();
        $sql = "SELECT mois, statut
                FROM DeclarationUrssaf
                WHERE annee = :annee";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['annee' => $annee]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $mois = (int)($row['mois'] ?? 0);
            $statut = self::normalizeUrssafStatut((string)($row['statut'] ?? ''));
            if ($mois >= 1 && $mois <= 12 && $statut !== '') {
                $result[$mois] = $statut;
            }
        }

        return $result;
    }

    /**
     * Sauvegarde les statuts URSSAF pour les mois transmis.
     * $statuts format attendu: [1 => 'en_cours', 2 => 'preleve', ...]
     */
    public static function saveUrssafStatutsParAnnee(int $annee, array $statuts): void
    {
        self::ensureUrssafTableExists();
        $pdo = Database::getConnection();

        $sql = "INSERT INTO DeclarationUrssaf (annee, mois, statut, updated_at)
                VALUES (:annee, :mois, :statut, NOW())
                ON DUPLICATE KEY UPDATE statut = VALUES(statut), updated_at = NOW()";
        $stmt = $pdo->prepare($sql);

        foreach ($statuts as $mois => $statut) {
            $moisInt = (int)$mois;
            if ($moisInt < 1 || $moisInt > 12) {
                continue;
            }

            $normalized = self::normalizeUrssafStatut((string)$statut);
            if ($normalized === '') {
                continue;
            }

            $stmt->execute([
                'annee' => $annee,
                'mois' => $moisInt,
                'statut' => $normalized,
            ]);
        }
    }

    private static function normalizeUrssafStatut(string $statut): string
    {
        $value = trim(strtolower($statut));
        if ($value === self::URSSAF_STATUS_EN_COURS || $value === self::URSSAF_STATUS_PRELEVE) {
            return $value;
        }

        return '';
    }

    private static function ensureUrssafTableExists(): void
    {
        static $ready = false;
        if ($ready) {
            return;
        }

        $pdo = Database::getConnection();
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS DeclarationUrssaf (
                id INT AUTO_INCREMENT PRIMARY KEY,
                annee INT NOT NULL,
                mois TINYINT NOT NULL,
                statut VARCHAR(20) NOT NULL DEFAULT 'en_cours',
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_declaration_urssaf_annee_mois (annee, mois)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $ready = true;
    }
}
