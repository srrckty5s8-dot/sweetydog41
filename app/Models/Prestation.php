<?php

class Prestation{
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO Prestations (id_animal, date_soin, type_soin, prix, mode_paiement, notes)
                VALUES (:id_animal, :date_soin, :type_soin, :prix, :mode_paiement, :notes)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_animal'      => $data['id_animal'],
            'date_soin'      => $data['date_soin'],
            'type_soin'      => $data['type_soin'],
            'prix'           => $data['prix'],
            'mode_paiement'  => $data['mode_paiement'] ?? null,
            'notes'          => $data['notes'],
        ]);
        return (int)$pdo->lastInsertId();
    }
}
