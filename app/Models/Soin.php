<?php

class Soin
{
    private static ?bool $facturesTableExists = null;

    private static function hasFacturesTable(PDO $pdo): bool
    {
        if (self::$facturesTableExists !== null) {
            return self::$facturesTableExists;
        }

        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'Factures'");
            self::$facturesTableExists = (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            self::$facturesTableExists = false;
        }

        return self::$facturesTableExists;
    }

    private static function normalizeLabel(string $value): string
    {
        $v = trim($value);
        if ($v === '') {
            return '';
        }

        return function_exists('mb_strtolower')
            ? mb_strtolower($v, 'UTF-8')
            : strtolower($v);
    }

    private static function splitCsv(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/', $value) ?: [];
        $clean = [];
        foreach ($parts as $part) {
            $part = trim((string)$part);
            if ($part !== '') {
                $clean[] = $part;
            }
        }
        return $clean;
    }

    private static function buildMergeKey(array $row): string
    {
        return (int)($row['id_animal'] ?? 0) . '|' . (string)($row['date_soin'] ?? '');
    }

    private static function mergeTypeLabels(string $baseTypes, array $extraTypes): string
    {
        $result = self::splitCsv($baseTypes);
        $seen = [];

        foreach ($result as $label) {
            $seen[self::normalizeLabel($label)] = true;
        }

        foreach ($extraTypes as $type) {
            $type = trim((string)$type);
            if ($type === '') {
                continue;
            }

            $key = self::normalizeLabel($type);
            if (isset($seen[$key])) {
                continue;
            }

            $result[] = $type;
            $seen[$key] = true;
        }

        return implode(', ', $result);
    }

    private static function mergeNotes(string $baseNotes, array $extraNotes): string
    {
        $result = [];
        $seen = [];

        $add = static function (string $note) use (&$result, &$seen): void {
            $note = trim($note);
            if ($note === '' || $note === '-') {
                return;
            }

            $key = self::normalizeLabel($note);
            if ($key === '' || isset($seen[$key])) {
                return;
            }

            $result[] = $note;
            $seen[$key] = true;
        };

        foreach ((preg_split('/\s*\|\s*/', $baseNotes) ?: []) as $note) {
            $add((string)$note);
        }

        foreach ($extraNotes as $note) {
            $add((string)$note);
        }

        return empty($result) ? trim($baseNotes) : implode(' | ', $result);
    }

    private static function isRemiseEntry(array $row): bool
    {
        $type = trim((string)($row['type_soin'] ?? ''));
        $notes = trim((string)($row['notes'] ?? ''));
        $prix = (float)($row['prix'] ?? 0);

        return stripos($type, 'Remise') === 0
            || stripos($notes, 'Remise') === 0
            || stripos($notes, 'remise commerciale') !== false
            || $prix < 0;
    }

    private static function isVenteEntry(array $row): bool
    {
        $type = trim((string)($row['type_soin'] ?? ''));
        return stripos($type, 'Vente') === 0;
    }

    private static function mergeRemises(array $rows): array
    {
        $merged = [];
        $pendingRemises = [];

        foreach ($rows as $row) {
            $row['prix'] = (float)($row['prix'] ?? 0);
            $key = self::buildMergeKey($row);

            if (self::isRemiseEntry($row)) {
                if (!isset($pendingRemises[$key])) {
                    $pendingRemises[$key] = [
                        'prix' => 0.0,
                        'types' => [],
                        'notes' => [],
                        'ids' => [],
                        'sample' => $row,
                    ];
                }

                $pendingRemises[$key]['prix'] += (float)$row['prix'];

                $remiseType = trim((string)($row['type_soin'] ?? 'Remise commerciale'));
                if ($remiseType === '') {
                    $remiseType = 'Remise commerciale';
                }
                $typeKey = self::normalizeLabel($remiseType);
                $alreadyType = false;
                foreach ($pendingRemises[$key]['types'] as $existingType) {
                    if (self::normalizeLabel((string)$existingType) === $typeKey) {
                        $alreadyType = true;
                        break;
                    }
                }
                if (!$alreadyType) {
                    $pendingRemises[$key]['types'][] = $remiseType;
                }

                $remiseNote = trim((string)($row['notes'] ?? ''));
                if ($remiseNote !== '' && $remiseNote !== '-') {
                    $noteKey = self::normalizeLabel($remiseNote);
                    $alreadyNote = false;
                    foreach ($pendingRemises[$key]['notes'] as $existingNote) {
                        if (self::normalizeLabel((string)$existingNote) === $noteKey) {
                            $alreadyNote = true;
                            break;
                        }
                    }
                    if (!$alreadyNote) {
                        $pendingRemises[$key]['notes'][] = $remiseNote;
                    }
                }

                $idRemise = (int)($row['id_prestation'] ?? 0);
                if ($idRemise > 0 && !in_array($idRemise, $pendingRemises[$key]['ids'], true)) {
                    $pendingRemises[$key]['ids'][] = $idRemise;
                }

                continue;
            }

            $idPrestation = (int)($row['id_prestation'] ?? 0);
            $groupIds = $idPrestation > 0 ? [$idPrestation] : [];
            $row['facture_group_ids'] = implode(',', $groupIds);

            if (!self::isVenteEntry($row) && isset($pendingRemises[$key])) {
                $pending = $pendingRemises[$key];
                $remiseMontant = (float)($pending['prix'] ?? 0);
                $prixHorsRemise = (float)($row['prix'] ?? 0);
                $prixApresRemise = $prixHorsRemise + $remiseMontant;

                $row['prix_hors_remise'] = $prixHorsRemise;
                $row['remise_montant'] = $remiseMontant;
                $row['prix_apres_remise'] = $prixApresRemise;
                $row['prix'] = $prixApresRemise;

                $row['type_soin'] = self::mergeTypeLabels((string)($row['type_soin'] ?? ''), (array)($pending['types'] ?? []));
                $row['notes'] = self::mergeNotes((string)($row['notes'] ?? ''), (array)($pending['notes'] ?? []));

                foreach ((array)($pending['ids'] ?? []) as $idRemise) {
                    $idRemise = (int)$idRemise;
                    if ($idRemise > 0 && !in_array($idRemise, $groupIds, true)) {
                        $groupIds[] = $idRemise;
                    }
                }
                $row['facture_group_ids'] = implode(',', $groupIds);

                unset($pendingRemises[$key]);
            }

            $merged[] = $row;
        }

        // Remises orphelines: conservées pour ne pas perdre d'information.
        foreach ($pendingRemises as $pending) {
            $sample = (array)($pending['sample'] ?? []);
            $sample['prix'] = (float)($pending['prix'] ?? 0);
            if (!empty($pending['types'])) {
                $sample['type_soin'] = implode(', ', $pending['types']);
            }
            if (!empty($pending['notes'])) {
                $sample['notes'] = implode(' | ', $pending['notes']);
            }

            $ids = [];
            foreach ((array)($pending['ids'] ?? []) as $id) {
                $id = (int)$id;
                if ($id > 0 && !in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }
            $sample['facture_group_ids'] = implode(',', $ids);
            $merged[] = $sample;
        }

        return $merged;
    }

    public static function findByAnimal(int $id_animal): array
    {
        if ($id_animal <= 0) {
            return [];
        }

        $pdo = Database::getConnection();
        Prestation::ensureDureeColumn($pdo);

        $useFactures = self::hasFacturesTable($pdo);
        if ($useFactures) {
            $sql = "
                SELECT
                    p.id_prestation,
                    p.id_animal,
                    p.date_soin,
                    p.type_soin,
                    p.notes,
                    p.duree_minutes,
                    p.prix,
                    p.mode_paiement,
                    f.numero_facture
                FROM Prestations p
                LEFT JOIN Factures f ON f.id_prestation = p.id_prestation
                WHERE p.id_animal = :id
                ORDER BY p.date_soin DESC, p.id_prestation DESC
            ";
        } else {
            $sql = "
                SELECT
                    p.id_prestation,
                    p.id_animal,
                    p.date_soin,
                    p.type_soin,
                    p.notes,
                    p.duree_minutes,
                    p.prix,
                    p.mode_paiement,
                    NULL AS numero_facture
                FROM Prestations p
                WHERE p.id_animal = :id
                ORDER BY p.date_soin DESC, p.id_prestation DESC
            ";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id_animal]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return self::mergeRemises($rows);
    }

    public static function findByProprietaire(int $id_proprietaire): array
    {
        if ($id_proprietaire <= 0) {
            return [];
        }

        $pdo = Database::getConnection();
        Prestation::ensureDureeColumn($pdo);

        $useFactures = self::hasFacturesTable($pdo);
        if ($useFactures) {
            $sql = "
                SELECT
                    p.id_prestation,
                    p.id_animal,
                    p.date_soin,
                    p.type_soin,
                    p.notes,
                    p.duree_minutes,
                    p.prix,
                    p.mode_paiement,
                    a.nom_animal,
                    a.espece,
                    f.numero_facture
                FROM Prestations p
                JOIN Animaux a ON p.id_animal = a.id_animal
                LEFT JOIN Factures f ON f.id_prestation = p.id_prestation
                WHERE a.id_proprietaire = :id
                ORDER BY p.date_soin DESC, p.id_prestation DESC
            ";
        } else {
            $sql = "
                SELECT
                    p.id_prestation,
                    p.id_animal,
                    p.date_soin,
                    p.type_soin,
                    p.notes,
                    p.duree_minutes,
                    p.prix,
                    p.mode_paiement,
                    a.nom_animal,
                    a.espece,
                    NULL AS numero_facture
                FROM Prestations p
                JOIN Animaux a ON p.id_animal = a.id_animal
                WHERE a.id_proprietaire = :id
                ORDER BY p.date_soin DESC, p.id_prestation DESC
            ";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id_proprietaire]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return self::mergeRemises($rows);
    }
}
