<?php
$moisNoms = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
];

$backUrl = route('declaration.index');
$backLabel = 'Retour Déclaration';
if (!empty($isFromFacturation)) {
    $backQuery = [];
    if (!empty($returnClientId)) {
        $backQuery['client'] = (int)$returnClientId;
    }
    $backUrl = route('facturation.index', [], $backQuery);
    $backLabel = 'Retour Facturation';
}

$navQueryBase = is_array($navigationQuery ?? null) ? $navigationQuery : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factures - Déclaration</title>
    <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #333; }
        .container-large { max-width: 1150px; padding: 25px; margin: 0 auto; }
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px; margin-bottom: 25px;
        }
        .brand h2 { margin: 0; color: var(--vert-fonce); }
        .top-nav { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .top-nav a { text-decoration: none; color: #666; font-size: .9rem; font-weight: 600; }
        .top-nav a:hover { color: var(--vert-fonce); }

        .actions-bar {
            background: #fff; border-radius: 14px; padding: 14px 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 18px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        }
        .total-badge {
            background: #ecfdf5; color: #065f46; border: 1px solid #bbf7d0;
            border-radius: 999px; padding: 8px 14px; font-weight: 800; font-size: .9rem;
        }
        .btn-back {
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none; background: var(--vert-fonce); color: #fff;
            border-radius: 10px; padding: 10px 14px; font-weight: 700;
        }

        .year-filters {
            background: #fff; border-radius: 14px; padding: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 18px;
            display: flex; gap: 8px; flex-wrap: wrap;
        }
        .year-chip {
            text-decoration: none; border-radius: 999px; padding: 7px 12px;
            border: 1px solid #d1d5db; color: #374151; font-weight: 700; font-size: .85rem;
            background: #fff;
        }
        .year-chip.active {
            background: #1b4332; border-color: #1b4332; color: #fff;
        }

        .year-section {
            background: #fff; border-radius: 14px; padding: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 16px;
        }
        .year-title {
            margin: 0 0 12px 0; color: #1f2937; font-size: 1.15rem; font-weight: 900;
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
        }
        .year-count {
            font-size: .8rem; color: #475569; background: #f1f5f9; padding: 4px 10px; border-radius: 999px;
        }

        .month-section {
            border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 12px;
            background: #fff;
        }
        .month-header {
            background: #f8fafc; padding: 10px 12px; display: flex;
            justify-content: space-between; align-items: center; gap: 8px;
            font-weight: 800; color: #1f2937;
            cursor: pointer;
            user-select: none;
        }
        .month-header::-webkit-details-marker { display: none; }
        .month-count {
            color: #64748b; font-size: .8rem; font-weight: 700;
        }
        .month-toggle {
            color: #64748b;
            font-size: .82rem;
            font-weight: 800;
            min-width: 24px;
            text-align: right;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .2s ease, color .2s ease;
        }
        .month-section[open] .month-toggle {
            color: #1b4332;
            transform: rotate(180deg);
        }
        .month-content {
            padding: 0;
        }

        .invoice-table {
            width: 100%; border-collapse: collapse;
        }
        .invoice-table th {
            background: #1b4332; color: #fff; font-size: .74rem; text-transform: uppercase;
            letter-spacing: .4px; padding: 10px 12px; text-align: left;
        }
        .invoice-table td {
            background: #fff; padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle;
        }
        .invoice-table tr:last-child td { border-bottom: none; }

        .file-name {
            font-weight: 700; color: #111827; word-break: break-all;
        }
        .file-meta { color: #6b7280; font-size: .82rem; }
        .btn-open {
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; background: #e8f5e9; color: #2e7d32;
            border: 1px solid #c8e6c9; border-radius: 8px; padding: 6px 10px; font-weight: 700;
        }
        .empty-state {
            text-align: center; background: #fff; border-radius: 14px; padding: 40px 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); color: #64748b;
        }

        @media (max-width: 700px) {
            .top-bar { flex-direction: column; align-items: flex-start; }
            .top-nav { width: 100%; flex-wrap: nowrap; overflow-x: auto; }
            .top-nav::-webkit-scrollbar { display: none; }
            .year-title { flex-direction: column; align-items: flex-start; }
            .invoice-table thead { display: none; }
            .invoice-table, .invoice-table tbody, .invoice-table tr, .invoice-table td {
                display: block; width: 100%;
            }
            .invoice-table tr {
                border-bottom: 1px solid #eef2f7;
                padding: 6px 0;
            }
            .invoice-table td {
                border-bottom: none;
                padding: 4px 10px;
            }
        }
    </style>
</head>
<body>
<div class="container-large">
    <div class="top-bar">
        <div class="brand"><h2>📁 Factures</h2></div>
        <div class="top-nav">
            <a href="<?= route('clients.index') ?>">🐾 Clients</a>
            <a href="<?= route('appointments.index') ?>">📅 Agenda</a>
            <a href="<?= route('declaration.index') ?>">📊 Déclaration</a>
            <a href="<?= route('settings.index') ?>">⚙️ Paramètres</a>
            <a href="<?= route('logout') ?>" style="color: #e63946;"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        </div>
    </div>

    <div class="actions-bar">
        <a class="btn-back" href="<?= htmlspecialchars($backUrl) ?>">
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backLabel) ?>
        </a>
        <span class="total-badge">
            <i class="fa-solid fa-file-pdf"></i> <?= (int)$totalInvoices ?> facture(s)
        </span>
    </div>

    <?php if (!empty($availableYears)): ?>
        <div class="year-filters">
            <a class="year-chip <?= $selectedYear === 0 ? 'active' : '' ?>"
               href="<?= route('declaration.invoices', [], $navQueryBase) ?>">Toutes</a>
            <?php foreach ($availableYears as $year): ?>
                <?php
                    $yearInt = (int)$year;
                    $yearQuery = $navQueryBase;
                    $yearQuery['annee'] = $yearInt;
                ?>
                <a class="year-chip <?= $selectedYear === $yearInt ? 'active' : '' ?>"
                   href="<?= route('declaration.invoices', [], $yearQuery) ?>">
                    <?= $yearInt ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($groupedInvoices)): ?>
        <div class="empty-state">
            <h3 style="margin-top: 0;">Aucune facture trouvée</h3>
            <p style="margin-bottom: 0;">Les PDF apparaîtront ici, classés par année puis par mois.</p>
        </div>
    <?php else: ?>
        <?php foreach ($groupedInvoices as $year => $months): ?>
            <?php
                $yearTotal = 0;
                foreach ($months as $monthFiles) {
                    $yearTotal += count($monthFiles);
                }
            ?>
            <section class="year-section">
                <h3 class="year-title">
                    <span>Année <?= (int)$year ?></span>
                    <span class="year-count"><?= $yearTotal ?> facture(s)</span>
                </h3>

                <?php foreach ($months as $month => $files): ?>
                    <?php
                        $monthNum = (int)$month;
                        $monthName = $moisNoms[$monthNum] ?? ('Mois ' . $monthNum);
                    ?>
                    <details class="month-section">
                        <summary class="month-header">
                            <span><?= htmlspecialchars($monthName) ?></span>
                            <span style="display: inline-flex; align-items: center; gap: 10px;">
                                <span class="month-count"><?= count($files) ?> facture(s)</span>
                                <span class="month-toggle"><i class="fa-solid fa-chevron-down"></i></span>
                            </span>
                        </summary>
                        <div class="month-content">
                            <table class="invoice-table">
                                <thead>
                                <tr>
                                    <th>Fichier</th>
                                    <th>Date fichier</th>
                                    <th>Taille</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td>
                                            <div class="file-name"><?= htmlspecialchars($file['name']) ?></div>
                                            <div class="file-meta"><?= htmlspecialchars($file['relative_path']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($file['date_label']) ?></td>
                                        <td><?= number_format((float)$file['size_kb'], 1, ',', ' ') ?> Ko</td>
                                        <td>
                                            <a class="btn-open" target="_blank"
                                               href="<?= route('declaration.invoices.open', [], ['f' => $file['token']]) ?>">
                                                <i class="fa-solid fa-file-pdf"></i> Ouvrir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
