<?php
// 1. REGROUPEMENT DES DONNÉES PAR PROPRIÉTAIRE
$groupes = [];
foreach ($clients as $c) {
    $id_p = $c['id_proprietaire'];
    if (!isset($groupes[$id_p])) {
        $groupes[$id_p] = [
            'id'        => $id_p,
            'nom'       => $c['nom'],
            'prenom'    => $c['prenom'],
            'telephone' => $c['telephone'],
            'email'     => $c['email'] ?? '',
            'adresse'   => $c['adresse'] ?? '',
            'animaux'   => []
        ];
    }
    $groupes[$id_p]['animaux'][] = $c;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SweetyDog - Gestion Salon</title>

    <link rel="stylesheet" href="/assets/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* =========================
           PAGE LISTE CLIENTS (PROPRE)
           ========================= */

        /* On n'aligne PLUS la barre du haut sur le tableau : totalement dissocié */
        :root{
            --tbl-col-2: 200px; /* Race */
            --tbl-col-4: 180px; /* Action */
        }

        /* container-large existe déjà dans style.css, on l'améliore légèrement sans casser */
        .container-large{
            max-width: 1400px;
            padding: 25px;
        }

        /* TOP BAR */
        .top-bar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:20px;
            margin-bottom: 22px;
        }
        .brand h2{ margin:0; color: var(--vert-fonce); }

        .top-nav{
            display:flex;
            gap:16px;
            align-items:center;
            flex-wrap:wrap;
        }
        .top-nav a{
            text-decoration:none;
            color:#666;
            font-size:.9rem;
            font-weight:600;
        }
        .top-nav a:hover{ color: var(--vert-fonce); }

        /* LAYOUT */
        .dashboard-grid{
            display:grid;
            grid-template-columns: 1fr 320px;
            gap: 25px;
        }
        @media (max-width: 1200px){
            .dashboard-grid{ grid-template-columns: 1fr; }
        }

        /* =========================
           ACTION BAR (DISSOCIÉE)
           ========================= */
        .clients-actionbar{
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap:18px;
            margin-bottom:18px;
            flex-wrap:wrap;
        }

        .stats-box{
            display:flex;
            flex-direction:column;
            gap:4px;
        }
        .stats-label{
            color:#94a3b8;
            font-size:.75rem;
            text-transform:uppercase;
            font-weight:800;
            letter-spacing:.5px;
        }
        .stats-val{
            color: var(--vert-fonce);
            font-size: 1.6rem;
            font-weight: 900;
            line-height: 1.1;
        }

        .clients-actions-right{
            display:flex;
            align-items:center;
            justify-content:flex-end;
            gap:14px;
            flex: 1;
            min-width: 320px;
        }

        /* =========================
           SEARCH (BLOQUÉ / CENTRÉ)
           ========================= */
        .clients-search{
  position: relative;
  width: min(520px, 100%);
  height: 46px;
  flex: 1;
  min-width: 260px;
  margin: 0 !important;
  padding: 0 !important;
  display: flex !important;
  align-items: center !important;
}

.clients-search-input{
  width: 100% !important;
  height: 46px !important;
  padding: 0 44px 0 46px !important;
  border: 2px solid #dfe7e2 !important;
  border-radius: 14px !important;
  background: #fff !important;
  box-shadow: 0 6px 16px rgba(0,0,0,0.06) !important;
  font-size: .95rem !important;
  color: #111827 !important;
  box-sizing: border-box !important;
}

.clients-search-input:focus{
  outline: none !important;
  border-color: var(--vert-moyen) !important;
  box-shadow: 0 10px 22px rgba(0,0,0,0.10) !important;
}

/* ✅ LOUPE : centrage parfait, quoi que fasse style.css */
.clients-search-svg{
  position: absolute !important;
  left: 16px !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  width: 18px !important;
  height: 18px !important;
  display: block !important;
  pointer-events: none !important;
  opacity: .75 !important;
  margin: 0 !important;
}

/* ✅ CROIX : centrage parfait */
.clients-search-clear{
  position: absolute !important;
  right: 6px !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  width: 40px !important;
  height: 40px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  text-decoration: none !important;
  font-size: 18px !important;
  font-weight: 900 !important;
  color: #64748b !important;
  border-radius: 12px !important;
}

.clients-search-clear:hover{
  color: #111827 !important;
  background: #eef2f7 !important;
}


        /* Bouton NOUVEAU */
        .clients-btn-new{
            height: 46px;
            padding: 0 18px !important;
            border-radius: 14px;
            background: var(--vert-fonce) !important;
            color: white !important;
            text-decoration: none;
            font-weight: 900;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px;
            white-space: nowrap;
            transition: .2s ease;
            flex-shrink: 0;
            border: none !important;
            cursor: pointer !important;
            font-size: 1rem !important;
            margin: 0 !important;
            line-height: 1 !important;
        }
        .clients-btn-new:hover{
            background: var(--vert-moyen);
            transform: translateY(-1px);
        }

        @media (max-width: 850px){
            .clients-actions-right{
                width:100%;
                min-width: 0;
                flex-wrap:wrap;
                justify-content:stretch;
            }
            .clients-search{ width:100%; min-width:0; }
            .clients-btn-new{ width:100%; }
        }

        /* =========================
           TABLE
           ========================= */
        .table-container{
            background:white;
            border-radius: 12px;
            overflow:hidden;
            border:1px solid #f1f5f9;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th:nth-child(2), td:nth-child(2){ width: 160px; }
        th:nth-child(3), td:nth-child(3){ width: 120px; }
        th:nth-child(4), td:nth-child(4){ width: 180px; }
        th:nth-child(5), td:nth-child(5){ width: 100px; }

        th{
            text-align:left;
            padding:15px 20px;
            background: var(--vert-fonce);
            color:white;
            font-size:.75rem;
            text-transform:uppercase;
            letter-spacing:.6px;
        }
        td{
            padding:12px 20px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align:middle;
        }

        .row-proprio{ background:#f8fafc; }
        .proprio-name{
            font-weight:900;
            color: var(--vert-fonce);
            font-size: 1.05rem;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            gap:8px;
        }

        .dog-indent{ padding-left:45px !important; position:relative; }

        .info-tag{
            padding:5px 12px;
            border-radius:20px;
            font-size:.78rem;
            font-weight:600;
            background:#f0fdf4;
            color: var(--vert-fonce);
            display:inline-block;
            border: 1px solid #d1e7dd;
            letter-spacing: 0.2px;
        }
        .info-tag-sexe{
            padding:5px 10px;
            border-radius:20px;
            font-size:.78rem;
            font-weight:700;
            display:inline-block;
            white-space:nowrap;
        }

        .btn-patte{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            background: var(--vert-moyen);
            width:38px;
            height:38px;
            border-radius:10px;
            text-decoration:none;
            transition:.15s ease;
            color: #fff;
        }
        .btn-patte:hover{ background: var(--vert-fonce); transform: scale(1.05); }

        .agenda-card{
            background:#fafbfc;
            border:1px solid #edf2f7;
            border-radius:15px;
            padding:20px;
        }
        .rdv-item{
            background:white;
            padding:12px;
            border-radius:12px;
            margin-bottom:12px;
            border-left:4px solid var(--vert-moyen);
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        /* MODAL (communs) */
        .modal-overlay{
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.35);
            display:flex; align-items:center; justify-content:center;
            padding: 20px;
            z-index: 9999;
        }
        .modal-card{
            width: min(650px, 100%);
            background: white;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.20);
        }
        .modal-close{
            width: 40px; height: 40px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: 22px;
            cursor: pointer;
        }
        .btn-action{
            padding: 10px 14px;
            border-radius: 12px;
            text-decoration:none;
            font-weight:800;
            display:inline-flex;
            align-items:center;
            gap:8px;
        }

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
            .brand h2 { font-size: 1.2rem; }
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

            .dashboard-grid { grid-template-columns: 1fr !important; gap: 15px; }

            .clients-actionbar {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                background: linear-gradient(180deg, #ffffff 0%, #f5fbf7 100%);
                padding: 12px;
                border-radius: 14px;
                border: 1px solid #e6efe9;
                position: sticky;
                top: 8px;
                z-index: 7;
                box-shadow: 0 8px 22px rgba(15, 23, 42, 0.07);
            }
            .clients-actions-right {
                min-width: 0 !important;
                flex-direction: column;
                gap: 10px;
            }
            .clients-search { min-width: 0 !important; width: 100% !important; }
            .clients-btn-new { width: 100% !important; justify-content: center !important; }

            /* Tableau en mode carte */
            .table-container { overflow-x: auto; }
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }

            tr {
                margin-bottom: 10px;
                border: 1px solid #f1f5f9;
                border-radius: 12px;
                overflow: hidden;
                background: white;
            }
            tr.row-proprio {
                background: #f0fdf4;
                border: 1px solid #d1e7dd;
            }

            td {
                padding: 8px 15px !important;
                border-bottom: none !important;
                text-align: left !important;
                position: relative;
            }

            /* Cacher les cellules vides */
            td:empty { display: none; padding: 0 !important; }

            .dog-indent { padding-left: 15px !important; }

            .btn-patte { width: 34px; height: 34px; }

            /* Sexe et Race sur la même ligne dans les cartes animaux */
            .info-tag { font-size: 0.72rem; padding: 3px 8px; }
            .info-tag-sexe { font-size: 0.72rem; padding: 3px 8px; }

            .proprio-name { font-size: 0.95rem; }

            /* Agenda card */
            .agenda-card { padding: 15px; }
            .agenda-card h3 { font-size: 0.8rem !important; }

            /* Modal */
            .modal-card { padding: 15px; width: 95%; }
            .btn-action { padding: 8px 12px; font-size: 0.85rem; }

            /* Refonte visuelle de la liste clients en cartes */
            .table-container {
                background: transparent !important;
                border: none !important;
                overflow: visible !important;
            }
            tbody {
                display: block;
            }

            tbody tr.row-proprio {
                display: grid !important;
                grid-template-columns: 1fr !important;
                margin: 0 0 6px 0 !important;
                border-radius: 14px !important;
                border: 1px solid #d7e8dc !important;
                background: linear-gradient(180deg, #f0fdf4 0%, #e9f9ef 100%) !important;
                box-shadow: 0 8px 18px rgba(15,23,42,0.06);
            }
            tbody tr.row-proprio td {
                padding: 9px 12px !important;
                border: none !important;
            }
            tbody tr.row-proprio td:nth-child(2),
            tbody tr.row-proprio td:nth-child(3),
            tbody tr.row-proprio td:nth-child(5) {
                display: none !important;
            }
            tbody tr.row-proprio td:nth-child(4) {
                background: rgba(255,255,255,0.75);
                border: 1px dashed #c8d9cc !important;
                border-radius: 10px;
                margin: 0 12px 10px 12px;
                padding: 8px 10px !important;
            }
            .proprio-name {
                font-size: 0.98rem;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            tbody tr:not(.row-proprio) {
                display: grid !important;
                grid-template-columns: 1fr auto !important;
                grid-template-areas:
                    "name action"
                    "race sexe";
                gap: 6px 10px;
                margin: 0 0 10px 12px !important;
                border: 1px solid #e6eef3 !important;
                border-left: 4px solid #d1e7dd !important;
                border-radius: 12px !important;
                background: #ffffff !important;
                box-shadow: 0 8px 16px rgba(15,23,42,0.05);
            }
            tbody tr:not(.row-proprio) td {
                border: none !important;
                padding: 8px 10px !important;
            }
            tbody tr:not(.row-proprio) td:nth-child(1) { grid-area: name; }
            tbody tr:not(.row-proprio) td:nth-child(2) { grid-area: race; padding-top: 0 !important; }
            tbody tr:not(.row-proprio) td:nth-child(3) { grid-area: sexe; padding-top: 0 !important; }
            tbody tr:not(.row-proprio) td:nth-child(4) { display: none !important; }
            tbody tr:not(.row-proprio) td:nth-child(5) {
                grid-area: action;
                align-self: start;
                justify-self: end;
            }
            .dog-indent {
                padding-left: 10px !important;
                font-size: 0.96rem;
            }
            .info-tag,
            .info-tag-sexe {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                min-height: 26px;
                border-radius: 999px !important;
                font-size: 0.72rem !important;
                font-weight: 700 !important;
            }
            .btn-patte {
                width: 38px;
                height: 38px;
                border-radius: 11px;
                box-shadow: 0 6px 14px rgba(45,106,79,0.25);
            }

            .agenda-card {
                margin-top: 8px;
                border-radius: 14px;
                border: 1px solid #e4efe8;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }
            .rdv-item {
                border-radius: 10px;
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            .top-nav { gap: 8px; }
            .top-nav a { font-size: 0.75rem; }
            .brand h2 { font-size: 1rem; }
            .stats-val { font-size: 1.3rem; }
        }
    </style>
</head>
<body>

<div class="container-large">

    <?php if (!empty($_GET['success']) && $_GET['success'] === 'updated'): ?>
        <div class="alert alert-success">✅ Client modifié.</div>
    <?php endif; ?>

    <?php if (!empty($_GET['success']) && $_GET['success'] === 'deleted'): ?>
        <div class="alert alert-success">✅ Client supprimé.</div>
    <?php endif; ?>

    <?php if (!empty($_GET['error']) && $_GET['error'] === 'delete_failed'): ?>
        <div class="alert alert-error">❌ Suppression impossible.</div>
    <?php endif; ?>

    <div class="top-bar">
        <div class="brand"><h2>🐾 SweetyDog</h2></div>
        <div class="top-nav">
            <a href="<?= htmlspecialchars(route('appointments.index')) ?>">📅 Agenda</a>
            <a href="<?= htmlspecialchars(route('facturation.index')) ?>">🧾 Facturation</a>
            <a href="<?= htmlspecialchars(route('declaration.index')) ?>">📊 Déclaration</a>
            <a href="<?= htmlspecialchars(route('settings.index')) ?>">⚙️ Paramètres</a>

        

            <a href="<?= route('logout') ?>" style="color: #e63946;"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="main-content">

            <!-- ✅ BARRE DU HAUT DISSOCIÉE DU TABLEAU -->
            <div class="clients-actionbar">
                <div class="stats-box">
                    <span class="stats-label">Propriétaires</span>
                    <span class="stats-val"><?php echo count($groupes); ?></span>
                </div>

                <div class="clients-actions-right">
                    <div class="clients-search">

  <svg class="clients-search-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
  </svg>

  <input type="text"
         id="live-search-input"
         class="clients-search-input"
         placeholder="Rechercher..."
         autocomplete="off">

  <a class="clients-search-clear" id="live-search-clear"
     href="#" style="display:none;"
     aria-label="Effacer la recherche">×</a>
</div>

                    <a href="<?= route('clients.create') ?>" class="clients-btn-new">
                        <span>+</span> NOUVEAU
                    </a>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>Propriétaire / Animal</th>
                        <th>Race</th>
                        <th>Sexe</th>
                        <th>Contact</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($groupes)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">
                                Aucun résultat trouvé.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($groupes as $id_p => $data): ?>
                        <tr class="row-proprio">
                            <td>
                                <span class="proprio-name"
                                      data-id="<?= (int)$data['id'] ?>"
                                      data-name="<?= htmlspecialchars($data['prenom'] . ' ' . $data['nom']) ?>"
                                      data-tel="<?= htmlspecialchars($data['telephone'] ?? '') ?>"
                                      data-email="<?= htmlspecialchars($data['email'] ?? '') ?>"
                                      data-adresse="<?= htmlspecialchars(str_replace("\n", ' - ', $data['adresse'] ?? '')) ?>">
                                    👤 <?= htmlspecialchars($data['prenom'] . ' ' . $data['nom']); ?>
                                    <small style="font-weight:600; color:#888; font-size:.8rem;">
                                        (<?= count($data['animaux']); ?>)
                                    </small>
                                </span>
                            </td>

                            <td></td>
                            <td></td>

                            <td style="font-family: monospace; font-weight: 800; color: var(--vert-fonce);">
                                📞 <?= htmlspecialchars(wordwrap($data['telephone'], 2, ' ', true)); ?>
                            </td>

                            <td></td>
                        </tr>

                        <?php foreach ($data['animaux'] as $animal): ?>
                            <tr>
                                <td class="dog-indent">
                                    <?php
                                        $espece = strtolower($animal['espece'] ?? '');
                                        if ($espece === 'chat') {
                                            $icone = '<i class="fa-solid fa-cat" style="color:#94a3b8; margin-right:6px;"></i>';
                                        } elseif ($espece === 'lapin') {
                                            $icone = '🐰';
                                        } else {
                                            $icone = '<i class="fa-solid fa-dog" style="color:#94a3b8; margin-right:6px;"></i>';
                                        }
                                    ?>
                                    <a href="<?= route('animals.edit', ['id' => $animal['id_animal']]) ?>"
                                       style="text-decoration:none; color: inherit;">
                                        <?= $icone ?> <strong><?= htmlspecialchars($animal['nom_animal']); ?></strong>
                                    </a>
                                </td>
                                <td>
                                    <span class="info-tag"><?= htmlspecialchars($animal['race'] ?: 'Non précisée'); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($animal['sexe'])): ?>
                                        <span class="info-tag-sexe" style="<?= $animal['sexe'] === 'M' ? 'background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe;' : 'background:#fce7f3; color:#be185d; border:1px solid #fbcfe8;' ?>">
                                            <?= $animal['sexe'] === 'M' ? '♂ Mâle' : '♀ Femelle' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td></td>
                                <td style="text-align:right;">
                                    <a href="<?= route('animals.tracking', ['id' => $animal['id_animal']]) ?>"
   class="btn-patte" title="Suivi toilettage">
    <i class="fa-solid fa-paw"></i>
</a>


                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="agenda-card">
            <h3 style="margin-top:0; font-size:.9rem; color:#64748b; letter-spacing:1px;">📅 RDV DU JOUR</h3>
            <div style="margin: 15px 0; height: 1px; background: #e2e8f0;"></div>

            <?php if (empty($rdv_du_jour)): ?>
                <div style="text-align:center; padding:20px;">
                    <p style="color:#94a3b8; font-size:.85rem;">Aucun RDV.</p>
                </div>
            <?php else: ?>
                <?php foreach ($rdv_du_jour as $rdv): ?>
                    <div class="rdv-item">
                        <span style="color: var(--vert-fonce); font-weight:900; font-size:.8rem;">
                            <?= date('H:i', strtotime($rdv['date_debut'])); ?>
                        </span><br>
                        <strong style="font-size:.95rem; color:#1e293b;">
                            <?= htmlspecialchars($rdv['nom_animal']); ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- MODAL INFOS CLIENT -->
<div id="clientInfoModal" class="modal-overlay" style="display:none;">
    <div class="modal-card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <h3 id="ci_title" style="margin:0;">👤 Infos client</h3>
            <button type="button" class="btn-action" onclick="closeClientInfo()" style="background:#fff; border:1px solid #ddd; color:#333;">Fermer</button>
        </div>

        <div style="margin-top:12px; line-height:1.6;">
            <div><strong>Téléphone :</strong> <span id="ci_tel">—</span></div>
            <div><strong>Email :</strong> <span id="ci_email">—</span></div>
            <div><strong>Adresse :</strong> <span id="ci_adresse">—</span></div>
        </div>

        <div style="display:flex; gap:10px; margin-top:16px; flex-wrap:wrap;">
            <a id="ci_call" class="btn-action" href="#" style="background:var(--vert-fonce); color:white; border:none;">📞 Appel</a>
            <a id="ci_mail" class="btn-action" href="#" style="background:#0ea5e9; color:white; border:none;">✉️ Mail</a>
            <a id="ci_edit" class="btn-action" href="#" style="background:#111827; color:white; border:none;">✏️ Modifier</a>
            
        </div>
    </div>
</div>

<script>
    const editTemplate = <?= json_encode(route('clients.edit', ['id' => '__ID__'])) ?>;

    function formatTel(t) {
        var digits = t.replace(/\s+/g, '');
        return digits.replace(/(.{2})/g, '$1 ').trim();
    }

    function openClientInfo(id, fullName, tel, email, adresse){
        document.getElementById('ci_title').textContent = '👤 ' + (fullName || 'Infos client');

        document.getElementById('ci_tel').textContent = tel ? formatTel(tel) : 'Non renseigné';
        document.getElementById('ci_email').textContent = email ? email : 'Non renseigné';
        document.getElementById('ci_adresse').textContent = adresse ? adresse : 'Non renseignée';

        const callBtn = document.getElementById('ci_call');
        callBtn.href = tel ? ('tel:' + tel.replace(/\s+/g, '')) : '#';
        callBtn.style.opacity = tel ? '1' : '0.4';
        callBtn.style.pointerEvents = tel ? 'auto' : 'none';

        const mailBtn = document.getElementById('ci_mail');
        mailBtn.href = email ? ('mailto:' + email) : '#';
        mailBtn.style.opacity = email ? '1' : '0.4';
        mailBtn.style.pointerEvents = email ? 'auto' : 'none';

        document.getElementById('ci_edit').href = editTemplate.replace('__ID__', String(id));
        document.getElementById('clientInfoModal').style.display = 'flex';
    }

function closeClientInfo(){
    document.getElementById('clientInfoModal').style.display = 'none';
}

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('clientInfoModal');

    if (!modal) {
        console.warn('clientInfoModal introuvable dans le DOM');
        return;
    }

    // Clic sur les noms de propriétaires
    document.querySelectorAll('.proprio-name').forEach(function(el) {
        el.addEventListener('click', function() {
            openClientInfo(
                parseInt(this.dataset.id),
                this.dataset.name,
                this.dataset.tel,
                this.dataset.email,
                this.dataset.adresse
            );
        });
    });

    // Fermer si clic sur l'overlay
    modal.addEventListener('click', function(e){
        if (e.target === this) closeClientInfo();
    });

    // Fermer avec ESC
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') closeClientInfo();
    });

    // ===== RECHERCHE EN TEMPS RÉEL =====
    var searchInput = document.getElementById('live-search-input');
    var clearBtn = document.getElementById('live-search-clear');
    var tbody = document.querySelector('.table-container tbody');

    if (searchInput && clearBtn && tbody) {
        function norm(str) {
            var value = (str || '').toLowerCase();
            if (typeof value.normalize === 'function') {
                value = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }
            return value;
        }

        var rows = Array.from(tbody.querySelectorAll('tr'));
        var groups = [];
        var currentGroup = null;

        rows.forEach(function(row) {
            if (row.classList.contains('row-proprio')) {
                currentGroup = { owner: row, animals: [] };
                groups.push(currentGroup);
                return;
            }

            if (currentGroup) {
                currentGroup.animals.push(row);
            }
        });

        function showRow(row, visible) {
            if (visible) {
                row.style.removeProperty('display');
                row.removeAttribute('aria-hidden');
                return;
            }

            // En mobile certaines règles CSS utilisent `display: ... !important`.
            // On force donc aussi `none !important` pour que le filtre reste fiable.
            row.style.setProperty('display', 'none', 'important');
            row.setAttribute('aria-hidden', 'true');
        }

        function filterClients() {
            var query = norm(searchInput.value.trim());
            clearBtn.style.display = query ? 'flex' : 'none';

            groups.forEach(function(group) {
                var ownerText = norm(group.owner.textContent);
                var ownerMatches = query === '' || ownerText.indexOf(query) !== -1;

                if (query === '') {
                    showRow(group.owner, true);
                    group.animals.forEach(function(animalRow) {
                        showRow(animalRow, true);
                    });
                    return;
                }

                if (ownerMatches) {
                    showRow(group.owner, true);
                    group.animals.forEach(function(animalRow) {
                        showRow(animalRow, true);
                    });
                    return;
                }

                var hasAnimalMatch = false;
                group.animals.forEach(function(animalRow) {
                    var matches = norm(animalRow.textContent).indexOf(query) !== -1;
                    showRow(animalRow, matches);
                    if (matches) {
                        hasAnimalMatch = true;
                    }
                });

                showRow(group.owner, hasAnimalMatch);
            });
        }

        // Mobile (iOS/Android): certains claviers prédictifs ne déclenchent pas
        // toujours "input" de manière fiable, on écoute plusieurs événements.
        function bindLiveSearch() {
            ['input', 'beforeinput', 'textInput', 'keyup', 'change', 'search', 'compositionend'].forEach(function(evt) {
                searchInput.addEventListener(evt, filterClients);
            });
        }
        bindLiveSearch();

        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.value = '';
            filterClients();
            searchInput.focus();
        });

        filterClients();
    }

});
</script>


</body>
</html>
