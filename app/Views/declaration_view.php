<?php
    $moisNoms = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];
    $moisLabel = $moisNoms[$mois] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déclaration - SweetyDog</title>
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #333; }

        .container-large { max-width: 1100px; padding: 25px; margin: 0 auto; }

        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px; margin-bottom: 25px;
        }
        .brand h2 { margin: 0; color: var(--vert-fonce); }
        .top-nav { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .top-nav a { text-decoration: none; color: #666; font-size: .9rem; font-weight: 600; }
        .top-nav a:hover { color: var(--vert-fonce); }

        /* Sélecteur de mois */
        .month-selector {
            display: flex; align-items: center; gap: 12px; margin-bottom: 30px;
            background: white; padding: 15px 25px; border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); flex-wrap: wrap;
        }
        .month-selector label { font-weight: 700; color: #475569; font-size: 0.95rem; }
        .month-selector select, .month-selector input[type="number"] {
            padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px;
            background: #f8fafc; font-size: 0.95rem; font-weight: 600; color: #1e293b;
        }
        .month-selector select:focus, .month-selector input[type="number"]:focus {
            outline: none; border-color: var(--vert-moyen);
        }
        .btn-filter {
            background: var(--vert-fonce); color: white; border: none;
            padding: 10px 22px; border-radius: 10px; font-weight: 700;
            cursor: pointer; transition: all 0.3s ease; font-size: 0.9rem;
        }
        .btn-filter:hover { background: #1b4332; transform: translateY(-1px); }
        .btn-invoices {
            display: inline-flex; align-items: center; gap: 7px;
            text-decoration: none; background: #e8f5e9; color: #2e7d32;
            border: 1px solid #c8e6c9; padding: 10px 16px; border-radius: 10px;
            font-weight: 700; font-size: 0.9rem;
        }
        .btn-invoices:hover { background: #d8f3dc; }
        .btn-monthly {
            display: inline-flex; align-items: center; gap: 7px;
            text-decoration: none; background: #eef2ff; color: #3730a3;
            border: 1px solid #c7d2fe; padding: 10px 16px; border-radius: 10px;
            font-weight: 700; font-size: 0.9rem;
        }
        .btn-monthly:hover { background: #e0e7ff; }

        /* Cards stats */
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;
        }
        @media (max-width: 700px) { .stats-grid { grid-template-columns: 1fr; } }
        .stat-card {
            background: white; border-radius: 14px; padding: 20px 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;
        }
        .stat-card .stat-icon { font-size: 2rem; margin-bottom: 8px; }
        .stat-card .stat-label { color: #94a3b8; font-size: 0.78rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
        .stat-card .stat-value { color: var(--vert-fonce); font-size: 1.8rem; font-weight: 900; margin-top: 4px; }

        /* Chart container */
        .chart-section {
            display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;
        }
        @media (max-width: 900px) { .chart-section { grid-template-columns: 1fr; } }
        .chart-card {
            background: white; border-radius: 14px; padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .chart-card h3 { margin: 0 0 20px 0; color: #1e293b; font-size: 1.1rem; }

        /* Tableau détail */
        .detail-table {
            width: 100%; border-collapse: collapse; background: white;
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .detail-table th {
            text-align: left; padding: 14px 20px; background: var(--vert-fonce);
            color: white; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .detail-table td {
            padding: 12px 20px; border-bottom: 1px solid #f3f4f6; vertical-align: middle;
        }
        .detail-table tr:last-child td { border-bottom: none; }
        .detail-table .type-label {
            font-weight: 700; display: inline-flex; align-items: center; gap: 8px;
        }
        .detail-table .bar-cell { width: 40%; }
        .bar-bg {
            background: #f1f5f9; border-radius: 8px; height: 24px; overflow: hidden; position: relative;
        }
        .bar-fill {
            height: 100%; border-radius: 8px; transition: width 0.6s ease;
            display: flex; align-items: center; justify-content: flex-end; padding-right: 8px;
            font-size: 0.72rem; font-weight: 800; color: white;
        }
        .empty-state {
            text-align: center; padding: 60px 20px; color: #94a3b8;
        }
        .empty-state .empty-icon { font-size: 3rem; margin-bottom: 15px; }

        /* ============================
           RESPONSIVE MOBILE
           ============================ */
        @media (max-width: 600px) {
            body {
                background: linear-gradient(180deg, #edf7f1 0%, #f8fafc 45%, #f4f7f6 100%);
            }
            .container-large {
                padding: 12px !important;
                border-radius: 18px !important;
                border: 1px solid #e1ece5;
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .brand h2 { font-size: 1.1rem; }
            .top-nav {
                gap: 8px;
                width: 100%;
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 4px;
                -webkit-overflow-scrolling: touch;
            }
            .top-nav::-webkit-scrollbar { display: none; }
            .top-nav a {
                font-size: 0.78rem;
                flex: 0 0 auto;
                background: #fff;
                border: 1px solid #dce8e1;
                border-radius: 999px;
                padding: 8px 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }
            .top-nav a[style*="#e63946"] {
                background: #fff5f5;
                border-color: #fecaca;
            }

            .month-selector {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                padding: 15px;
                border: 1px solid #e4efe8;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
                position: sticky;
                top: 8px;
                z-index: 6;
            }
            .month-selector select,
            .month-selector input[type="number"] {
                width: 100%;
            }
            .btn-filter { width: 100%; text-align: center; }
            .btn-invoices { width: 100%; justify-content: center; }
            .btn-monthly { width: 100%; justify-content: center; }

            .stats-grid { grid-template-columns: 1fr !important; gap: 12px; }
            .stat-card {
                padding: 15px;
                border: 1px solid #e4efe8;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }
            .stat-card .stat-value { font-size: 1.4rem; }
            .stat-card .stat-icon { font-size: 1.5rem; }

            .chart-section { grid-template-columns: 1fr !important; gap: 15px; }
            .chart-card {
                padding: 15px;
                border: 1px solid #e4efe8;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }
            .chart-card h3 { font-size: 0.95rem; }

            /* Tableau en cartes */
            .detail-table, .detail-table thead, .detail-table tbody,
            .detail-table tr, .detail-table th, .detail-table td { display: block; }
            .detail-table thead { display: none; }
            .detail-table tr {
                margin-bottom: 10px;
                border: 1px solid #f1f5f9;
                border-radius: 12px;
                overflow: hidden;
                background: white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            }
            .detail-table td {
                padding: 8px 15px !important;
                border-bottom: 1px solid #f8f9fa !important;
                text-align: left !important;
            }
            .detail-table td:before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 0.7rem;
                text-transform: uppercase;
                color: #94a3b8;
                display: block;
                margin-bottom: 3px;
            }
            .detail-table td:last-child { border-bottom: none !important; }
            .detail-table .bar-cell { width: auto !important; }
        }

        @media (max-width: 480px) {
            .brand h2 { font-size: 1rem; }
            .top-nav a { font-size: 0.72rem; }
            .stat-card .stat-value { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

<div class="container-large">

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="brand"><h2>📊 Déclaration</h2></div>
        <div class="top-nav">
            <a href="<?= route('clients.index') ?>">🐾 Clients</a>
            <a href="<?= route('appointments.index') ?>">📅 Agenda</a>
            <a href="<?= route('facturation.index') ?>">🧾 Facturation</a>
            <a href="<?= route('settings.index') ?>">⚙️ Paramètres</a>
            <a href="<?= route('logout') ?>" style="color: #e63946;"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        </div>
    </div>

    <!-- SÉLECTEUR DE MOIS -->
    <form class="month-selector" method="GET" action="<?= route('declaration.index') ?>">
        <label for="mois">📅 Période :</label>
        <select name="mois" id="mois">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m == $mois ? 'selected' : '' ?>><?= $moisNoms[$m] ?></option>
            <?php endfor; ?>
        </select>
        <input type="number" name="annee" value="<?= $annee ?>" min="2020" max="2040" style="width: 100px;">
        <button type="submit" class="btn-filter">Afficher</button>
        <a href="<?= route('declaration.invoices') ?>" class="btn-invoices">
            <i class="fa-solid fa-file-invoice"></i> Factures
        </a>
        <a href="<?= route('declaration.monthly', [], ['annee' => $annee]) ?>" class="btn-monthly">
            <i class="fa-solid fa-table-list"></i> CA mensuel & URSSAF
        </a>
    </form>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Chiffre d'affaires</div>
            <div class="stat-value"><?= number_format($caTotal, 2, ',', ' ') ?> €</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-label">Prestations</div>
            <div class="stat-value"><?= $nbPrestations ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-label">Panier moyen</div>
            <div class="stat-value"><?= $nbPrestations > 0 ? number_format($caTotal / $nbPrestations, 2, ',', ' ') : '0,00' ?> €</div>
        </div>
    </div>

    <?php if (empty($caParType)): ?>
        <!-- Aucune donnée -->
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Aucune prestation en <?= htmlspecialchars($moisLabel) ?> <?= $annee ?></h3>
            <p>Sélectionnez un autre mois pour voir les statistiques.</p>
        </div>
    <?php else: ?>

        <!-- GRAPHIQUES -->
        <div class="chart-section">
            <div class="chart-card">
                <h3>🥧 Répartition du CA par type</h3>
                <canvas id="chartCamembert" style="max-height: 350px;"></canvas>
            </div>
            <div class="chart-card">
                <h3>📊 Détail par catégorie</h3>
                <canvas id="chartBarres" style="max-height: 350px;"></canvas>
            </div>
        </div>

        <!-- TABLEAU DÉTAIL -->
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Type de prestation</th>
                    <th>Montant</th>
                    <th>% du CA</th>
                    <th class="bar-cell">Répartition</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($caParType as $item): ?>
                    <?php
                        $pct = $caTotal > 0 ? round(($item['total'] / $caTotal) * 100, 1) : 0;
                        $isVente = strpos($item['type'], 'Vente') !== false;
                        $color = $isVente ? '#7c3aed' : '#2d6a4f';
                    ?>
                    <tr>
                        <td data-label="Type">
                            <span class="type-label">
                                <span style="display:inline-block; width:14px; height:14px; border-radius:4px; background:<?= $color ?>;"></span>
                                <?= htmlspecialchars($item['type']) ?>
                            </span>
                        </td>
                        <td data-label="Montant" style="font-weight: 800; color: #1e293b;"><?= number_format($item['total'], 2, ',', ' ') ?> €</td>
                        <td data-label="% du CA" style="font-weight: 700; color: #64748b;"><?= $pct ?> %</td>
                        <td data-label="Répartition" class="bar-cell">
                            <div class="bar-bg">
                                <div class="bar-fill" style="width: <?= $pct ?>%; background: <?= $color ?>;">
                                    <?= $pct >= 8 ? $pct . '%' : '' ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background: #f8fafc; font-weight: 900;">
                    <td>TOTAL</td>
                    <td style="color: var(--vert-fonce); font-size: 1.1rem;"><?= number_format($caTotal, 2, ',', ' ') ?> €</td>
                    <td>100 %</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<?php if (!empty($caParType)): ?>
<script>
(function() {
    var data = <?= json_encode($caParType) ?>;
    var labels = data.map(function(d) { return d.type; });
    var values = data.map(function(d) { return d.total; });
    var colors = data.map(function(d) {
        return d.type.indexOf('Vente') !== -1 ? '#7c3aed' : '#2d6a4f';
    });

    // Camembert
    new Chart(document.getElementById('chartCamembert'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, usePointStyle: true, pointStyle: 'circle', font: { size: 12, weight: '600' } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var val = ctx.parsed;
                            var total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                            return ctx.label + ' : ' + val.toFixed(2).replace('.', ',') + ' € (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });

    // Barres horizontales
    new Chart(document.getElementById('chartBarres'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.parsed.x.toFixed(2).replace('.', ',') + ' €';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { callback: function(v) { return v + ' €'; }, font: { weight: '600' } },
                    grid: { color: '#f1f5f9' }
                },
                y: {
                    ticks: { font: { size: 11, weight: '700' } },
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>

</body>
</html>
