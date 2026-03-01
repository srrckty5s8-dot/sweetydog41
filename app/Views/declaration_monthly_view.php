<?php
$moisNoms = [
    1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declaration Mensuelle - SweetyDog</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/style.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #1e293b; }
        .container-large { max-width: 1080px; margin: 0 auto; padding: 24px; }

        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            gap: 18px; margin-bottom: 20px;
        }
        .brand h2 { margin: 0; color: var(--vert-fonce); }
        .top-nav { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .top-nav a { text-decoration: none; color: #666; font-size: .9rem; font-weight: 600; }
        .top-nav a:hover { color: var(--vert-fonce); }

        .toolbar {
            background: #fff; border-radius: 14px; padding: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;
            margin-bottom: 18px;
        }
        .toolbar-left { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .toolbar-right { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }

        .btn {
            display: inline-flex; align-items: center; gap: 7px; text-decoration: none;
            border-radius: 10px; padding: 10px 14px; font-weight: 700; font-size: .9rem; border: 1px solid transparent;
        }
        .btn.back { background: #f8fafc; color: #334155; border-color: #e2e8f0; }
        .btn.back:hover { background: #eef2f7; }
        .btn.invoices { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
        .btn.invoices:hover { background: #d8f3dc; }
        .btn.save {
            background: var(--vert-fonce); color: #fff; border: none; cursor: pointer;
            padding: 11px 18px;
        }
        .btn.save:hover { background: #1b4332; }

        .year-form { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .year-form label { font-weight: 700; color: #475569; }
        .year-form input {
            width: 110px; padding: 9px 10px; border: 2px solid #e2e8f0;
            border-radius: 10px; background: #f8fafc; font-weight: 600;
        }
        .year-form button {
            border: none; background: #0f766e; color: #fff;
            padding: 10px 14px; border-radius: 10px; font-weight: 700; cursor: pointer;
        }
        .year-form button:hover { background: #115e59; }

        .stat-card {
            background: #fff; border-radius: 14px; padding: 16px 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 16px;
            display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        .stat-card .label { font-size: .85rem; text-transform: uppercase; color: #64748b; font-weight: 800; }
        .stat-card .value { font-size: 1.6rem; color: var(--vert-fonce); font-weight: 900; }

        .success-box {
            background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0;
            border-radius: 10px; padding: 10px 14px; margin-bottom: 12px; font-weight: 700;
        }

        .table-wrap {
            background: #fff; border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .table-wrap table { width: 100%; border-collapse: collapse; }
        .table-wrap th {
            text-align: left; padding: 12px 14px; background: var(--vert-fonce);
            color: #fff; font-size: .78rem; text-transform: uppercase; letter-spacing: .4px;
        }
        .table-wrap td {
            padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
        }
        .table-wrap tr:last-child td { border-bottom: none; }
        .month-cell { font-weight: 700; }
        .ca-cell { font-weight: 800; color: #0f172a; }
        .status-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            width: 100%;
            max-width: 320px;
            min-height: 42px;
        }
        .status-glyph {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(calc(-50% - 7px));
            width: 14px;
            height: 14px;
            z-index: 2;
            pointer-events: none;
            display: block;
        }
        .status-glyph::before {
            content: '';
            display: block;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
        }
        .status-wrap::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 14px;
            width: 9px;
            height: 9px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: translateY(-150%) rotate(45deg);
            opacity: .7;
            pointer-events: none;
        }
        .status-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 100%;
            min-width: 220px;
            border: 2px solid #cbd5e1;
            border-radius: 999px;
            height: 42px;
            padding: 0 38px 0 36px;
            font-weight: 700;
            font-size: .9rem;
            line-height: 42px;
            background: #fff;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .status-select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(148, 163, 184, .25);
        }
        .status-wrap.status-en-cours { color: #1e40af; }
        .status-wrap.status-en-cours .status-glyph::before {
            border: 2px solid rgba(37, 99, 235, 0.28);
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: statusSpin 0.9s linear infinite;
        }
        .status-select.status-en-cours {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1e3a8a;
        }
        .status-wrap.status-preleve { color: #166534; }
        .status-wrap.status-preleve .status-glyph::before {
            background: #16a34a;
            border-radius: 50%;
            animation: none;
        }
        .status-wrap.status-preleve .status-glyph::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            width: 4px;
            height: 8px;
            border-right: 2px solid #fff;
            border-bottom: 2px solid #fff;
            transform: translate(-45%, -58%) rotate(45deg);
        }
        .status-select.status-preleve {
            background: #f0fdf4;
            border-color: #86efac;
            color: #166534;
        }
        @keyframes statusSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .save-row {
            margin-top: 14px;
            display: flex; justify-content: flex-end;
        }

        @media (max-width: 700px) {
            .container-large { padding: 12px; }
            .top-bar { flex-direction: column; align-items: flex-start; gap: 10px; }
            .top-nav {
                width: 100%; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch;
                padding-bottom: 4px; gap: 8px;
            }
            .top-nav::-webkit-scrollbar { display: none; }
            .top-nav a {
                flex: 0 0 auto; font-size: 0.78rem; background: #fff;
                border: 1px solid #dce8e1; border-radius: 999px; padding: 8px 12px;
            }

            .toolbar { padding: 12px; }
            .toolbar-left, .toolbar-right, .year-form { width: 100%; }
            .year-form input, .year-form button { width: 100%; }
            .btn { justify-content: center; width: 100%; }

            .table-wrap, .table-wrap table, .table-wrap thead, .table-wrap tbody,
            .table-wrap tr, .table-wrap th, .table-wrap td { display: block; }
            .table-wrap thead { display: none; }
            .table-wrap tr {
                margin: 10px; border: 1px solid #e2e8f0; border-radius: 10px;
                overflow: hidden;
            }
            .table-wrap td { border-bottom: 1px solid #f1f5f9; }
            .table-wrap td:last-child { border-bottom: none; }
            .table-wrap td::before {
                content: attr(data-label);
                display: block; font-size: .72rem; text-transform: uppercase;
                color: #94a3b8; font-weight: 700; margin-bottom: 4px;
            }
            .status-wrap { max-width: none; }
            .status-select { min-width: 0; }
            .save-row { justify-content: stretch; }
            .btn.save { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="container-large">
    <div class="top-bar">
        <div class="brand"><h2><i class="fa-solid fa-receipt"></i> Declaration Mensuelle</h2></div>
        <div class="top-nav">
            <a href="<?= route('clients.index') ?>">🐾 Clients</a>
            <a href="<?= route('appointments.index') ?>">📅 Agenda</a>
            <a href="<?= route('facturation.index') ?>">🧾 Facturation</a>
            <a href="<?= route('declaration.index') ?>">📊 Declaration</a>
            <a href="<?= route('settings.index') ?>">⚙️ Parametres</a>
            <a href="<?= route('logout') ?>" style="color: #e63946;"><i class="fa-solid fa-right-from-bracket"></i> Deconnexion</a>
        </div>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            <a class="btn back" href="<?= route('declaration.index') ?>"><i class="fa-solid fa-arrow-left"></i> Retour declaration</a>
            <a class="btn invoices" href="<?= route('declaration.invoices') ?>"><i class="fa-solid fa-file-invoice"></i> Factures</a>
        </div>
        <div class="toolbar-right">
            <form class="year-form" method="GET" action="<?= route('declaration.monthly') ?>">
                <label for="annee">Annee</label>
                <input id="annee" name="annee" type="number" min="2020" max="2040" value="<?= (int)$annee ?>">
                <button type="submit">Afficher</button>
            </form>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="label">Chiffre d'affaires annuel</div>
            <div class="value"><?= number_format((float)$caAnnuel, 2, ',', ' ') ?> €</div>
        </div>
        <div style="font-weight:700; color:#475569;">Exercice <?= (int)$annee ?></div>
    </div>

    <?php if (!empty($saved)): ?>
        <div class="success-box"><i class="fa-solid fa-check"></i> Statuts URSSAF enregistres.</div>
    <?php endif; ?>

    <form method="POST" action="<?= route('declaration.monthly') ?>">
        <input type="hidden" name="annee" value="<?= (int)$annee ?>">

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th>Chiffre d'affaires realise</th>
                        <th>Prelevement URSSAF</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php $m = (int)$row['mois']; ?>
                    <?php $statusClass = (($row['statut'] ?? '') === 'preleve') ? 'status-preleve' : 'status-en-cours'; ?>
                    <tr>
                        <td class="month-cell" data-label="Mois"><?= htmlspecialchars($moisNoms[$m] ?? (string)$m) ?></td>
                        <td class="ca-cell" data-label="CA realise"><?= number_format((float)$row['ca'], 2, ',', ' ') ?> €</td>
                        <td data-label="Prelevement URSSAF">
                            <div class="status-wrap <?= $statusClass ?>">
                                <span class="status-glyph" aria-hidden="true"></span>
                                <select class="status-select <?= $statusClass ?>" name="statut[<?= $m ?>]">
                                    <option value="en_cours" <?= ($row['statut'] ?? '') === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                    <option value="preleve" <?= ($row['statut'] ?? '') === 'preleve' ? 'selected' : '' ?>>Preleve par l'URSSAF</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="save-row">
            <button type="submit" class="btn save"><i class="fa-solid fa-floppy-disk"></i> Enregistrer les statuts</button>
        </div>
    </form>
</div>
<script>
(function () {
    function applyStatusColor(selectEl) {
        selectEl.classList.remove('status-en-cours', 'status-preleve');
        var wrap = selectEl.closest('.status-wrap');
        if (wrap) {
            wrap.classList.remove('status-en-cours', 'status-preleve');
        }
        if (selectEl.value === 'preleve') {
            selectEl.classList.add('status-preleve');
            if (wrap) wrap.classList.add('status-preleve');
        } else {
            selectEl.classList.add('status-en-cours');
            if (wrap) wrap.classList.add('status-en-cours');
        }
    }

    var selects = document.querySelectorAll('.status-select');
    selects.forEach(function (selectEl) {
        applyStatusColor(selectEl);
        selectEl.addEventListener('change', function () {
            applyStatusColor(selectEl);
        });
    });
})();
</script>
</body>
</html>
