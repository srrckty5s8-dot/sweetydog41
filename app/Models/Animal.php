<?php

class Animal
{
    public static function getListForAppointments(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT a.id_animal,
                       a.nom_animal,
                       a.race,
                       p.nom as nom_client,
                       p.prenom as prenom_client
                FROM Animaux a
                LEFT JOIN Proprietaires p ON a.id_proprietaire = p.id_proprietaire
                ORDER BY a.nom_animal ASC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findWithOwner(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = Database::getConnection();

        $sql = "SELECT a.*, p.nom, p.prenom, p.telephone
                FROM Animaux a
                INNER JOIN Proprietaires p
                  ON p.id_proprietaire = a.id_proprietaire
                WHERE a.id_animal = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();

        $sql = "UPDATE Animaux
                SET nom_animal = :nom_animal,
                    espece = :espece,
                    race = :race,
                    poids = :poids,
                    steril = :steril,
                    sexe = :sexe,
                    date_naissance = :date_naissance
                WHERE id_animal = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'nom_animal' => $data['nom_animal'],
            'espece' => $data['espece'],
            'race' => $data['race'],
            'poids' => $data['poids'],
            'steril' => $data['steril'],
            'sexe' => $data['sexe'] ?? null,
            'date_naissance' => !empty($data['date_naissance']) ? $data['date_naissance'] : null,
            'id' => $id,
        ]);
    }

    public static function updateComment(int $id, string $comment): bool
    {
        if ($id <= 0) {
            return false;
        }

        $pdo = Database::getConnection();

        $sql = "UPDATE Animaux
                SET commentaire = :commentaire
                WHERE id_animal = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            'commentaire' => $comment,
            'id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("DELETE FROM Animaux WHERE id_animal = :id");
        return $stmt->execute(['id' => $id]);
    }
}
