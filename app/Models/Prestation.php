<?php

class Prestation
{
    private static ?bool $dureeColumnReady = null;

    public static function ensureDureeColumn(PDO $pdo): void
    {
        if (self::$dureeColumnReady === true) {
            return;
        }

        $dbName = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
        if ($dbName === '') {
            return;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'Prestations' AND COLUMN_NAME = 'duree_minutes'");
        $stmt->execute(['db' => $dbName]);
        $exists = ((int)$stmt->fetchColumn()) > 0;

        if (!$exists) {
            $pdo->exec("ALTER TABLE Prestations ADD COLUMN duree_minutes INT NULL AFTER notes");
        }

        self::migrateLegacyDurationFromNotes($pdo);
        self::$dureeColumnReady = true;
    }

    private static function migrateLegacyDurationFromNotes(PDO $pdo): void
    {
        $sql = "
            SELECT id_prestation, notes
            FROM Prestations
            WHERE duree_minutes IS NULL
              AND notes IS NOT NULL
              AND notes <> ''
              AND (
                notes LIKE '%Durée toilettage:%'
                OR notes LIKE '%Temps toilettage:%'
              )
            LIMIT 500
        ";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (empty($rows)) {
            return;
        }

        $update = $pdo->prepare("UPDATE Prestations SET duree_minutes = :duree, notes = :notes WHERE id_prestation = :id");

        foreach ($rows as $row) {
            $id = (int)($row['id_prestation'] ?? 0);
            $notes = trim((string)($row['notes'] ?? ''));
            if ($id <= 0 || $notes === '') {
                continue;
            }

            $dureeMinutes = null;

            if (preg_match('/(?:Durée toilettage|Temps toilettage)\s*:\s*(\d+)\s*h\s*(\d{1,2})?/iu', $notes, $mHm)) {
                $h = (int)$mHm[1];
                $m = isset($mHm[2]) ? (int)$mHm[2] : 0;
                if ($m > 59) {
                    $m = 59;
                }
                $dureeMinutes = ($h * 60) + $m;
            } elseif (preg_match('/(?:Durée toilettage|Temps toilettage)\s*:\s*(\d+)\s*min/iu', $notes, $mMin)) {
                $dureeMinutes = (int)$mMin[1];
            }

            if ($dureeMinutes === null || $dureeMinutes <= 0) {
                continue;
            }

            $notesNettoyees = preg_replace('/\s*\|?\s*(?:Durée toilettage|Temps toilettage)\s*:\s*(?:\d+\s*h\s*\d{0,2}|\d+\s*min)\s*/iu', ' ', $notes);
            $notesNettoyees = preg_replace('/\s*\|\s*/u', ' | ', (string)$notesNettoyees);
            $notesNettoyees = trim((string)$notesNettoyees, " \t\n\r\0\x0B|");

            $update->execute([
                'id' => $id,
                'duree' => $dureeMinutes,
                'notes' => $notesNettoyees,
            ]);
        }
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        self::ensureDureeColumn($pdo);

        $sql = "INSERT INTO Prestations (id_animal, date_soin, type_soin, prix, mode_paiement, notes, duree_minutes)
                VALUES (:id_animal, :date_soin, :type_soin, :prix, :mode_paiement, :notes, :duree_minutes)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_animal' => $data['id_animal'],
            'date_soin' => $data['date_soin'],
            'type_soin' => $data['type_soin'],
            'prix' => $data['prix'],
            'mode_paiement' => $data['mode_paiement'] ?? null,
            'notes' => $data['notes'],
            'duree_minutes' => isset($data['duree_minutes']) && (int)$data['duree_minutes'] > 0
                ? (int)$data['duree_minutes']
                : null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function updateNotes(int $idPrestation, string $notes): bool
    {
        if ($idPrestation <= 0) {
            return false;
        }

        $pdo = Database::getConnection();
        self::ensureDureeColumn($pdo);

        $stmt = $pdo->prepare('UPDATE Prestations SET notes = :notes WHERE id_prestation = :id');
        return $stmt->execute([
            'id' => $idPrestation,
            'notes' => trim($notes),
        ]);
    }
}
