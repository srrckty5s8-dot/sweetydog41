<?php

class User
{
    public static function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE id_utilisateur = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        if ($id <= 0 || $hash === '') {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE Utilisateurs SET mot_de_passe = :mdp WHERE id_utilisateur = :id");
        return $stmt->execute([
            'mdp' => $hash,
            'id' => $id,
        ]);
    }
}
