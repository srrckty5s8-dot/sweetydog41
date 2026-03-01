<?php

class Client
{
    public static function getAllWithAnimaux(string $search = '')
    {
        $pdo = Database::getConnection();

        $sql = "SELECT a.*, p.nom, p.prenom, p.telephone, p.email, p.adresse
                FROM Animaux a
                INNER JOIN Proprietaires p ON a.id_proprietaire = p.id_proprietaire";

        if ($search) {
            $sql .= " WHERE p.nom LIKE :search
                      OR p.prenom LIKE :search
                      OR a.nom_animal LIKE :search
                      OR a.race LIKE :search";
        }

        $sql .= " ORDER BY p.nom ASC, a.nom_animal ASC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function create(array $data): int
{
    $pdo = Database::getConnection();

    $sql = "INSERT INTO Proprietaires (nom, prenom, telephone, email, adresse)
            VALUES (:nom, :prenom, :telephone, :email, :adresse)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'telephone' => $data['telephone'],
        'email' => $data['email'],
        'adresse' => $data['adresse'],
    ]);

    return (int)$pdo->lastInsertId();
}
public static function getAllProprietaires(): array
{
    $pdo = Database::getConnection();

    $sql = "SELECT id_proprietaire, nom, prenom, telephone
            FROM Proprietaires
            ORDER BY nom ASC";

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
public static function createProprietaire(array $data): int
{
    $pdo = Database::getConnection();

    $sql = "INSERT INTO Proprietaires (nom, prenom, telephone, email, adresse)
            VALUES (:nom, :prenom, :telephone, :email, :adresse)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'telephone' => $data['telephone'],
        'email' => $data['email'],
        'adresse' => $data['adresse'],
    ]);

    return (int)$pdo->lastInsertId();
}

public static function createAnimal(array $data): int
{
    $pdo = Database::getConnection();

    $sql = "INSERT INTO Animaux (id_proprietaire, nom_animal, espece, race, poids, steril, sexe, date_naissance)
            VALUES (:id_proprietaire, :nom_animal, :espece, :race, :poids, :steril, :sexe, :date_naissance)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id_proprietaire' => $data['id_proprietaire'],
        'nom_animal' => $data['nom_animal'],
        'espece' => $data['espece'],
        'race' => $data['race'],
        'poids' => $data['poids'],
        'steril' => $data['sterilise'],
        'sexe' => $data['sexe'] ?? null,
        'date_naissance' => !empty($data['date_naissance']) ? $data['date_naissance'] : null,
    ]);

    return (int)$pdo->lastInsertId();
}
public static function findProprietaire(int $id): ?array
{
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT * FROM Proprietaires WHERE id_proprietaire = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

public static function getAnimauxByProprietaire(int $id_proprietaire): array
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM Animaux WHERE id_proprietaire = :id ORDER BY nom_animal ASC");
    $stmt->execute(['id' => $id_proprietaire]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retourne tous les propriétaires avec la liste de leurs animaux (pour autocomplete facturation)
 * Chaque entrée = un propriétaire avec un champ 'animaux' contenant un tableau d'animaux
 */
public static function getAllProprietairesAvecAnimaux(): array
{
    $pdo = Database::getConnection();

    $sql = "SELECT p.id_proprietaire, p.nom, p.prenom, p.telephone,
                   a.id_animal, a.nom_animal, a.espece, a.race
            FROM Proprietaires p
            LEFT JOIN Animaux a ON a.id_proprietaire = p.id_proprietaire
            ORDER BY p.nom ASC, p.prenom ASC, a.nom_animal ASC";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Grouper par propriétaire
    $result = [];
    foreach ($rows as $row) {
        $id = (int)$row['id_proprietaire'];
        if (!isset($result[$id])) {
            $result[$id] = [
                'id_proprietaire' => $id,
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'telephone' => $row['telephone'],
                'animaux' => [],
            ];
        }
        if ($row['id_animal']) {
            $result[$id]['animaux'][] = [
                'id_animal' => (int)$row['id_animal'],
                'nom_animal' => $row['nom_animal'],
                'espece' => $row['espece'],
                'race' => $row['race'],
            ];
        }
    }

    return array_values($result);
}

public static function updateProprietaire(int $id, array $data): bool
{
    $pdo = Database::getConnection();

    $sql = "UPDATE Proprietaires
            SET nom = :nom,
                prenom = :prenom,
                telephone = :telephone,
                email = :email,
                adresse = :adresse
            WHERE id_proprietaire = :id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'telephone' => $data['telephone'],
        'email' => $data['email'],
        'adresse' => $data['adresse'],
        'id' => $id,
    ]);
}

public static function deleteProprietaire(int $id): bool
{
    if ($id <= 0) {
        return false;
    }

    $pdo = Database::getConnection();

    try {
        $pdo->beginTransaction();

        // Récupérer tous les animaux liés au propriétaire
        $stmtAnimaux = $pdo->prepare("SELECT id_animal FROM Animaux WHERE id_proprietaire = :id");
        $stmtAnimaux->execute(['id' => $id]);
        $animalIds = array_map('intval', $stmtAnimaux->fetchAll(PDO::FETCH_COLUMN));

        // Supprimer les données dépendantes de chaque animal (RDV + prestations)
        if (!empty($animalIds)) {
            $placeholders = implode(',', array_fill(0, count($animalIds), '?'));

            $stmtRdv = $pdo->prepare("DELETE FROM RendezVous WHERE id_animal IN ($placeholders)");
            $stmtRdv->execute($animalIds);

            $stmtPrestations = $pdo->prepare("DELETE FROM Prestations WHERE id_animal IN ($placeholders)");
            $stmtPrestations->execute($animalIds);
        }

        // Supprimer les animaux du propriétaire
        $stmtDeleteAnimaux = $pdo->prepare("DELETE FROM Animaux WHERE id_proprietaire = :id");
        $stmtDeleteAnimaux->execute(['id' => $id]);

        // Supprimer le propriétaire
        $stmtDeleteProprio = $pdo->prepare("DELETE FROM Proprietaires WHERE id_proprietaire = :id");
        $stmtDeleteProprio->execute(['id' => $id]);

        $deleted = $stmtDeleteProprio->rowCount() > 0;
        $pdo->commit();

        return $deleted;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return false;
    }
}


}
