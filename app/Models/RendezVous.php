<?php

class RendezVous
{
    public static function getUpcomingByAnimal(int $animalId, int $limit = 5): array
    {
        if ($animalId <= 0) {
            return [];
        }

        $pdo = Database::getConnection();
        $limit = max(1, min(20, $limit));

        $sql = "SELECT id_rdv, id_animal, titre, date_debut, date_fin
                FROM RendezVous
                WHERE id_animal = :id_animal
                  AND date_debut >= NOW()
                ORDER BY date_debut ASC
                LIMIT {$limit}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_animal' => $animalId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function getToday()
    {
        $pdo = Database::getConnection();

        $today = date('Y-m-d');

        $sql = "SELECT r.*, a.nom_animal
                FROM RendezVous r
                JOIN Animaux a ON r.id_animal = a.id_animal
                WHERE DATE(r.date_debut) = :today
                ORDER BY r.date_debut ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['today' => $today]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCalendarEvents(): array
    {
        $pdo = Database::getConnection();

        $sql = "SELECT r.id_rdv,
                       r.id_animal,
                       r.titre,
                       r.date_debut,
                       r.date_fin,
                       a.nom_animal,
                       p.nom,
                       p.prenom
                FROM RendezVous r
                JOIN Animaux a ON r.id_animal = a.id_animal
                JOIN Proprietaires p ON a.id_proprietaire = p.id_proprietaire";

        $stmt = $pdo->query($sql);
        $rdvs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $events = [];
        foreach ($rdvs as $r) {
            $events[] = [
                'id' => $r['id_rdv'],
                'title' => $r['nom_animal'] . " " . $r['nom'] . " - " . $r['titre'],
                'start' => $r['date_debut'],
                'end' => $r['date_fin'],
                'extendedProps' => [
                    'id_animal' => $r['id_animal'],
                    'nom_animal' => $r['nom_animal'],
                    'nom_client' => $r['prenom'] . " " . $r['nom'],
                    'titre' => $r['titre'],
                ],
            ];
        }

        return $events;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO RendezVous (id_animal, titre, date_debut, date_fin)
                VALUES (:id_animal, :titre, :date_debut, :date_fin)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id_animal' => $data['id_animal'],
            'titre' => $data['titre'],
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function getById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT r.*, a.nom_animal
                               FROM RendezVous r
                               JOIN Animaux a ON r.id_animal = a.id_animal
                               WHERE r.id_rdv = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE RendezVous
                               SET titre = :titre, date_debut = :date_debut, date_fin = :date_fin
                               WHERE id_rdv = :id");
        return $stmt->execute([
            'id' => $id,
            'titre' => $data['titre'],
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
        ]);
    }

    public static function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM RendezVous WHERE id_rdv = :id");
        return $stmt->execute(['id' => $id]);
    }
}
