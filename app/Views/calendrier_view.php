<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda | SweetyDog</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/style.css')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #333; }

        .container-large { max-width: 1100px; padding: 25px; margin: 0 auto; }

        /* TOP BAR (identique aux autres pages) */
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            gap: 20px; margin-bottom: 25px;
        }
        .brand h2 { margin: 0; color: var(--vert-fonce); }
        .top-nav { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .top-nav a { text-decoration: none; color: #666; font-size: .9rem; font-weight: 600; }
        .top-nav a:hover { color: var(--vert-fonce); }
        .top-nav a.active { color: var(--vert-fonce); border-bottom: 2px solid var(--vert-fonce); padding-bottom: 2px; }

        /* Calendrier */
        #calendar {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
        }
        .mobile-agenda-controls {
            display: none;
        }

        /* Toolbar FullCalendar */
        .fc .fc-toolbar-title {
            font-size: 1.1rem !important;
            font-weight: 700 !important;
            color: var(--vert-fonce) !important;
        }

        .fc .fc-button {
            background: white !important;
            border: 2px solid #e2e8f0 !important;
            color: #475569 !important;
            border-radius: 10px !important;
            padding: 6px 14px !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            box-shadow: none !important;
            text-transform: none !important;
            transition: all 0.2s ease !important;
        }

        .fc .fc-button:hover {
            background: #f8fafc !important;
            border-color: var(--vert-moyen) !important;
            color: var(--vert-fonce) !important;
        }

        .fc .fc-button-active {
            background: var(--vert-fonce) !important;
            border-color: var(--vert-fonce) !important;
            color: white !important;
        }

        .fc .fc-button-group > .fc-button { border-radius: 0 !important; }
        .fc .fc-button-group > .fc-button:first-child { border-radius: 10px 0 0 10px !important; }
        .fc .fc-button-group > .fc-button:last-child { border-radius: 0 10px 10px 0 !important; }

        /* En-têtes jours */
        .fc .fc-col-header-cell {
            background: #f8fafc !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 10px 0 !important;
        }

        .fc .fc-col-header-cell-cushion {
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.78rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.6px !important;
        }

        /* Grille */
        .fc td, .fc th { border-color: #f1f5f9 !important; }
        .fc .fc-timegrid-slot { height: 2.5em !important; }
        .fc .fc-timegrid-slot-label { font-size: 0.75rem !important; color: #94a3b8 !important; font-weight: 600 !important; }

        /* Aujourd'hui */
        .fc .fc-day-today { background: rgba(45, 106, 79, 0.04) !important; }

        /* Événements */
        .fc-event {
            background: var(--vert-fonce) !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 3px 8px !important;
            font-size: 0.78rem !important;
            cursor: pointer !important;
            box-shadow: 0 2px 6px rgba(45, 106, 79, 0.2) !important;
            transition: all 0.2s ease !important;
        }
        .fc-event:hover { background: var(--vert-moyen) !important; transform: translateY(-1px); }
        .fc-event-title { font-weight: 600 !important; }
        .fc-event-time { font-size: 0.7rem !important; opacity: 0.85 !important; }

        /* Vue jour */
        .fc-timeGridDay-view .fc-timegrid-slot { height: 3em !important; }
        .fc-timeGridDay-view .fc-event { border-radius: 8px !important; padding: 6px 10px !important; font-size: 0.85rem !important; }

        /* Vue mois */
        .fc-dayGridMonth-view .fc-daygrid-day-number { color: #475569 !important; font-size: 0.85rem !important; padding: 8px !important; font-weight: 600 !important; }
        .fc-dayGridMonth-view .fc-daygrid-day-frame { min-height: 90px !important; }
        .fc-dayGridMonth-view .fc-event {
            padding: 4px 8px !important;
            font-size: 0.78rem !important;
            border-radius: 6px !important;
            white-space: normal !important;
            overflow: visible !important;
        }
        .fc-dayGridMonth-view .fc-daygrid-event-dot { display: none !important; }
        .fc-dayGridMonth-view .fc-event-time {
            font-weight: 700 !important;
            font-size: 0.72rem !important;
            opacity: 1 !important;
        }
        .fc-dayGridMonth-view .fc-event-title {
            font-weight: 600 !important;
            font-size: 0.75rem !important;
        }
        .fc-dayGridMonth-view .fc-daygrid-event {
            white-space: normal !important;
            overflow: visible !important;
        }
        /* Forcer affichage en bloc (pas en ligne avec un point) */
        .fc-dayGridMonth-view .fc-daygrid-block-event .fc-event-time,
        .fc-dayGridMonth-view .fc-daygrid-block-event .fc-event-title { display: inline !important; }
        .fc-dayGridMonth-view .fc-daygrid-dot-event { display: flex !important; align-items: center !important; padding: 4px 6px !important; background: var(--vert-fonce) !important; border-radius: 6px !important; color: white !important; }
        .fc-dayGridMonth-view .fc-daygrid-dot-event .fc-event-title { color: white !important; }
        .fc-dayGridMonth-view .fc-daygrid-dot-event .fc-event-time { color: rgba(255,255,255,0.85) !important; }

        /* Indicateur heure actuelle */
        .fc .fc-timegrid-now-indicator-line { border-color: #e63946 !important; border-width: 2px !important; }

        /* Fonds de cellules */
        .fc .fc-bg-event.fc-bg-weekend { background: rgba(187, 247, 208, 0.35) !important; }
        .fc .fc-bg-event.fc-bg-holiday { background: rgba(201, 172, 138, 0.45) !important; }
        .fc .fc-bg-event.fc-bg-vacation { background: rgba(251, 191, 36, 0.32) !important; }
        .fc .fc-daygrid-day.fc-day-weekend-cell { background: rgba(187, 247, 208, 0.35) !important; }
        .fc .fc-daygrid-day.fc-day-holiday-cell { background: rgba(201, 172, 138, 0.45) !important; }
        .fc .fc-daygrid-day-frame {
            position: relative;
        }
        .fc .fc-holiday-name {
            font-size: 0.62rem;
            line-height: 1.15;
            font-weight: 700;
            color: #7c4a1f;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 6px;
            padding: 2px 6px;
            position: absolute;
            top: 2px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3;
            pointer-events: none;
            max-width: calc(100% - 12px);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
        }

        /* Bouton mode vacances actif */
        .fc .fc-vacationToggle-button.vacation-active {
            background: #f59e0b !important;
            border-color: #f59e0b !important;
            color: #fff !important;
        }

        /* ========== MODALS ========== */
        #modalRDV, #modalEditRDV, #popupActionsRDV {
            display: none; position: fixed; z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
        }
        #modalEditRDV { z-index: 1002; }
        #popupActionsRDV { z-index: 1001; }

        .modal-content {
            background: white; max-width: 420px; margin: 6% auto; padding: 28px;
            border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            animation: modalIn 0.3s ease;
        }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        .modal-content h3 {
            font-size: 1.15rem; font-weight: 700; color: var(--vert-fonce);
            margin: 0 0 20px 0; padding: 0; border: none;
        }

        .search-input {
            width: 100%; padding: 11px 14px; margin: 6px 0;
            border: 2px solid #e2e8f0; border-radius: 10px;
            font-size: 0.95rem; box-sizing: border-box;
            background: #f8fafc; transition: border-color 0.2s;
        }
        .search-input:focus { outline: none; border-color: var(--vert-moyen); background: #fff; }

        label { font-size: 0.85rem; font-weight: 600; color: #475569; margin-top: 12px; display: block; }

        .btn-confirm {
            width: 100%; background: var(--vert-fonce); color: white; padding: 13px;
            border: none; border-radius: 10px; font-weight: 700; margin-top: 20px;
            font-size: 0.95rem; cursor: pointer; transition: all 0.3s ease;
        }
        .btn-confirm:hover { background: #1b4332; transform: translateY(-1px); }

        /* Popup actions */
        .popup-actions-content {
            background: white; max-width: 340px; margin: 12% auto;
            border-radius: 16px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            animation: modalIn 0.3s ease;
        }

        .popup-header {
            padding: 18px 22px; border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
        }
        .popup-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--vert-fonce); border: none; padding: 0; }

        .popup-close {
            position: absolute; right: 16px; top: 14px; background: none;
            border: none; font-size: 22px; color: #94a3b8; cursor: pointer; padding: 4px;
            transition: color 0.2s;
        }
        .popup-close:hover { color: #333; }

        .popup-actions-list { padding: 8px 0; }

        .popup-action-item {
            display: flex; align-items: center; padding: 13px 22px; cursor: pointer;
            text-decoration: none; color: #1e293b; border: none; background: none;
            width: 100%; font-size: 0.9rem; font-weight: 600; text-align: left;
            transition: all 0.15s ease;
        }
        .popup-action-item:hover { background: #f0fdf4; color: var(--vert-fonce); }
        .popup-action-item.danger:hover { background: #fef2f2; color: #dc2626; }

        .popup-action-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 14px; font-size: 1rem; background: #f1f5f9;
        }

        .close-modal {
            position: absolute; right: 16px; top: 16px; font-size: 22px;
            color: #94a3b8; cursor: pointer; background: none; border: none; padding: 0;
            transition: color 0.2s;
        }
        .close-modal:hover { color: #333; }

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

            #calendar {
                padding: 8px !important;
                border-radius: 16px;
                box-shadow: inset 0 0 0 1px #ecf4ee;
                min-height: 70vh;
            }
            .mobile-agenda-controls {
                display: block;
                margin-bottom: 10px;
                background: #ffffff;
                border: 1px solid #e3ece6;
                border-radius: 14px;
                padding: 8px;
                box-shadow: 0 8px 20px rgba(15,23,42,0.08);
            }
            .mobile-agenda-row {
                display: flex;
                gap: 8px;
                align-items: center;
                margin-bottom: 8px;
            }
            .mobile-agenda-row:last-child { margin-bottom: 0; }
            .mobile-agenda-btn {
                min-height: 42px;
                border: 1px solid #d7e3dc;
                border-radius: 10px;
                background: #f8fcfa;
                color: #1f2937;
                font-size: 0.82rem;
                font-weight: 700;
                padding: 0 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 1;
            }
            .mobile-agenda-btn.icon-btn {
                flex: 0 0 42px;
                padding: 0;
            }
            .mobile-agenda-btn.active {
                background: var(--vert-fonce);
                border-color: var(--vert-fonce);
                color: #fff;
            }
            #mobile-current-label {
                flex: 1;
                min-height: 42px;
                border-radius: 10px;
                border: 1px solid #d7e3dc;
                background: #f8fcfa;
                color: #1e293b;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                font-weight: 800;
                text-align: center;
                padding: 0 8px;
            }

            /* FullCalendar mobile */
            .fc .fc-toolbar { display: none !important; }
            .fc .fc-view-harness { min-height: 62vh; }
            .fc .fc-toolbar-title { font-size: 0.95rem !important; }
            .fc .fc-button {
                padding: 6px 10px !important;
                font-size: 0.74rem !important;
                border-radius: 10px !important;
            }

            .fc .fc-col-header-cell-cushion { font-size: 0.65rem !important; }
            .fc .fc-timegrid-slot-label { font-size: 0.7rem !important; }
            .fc .fc-timegrid-slot { height: 3.7em !important; }
            .fc-event { font-size: 0.7rem !important; padding: 2px 4px !important; }
            .fc-timeGridDay-view .fc-event {
                padding: 7px 8px !important;
                border-radius: 10px !important;
                font-size: 0.82rem !important;
            }

            /* Modals en bottom-sheet */
            .modal-content {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 16px 14px 86px !important;
                border-radius: 18px 18px 0 0 !important;
                position: absolute !important;
                left: 0;
                right: 0;
                bottom: 0;
                max-height: 86vh;
                overflow: auto;
            }
            .popup-actions-content {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                border-radius: 18px 18px 0 0 !important;
                position: absolute !important;
                left: 0;
                right: 0;
                bottom: 0;
            }
            .close-modal,
            .popup-close {
                width: 42px;
                height: 42px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }
            .btn-confirm {
                position: sticky;
                bottom: calc(env(safe-area-inset-bottom) + 6px);
                z-index: 4;
                box-shadow: 0 10px 24px rgba(15,23,42,0.14);
            }

            .search-input { font-size: 0.9rem; }
            .btn-confirm { font-size: 0.9rem; padding: 12px; }

            /* Grille date début/fin en colonne */
            .modal-content div[style*="grid-template-columns: 1fr 1fr"] {
                display: flex !important;
                flex-direction: column !important;
                gap: 8px !important;
            }
        }

        @media (max-width: 480px) {
            .brand h2 { font-size: 1rem; }
            .top-nav a { font-size: 0.72rem; }
            .fc .fc-toolbar-title { font-size: 0.85rem !important; }
        }
    </style>
</head>
<body>

<div class="container-large">

    <!-- TOP BAR (identique aux autres pages) -->
    <div class="top-bar">
        <div class="brand"><h2>📅 Agenda</h2></div>
        <div class="top-nav">
            <a href="<?= htmlspecialchars(route('clients.index')) ?>">🐾 Clients</a>
            <a href="<?= htmlspecialchars(route('facturation.index')) ?>">🧾 Facturation</a>
            <a href="<?= htmlspecialchars(route('declaration.index')) ?>">📊 Déclaration</a>
            <a href="<?= htmlspecialchars(route('settings.index')) ?>">⚙️ Paramètres</a>
            <a href="<?= route('logout') ?>" style="color: #e63946;"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        </div>
    </div>

    <div class="mobile-agenda-controls" id="mobile-agenda-controls">
        <div class="mobile-agenda-row">
            <button type="button" class="mobile-agenda-btn icon-btn" id="mobile-prev" aria-label="Précédent">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div id="mobile-current-label">Aujourd'hui</div>
            <button type="button" class="mobile-agenda-btn icon-btn" id="mobile-next" aria-label="Suivant">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
        <div class="mobile-agenda-row">
            <button type="button" class="mobile-agenda-btn" id="mobile-today">Aujourd'hui</button>
            <button type="button" class="mobile-agenda-btn" id="mobile-view-day">Jour</button>
            <button type="button" class="mobile-agenda-btn" id="mobile-view-week">Semaine</button>
            <button type="button" class="mobile-agenda-btn" id="mobile-view-month">Mois</button>
        </div>
        <div class="mobile-agenda-row">
            <button type="button" class="mobile-agenda-btn" id="mobile-new-rdv">+ Nouveau RDV</button>
            <button type="button" class="mobile-agenda-btn" id="mobile-vacation-toggle">Vacances: OFF</button>
        </div>
    </div>

    <div id="calendar"></div>
    <p style="margin:10px 4px 0; color:#64748b; font-size:.85rem;">
        Astuce : activez <strong>Vacances: ON</strong> puis cliquez sur un jour pour le colorer. Recliquez pour l’enlever.
    </p>
</div>

<!-- Modal Nouveau RDV -->
<div id="modalRDV">
    <div class="modal-content" style="position:relative;">
        <button class="close-modal" onclick="document.getElementById('modalRDV').style.display='none'">&times;</button>
        <h3>Nouveau rendez-vous</h3>

        <form action="<?= htmlspecialchars(route('appointments.create')) ?>" method="POST" id="formRDV">
            <label>Animal</label>
            <input list="liste_animaux_dl" id="animal_input" class="search-input" placeholder="Rechercher..." required autocomplete="off">
            <input type="hidden" name="id_animal" id="id_animal_hidden">
            <input type="hidden" name="return_view" id="return_view_hidden">
            <input type="hidden" name="return_date" id="return_date_hidden">

            <datalist id="liste_animaux_dl">
                <?php foreach($liste_animaux as $a): ?>
                    <?php $info_complet = htmlspecialchars($a['nom_animal']) . " - " . htmlspecialchars($a['prenom_client'] . " " . $a['nom_client']); ?>
                    <option data-id="<?php echo $a['id_animal']; ?>" value="<?php echo $info_complet; ?>">
                <?php endforeach; ?>
            </datalist>

            <label>Prestation</label>
            <input type="text" name="titre" placeholder="Ex: Toilettage complet" required class="search-input">

            <label>Date</label>
            <input type="text" id="input_date_display" class="search-input" placeholder="jj/mm/aaaa" maxlength="10" autocomplete="off" inputmode="numeric">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label>Heure début</label>
                    <input type="text" id="input_heure_debut" class="search-input" placeholder="HH:MM" maxlength="5" autocomplete="off" inputmode="numeric">
                </div>
                <div>
                    <label>Heure fin</label>
                    <input type="text" id="input_heure_fin" class="search-input" placeholder="HH:MM" maxlength="5" autocomplete="off" inputmode="numeric">
                </div>
            </div>

            <input type="hidden" name="date_debut" id="input_debut">
            <input type="hidden" name="date_fin" id="input_fin">

            <button type="submit" class="btn-confirm">Confirmer</button>
        </form>
    </div>
</div>

<!-- Popup Actions -->
<div id="popupActionsRDV">
    <div class="popup-actions-content" style="position:relative;">
        <button class="popup-close" onclick="closePopupActions()">&times;</button>
        <div class="popup-header">
            <h3>Actions</h3>
        </div>
        <div class="popup-actions-list">
            <button id="action-modifier" class="popup-action-item">
                <span class="popup-action-icon">✏️</span>
                Modifier
            </button>
            <a href="#" id="action-fiche" class="popup-action-item">
                <span class="popup-action-icon">🐶</span>
                Fiche du chien
            </a>
            <a href="#" id="action-facturer" class="popup-action-item">
                <span class="popup-action-icon">📄</span>
                Facturer
            </a>
            <button id="action-supprimer" class="popup-action-item danger">
                <span class="popup-action-icon">🗑️</span>
                Supprimer
            </button>
        </div>
    </div>
</div>

<!-- Modal Modification -->
<div id="modalEditRDV">
    <div class="modal-content" style="position:relative;">
        <button class="close-modal" onclick="closeEditModal()">&times;</button>
        <h3>Modifier le rendez-vous</h3>
        <p id="edit-animal-name" style="color: #666; margin: 0 0 16px 0; font-size: 0.9rem;"></p>

        <form action="<?= htmlspecialchars(route('appointments.update', ['id' => '__ID__'])) ?>" method="POST" id="formEditRDV">
            <input type="hidden" name="id_rdv" id="edit_id_rdv">

            <label>Prestation</label>
            <input type="text" name="titre" id="edit_titre" required class="search-input">

            <label>Date</label>
            <input type="text" id="edit_date_display" class="search-input" placeholder="jj/mm/aaaa" maxlength="10" autocomplete="off" inputmode="numeric">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label>Heure début</label>
                    <input type="text" id="edit_heure_debut" class="search-input" placeholder="HH:MM" maxlength="5" autocomplete="off" inputmode="numeric">
                </div>
                <div>
                    <label>Heure fin</label>
                    <input type="text" id="edit_heure_fin" class="search-input" placeholder="HH:MM" maxlength="5" autocomplete="off" inputmode="numeric">
                </div>
            </div>

            <input type="hidden" name="date_debut" id="edit_debut">
            <input type="hidden" name="date_fin" id="edit_fin">

            <button type="submit" class="btn-confirm">Enregistrer</button>
        </form>
    </div>
</div>

<script>
var selectedRdvId = null;
var selectedAnimalId = null;
var selectedEvent = null;

/* ===== Formatage auto date jj/mm/aaaa ===== */
function setupDateInput(input) {
    input.addEventListener('input', function() {
        var v = input.value.replace(/[^\d]/g, '');
        if (v.length > 8) v = v.substring(0, 8);
        if (v.length > 4) input.value = v.substring(0,2) + '/' + v.substring(2,4) + '/' + v.substring(4);
        else if (v.length > 2) input.value = v.substring(0,2) + '/' + v.substring(2);
        else input.value = v;
    });
}

/* ===== Formatage auto heure HH:MM ===== */
function setupTimeInput(input) {
    input.addEventListener('input', function() {
        var v = input.value.replace(/[^\d]/g, '');
        if (v.length > 4) v = v.substring(0, 4);
        if (v.length > 2) input.value = v.substring(0,2) + ':' + v.substring(2);
        else input.value = v;
    });
}

/* ===== Convertir jj/mm/aaaa → YYYY-MM-DD ===== */
function dateToISO(dateStr) {
    var parts = dateStr.split('/');
    if (parts.length !== 3 || parts[2].length !== 4) return '';
    var j = parseInt(parts[0]), m = parseInt(parts[1]), a = parseInt(parts[2]);
    if (j < 1 || j > 31 || m < 1 || m > 12 || a < 2000) return '';
    return parts[2] + '-' + parts[1].padStart(2,'0') + '-' + parts[0].padStart(2,'0');
}

/* ===== Sync hidden fields avant soumission ===== */
function syncHiddenFields(dateDisplay, heureDebut, heureFin, hiddenDebut, hiddenFin) {
    var iso = dateToISO(dateDisplay.value);
    if (!iso || heureDebut.value.length < 5 || heureFin.value.length < 5) return false;
    hiddenDebut.value = iso + 'T' + heureDebut.value;
    hiddenFin.value = iso + 'T' + heureFin.value;
    return true;
}

/* ===== Convertir ISO "2025-04-01T09:00" → "01/04/2025" + "09:00" ===== */
function isoToDisplay(isoStr) {
    var d = isoStr.slice(0, 10); // YYYY-MM-DD
    var t = isoStr.slice(11, 16); // HH:MM
    var parts = d.split('-');
    return { date: parts[2] + '/' + parts[1] + '/' + parts[0], heure: t };
}

function toDateKey(date) {
    return date.getFullYear() + '-' +
        String(date.getMonth() + 1).padStart(2, '0') + '-' +
        String(date.getDate()).padStart(2, '0');
}

function dateFromKey(key) {
    var parts = key.split('-');
    return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
}

function addDays(date, days) {
    var d = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    d.setDate(d.getDate() + days);
    return d;
}

function getEasterDate(year) {
    // Algorithme de Butcher (calendrier grégorien)
    var a = year % 19;
    var b = Math.floor(year / 100);
    var c = year % 100;
    var d = Math.floor(b / 4);
    var e = b % 4;
    var f = Math.floor((b + 8) / 25);
    var g = Math.floor((b - f + 1) / 3);
    var h = (19 * a + b - d - g + 15) % 30;
    var i = Math.floor(c / 4);
    var k = c % 4;
    var l = (32 + 2 * e + 2 * i - h - k) % 7;
    var m = Math.floor((a + 11 * h + 22 * l) / 451);
    var month = Math.floor((h + l - 7 * m + 114) / 31); // 3=March, 4=April
    var day = ((h + l - 7 * m + 114) % 31) + 1;
    return new Date(year, month - 1, day);
}

function getFrenchHolidayMap(year) {
    var easter = getEasterDate(year);
    var map = {};

    function put(date, name) {
        map[toDateKey(date)] = name;
    }

    put(new Date(year, 0, 1), "Jour de l'an");
    put(addDays(easter, 1), 'Lundi de Pâques');
    put(new Date(year, 4, 1), 'Fête du Travail');
    put(new Date(year, 4, 8), 'Victoire 1945');
    put(addDays(easter, 39), 'Ascension');
    put(addDays(easter, 50), 'Lundi de Pentecôte');
    put(new Date(year, 6, 14), 'Fête nationale');
    put(new Date(year, 7, 15), 'Assomption');
    put(new Date(year, 10, 1), 'Toussaint');
    put(new Date(year, 10, 11), 'Armistice');
    put(new Date(year, 11, 25), 'Noël');

    return map;
}

function getFrenchHolidayKeys(year) {
    return Object.keys(getFrenchHolidayMap(year));
}

function buildHolidayBackgroundEvents(startYear, endYear) {
    var keys = new Set();
    for (var y = startYear; y <= endYear; y++) {
        getFrenchHolidayKeys(y).forEach(function(k) { keys.add(k); });
    }

    return Array.from(keys).sort().map(function(key) {
        var start = dateFromKey(key);
        var end = addDays(start, 1);
        return {
            start: toDateKey(start),
            end: toDateKey(end),
            allDay: true,
            display: 'background',
            classNames: ['fc-bg-holiday']
        };
    });
}

function buildVacationBackgroundEvents(vacationDaysSet) {
    return Array.from(vacationDaysSet).sort().map(function(key) {
        var start = dateFromKey(key);
        var end = addDays(start, 1);
        return {
            start: toDateKey(start),
            end: toDateKey(end),
            allDay: true,
            display: 'background',
            classNames: ['fc-bg-vacation']
        };
    });
}

function buildSelectedDateKeys(start, end) {
    var keys = [];
    var cursor = new Date(start.getFullYear(), start.getMonth(), start.getDate());
    var endDay = new Date(end.getFullYear(), end.getMonth(), end.getDate());

    if (cursor >= endDay) {
        return [toDateKey(start)];
    }

    while (cursor < endDay) {
        keys.push(toDateKey(cursor));
        cursor = addDays(cursor, 1);
    }

    return keys;
}

function loadVacationDays(storageKey) {
    try {
        var raw = localStorage.getItem(storageKey);
        if (!raw) return new Set();
        var parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) return new Set();

        var valid = parsed.filter(function(v) {
            return /^\d{4}-\d{2}-\d{2}$/.test(v);
        });

        return new Set(valid);
    } catch (e) {
        return new Set();
    }
}

function saveVacationDays(storageKey, daysSet) {
    localStorage.setItem(storageKey, JSON.stringify(Array.from(daysSet).sort()));
}

var holidayYearCache = {};
function isFrenchHolidayDate(date) {
    var year = date.getFullYear();
    if (!holidayYearCache[year]) {
        holidayYearCache[year] = getFrenchHolidayMap(year);
    }
    return !!holidayYearCache[year][toDateKey(date)];
}

function getFrenchHolidayName(date) {
    var year = date.getFullYear();
    if (!holidayYearCache[year]) {
        holidayYearCache[year] = getFrenchHolidayMap(year);
    }
    return holidayYearCache[year][toDateKey(date)] || '';
}

function closePopupActions() {
    document.getElementById('popupActionsRDV').style.display = 'none';
}

function closeEditModal() {
    document.getElementById('modalEditRDV').style.display = 'none';
}

function openEditModal() {
    closePopupActions();
    document.getElementById('edit_id_rdv').value = selectedRdvId;
    document.getElementById('edit_titre').value = selectedEvent.extendedProps.titre;

    var startISO = selectedEvent.startStr.slice(0, 16);
    var endISO = selectedEvent.endStr.slice(0, 16);
    var startParts = isoToDisplay(startISO);
    var endParts = isoToDisplay(endISO);

    document.getElementById('edit_date_display').value = startParts.date;
    document.getElementById('edit_heure_debut').value = startParts.heure;
    document.getElementById('edit_heure_fin').value = endParts.heure;

    document.getElementById('edit-animal-name').textContent = selectedEvent.extendedProps.nom_animal + ' • ' + selectedEvent.extendedProps.nom_client;

    var form = document.getElementById('formEditRDV');
    var baseUrl = <?= json_encode(route('appointments.update', ['id' => '__ID__'])) ?>;
    form.action = baseUrl.replace('__ID__', selectedRdvId);

    document.getElementById('modalEditRDV').style.display = 'block';
}

function openPopupActions(event) {
    selectedRdvId = event.id;
    selectedAnimalId = event.extendedProps.id_animal;
    selectedEvent = event;

    var trackingUrl = <?= json_encode(route('animals.tracking', ['id' => '__ID__'])) ?>;

    document.getElementById('action-fiche').href = trackingUrl.replace('__ID__', selectedAnimalId) + '#historique-soins';
    document.getElementById('action-facturer').href = trackingUrl.replace('__ID__', selectedAnimalId) + '#nouvelle-visite';

    document.getElementById('popupActionsRDV').style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var isPhone = window.innerWidth <= 600;

    // Activer le formatage auto sur tous les champs date/heure
    setupDateInput(document.getElementById('input_date_display'));
    setupTimeInput(document.getElementById('input_heure_debut'));
    setupTimeInput(document.getElementById('input_heure_fin'));
    setupDateInput(document.getElementById('edit_date_display'));
    setupTimeInput(document.getElementById('edit_heure_debut'));
    setupTimeInput(document.getElementById('edit_heure_fin'));

    var rdvEvents = <?php echo json_encode($events); ?>;
    var vacationStorageKey = 'sweetydog_calendar_vacation_days';
    var vacationDays = loadVacationDays(vacationStorageKey);
    var vacationModeActive = false;
    var weekendSource = null;
    var holidaySource = null;
    var holidayRangeKey = '';
    var vacationSource = null;

    function updateVacationToggleButton() {
        var fcBtn = document.querySelector('.fc-vacationToggle-button');
        if (fcBtn) {
            fcBtn.textContent = vacationModeActive ? 'Vacances: ON' : 'Vacances: OFF';
            fcBtn.classList.toggle('vacation-active', vacationModeActive);
        }

        var mobileBtn = document.getElementById('mobile-vacation-toggle');
        if (mobileBtn) {
            mobileBtn.textContent = vacationModeActive ? 'Vacances: ON' : 'Vacances: OFF';
            mobileBtn.classList.toggle('active', vacationModeActive);
        }
    }

    function toFrenchDate(date) {
        return date.toLocaleDateString('fr-FR', {
            weekday: 'short',
            day: '2-digit',
            month: 'short'
        });
    }

    function toFrenchMonth(date) {
        return date.toLocaleDateString('fr-FR', {
            month: 'long',
            year: 'numeric'
        });
    }

    function fillCreateModalFromDate(dateObj) {
        var d = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
        var dd = String(d.getDate()).padStart(2, '0');
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var yyyy = d.getFullYear();

        document.getElementById('input_date_display').value = dd + '/' + mm + '/' + yyyy;
        document.getElementById('input_heure_debut').value = '09:00';
        document.getElementById('input_heure_fin').value = '10:00';
        document.getElementById('animal_input').value = '';
        document.getElementById('id_animal_hidden').value = '';
        document.getElementById('modalRDV').style.display = 'block';
    }

    function renderWeekendSource(calendar) {
        if (weekendSource) {
            weekendSource.remove();
        }

        weekendSource = calendar.addEventSource([
            {
                daysOfWeek: [0, 6],
                allDay: true,
                display: 'background',
                classNames: ['fc-bg-weekend']
            }
        ]);
    }

    function renderHolidaySource(calendar, viewStart, viewEnd) {
        var startYear = viewStart.getFullYear() - 1;
        var endYear = viewEnd.getFullYear() + 1;
        var currentRangeKey = startYear + '-' + endYear;

        if (holidayRangeKey === currentRangeKey) {
            return;
        }

        holidayRangeKey = currentRangeKey;
        if (holidaySource) {
            holidaySource.remove();
        }

        holidaySource = calendar.addEventSource(buildHolidayBackgroundEvents(startYear, endYear));
    }

    function renderVacationSource(calendar) {
        if (vacationSource) {
            vacationSource.remove();
        }

        vacationSource = calendar.addEventSource(buildVacationBackgroundEvents(vacationDays));
    }

    function applyVacationSelection(selectionInfo, calendar) {
        var keys = buildSelectedDateKeys(selectionInfo.start, selectionInfo.end);
        if (!keys.length) return;

        var alreadyAllSelected = keys.every(function(key) {
            return vacationDays.has(key);
        });

        keys.forEach(function(key) {
            if (alreadyAllSelected) {
                vacationDays.delete(key);
            } else {
                vacationDays.add(key);
            }
        });

        saveVacationDays(vacationStorageKey, vacationDays);
        renderVacationSource(calendar);
    }

    var forcedView = <?= json_encode($calendarView ?? null) ?>;
    var forcedDate = <?= json_encode($calendarDate ?? null) ?>;
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: forcedView || (isPhone ? 'timeGridDay' : 'timeGridWeek'),
        initialDate: forcedDate || undefined,
        locale: 'fr',
        firstDay: 1,
        slotMinTime: '08:00:00',
        slotMaxTime: '19:00:00',
        selectable: true,
        allDaySlot: false,
        nowIndicator: true,
        eventDisplay: 'block',
        customButtons: {
            vacationToggle: {
                text: 'Vacances: OFF',
                click: function() {
                    vacationModeActive = !vacationModeActive;
                    updateVacationToggleButton();
                }
            }
        },
        headerToolbar: isPhone ? false : {
            left: 'prev,next today vacationToggle',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: "Aujourd'hui",
            month: 'Mois',
            week: 'Semaine',
            day: 'Jour'
        },
        events: rdvEvents,
        dayCellClassNames: function(arg) {
            var classes = [];
            var day = arg.date.getDay();
            if (day === 0 || day === 6) {
                classes.push('fc-day-weekend-cell');
            }
            if (isFrenchHolidayDate(arg.date)) {
                classes.push('fc-day-holiday-cell');
            }
            return classes;
        },
        dayCellDidMount: function(arg) {
            if (arg.view.type !== 'dayGridMonth') {
                return;
            }

            var holidayName = getFrenchHolidayName(arg.date);
            if (!holidayName) {
                return;
            }

            var dayFrame = arg.el.querySelector('.fc-daygrid-day-frame');
            if (!dayFrame || dayFrame.querySelector('.fc-holiday-name')) {
                return;
            }

            var label = document.createElement('div');
            label.className = 'fc-holiday-name';
            label.textContent = holidayName;
            label.title = holidayName;
            dayFrame.appendChild(label);
        },
        datesSet: function(info) {
            renderHolidaySource(calendar, info.start, info.end);
            updateVacationToggleButton();

            if (!isPhone) {
                return;
            }

            var label = document.getElementById('mobile-current-label');
            if (label) {
                if (info.view.type === 'dayGridMonth') {
                    label.textContent = toFrenchMonth(info.start);
                } else if (info.view.type === 'timeGridWeek') {
                    var weekEnd = new Date(info.end.getFullYear(), info.end.getMonth(), info.end.getDate() - 1);
                    label.textContent = toFrenchDate(info.start) + ' - ' + toFrenchDate(weekEnd);
                } else {
                    label.textContent = toFrenchDate(info.start);
                }
            }

            var btnDay = document.getElementById('mobile-view-day');
            var btnWeek = document.getElementById('mobile-view-week');
            var btnMonth = document.getElementById('mobile-view-month');
            if (btnDay) btnDay.classList.toggle('active', info.view.type === 'timeGridDay');
            if (btnWeek) btnWeek.classList.toggle('active', info.view.type === 'timeGridWeek');
            if (btnMonth) btnMonth.classList.toggle('active', info.view.type === 'dayGridMonth');
        },

        eventDidMount: function(info) {
            if (info.event.display === 'background') {
                return;
            }
            var props = info.event.extendedProps;
            info.el.setAttribute('title', props.nom_animal + ' - ' + props.titre + '\n' + props.nom_client);
        },

        select: function(info) {
            if (vacationModeActive) {
                applyVacationSelection(info, calendar);
                calendar.unselect();
                return;
            }

            document.getElementById('modalRDV').style.display = 'block';

            var debut = info.startStr;
            var fin = info.endStr;

            // En vue mois : date sans heure → défaut 09:00-10:00
            if (debut.length === 10) {
                debut = debut + 'T09:00';
                fin = debut.slice(0, 11) + '10:00';
            }

            var startParts = isoToDisplay(debut.slice(0, 16));
            var endParts = isoToDisplay(fin.slice(0, 16));

            document.getElementById('input_date_display').value = startParts.date;
            document.getElementById('input_heure_debut').value = startParts.heure;
            document.getElementById('input_heure_fin').value = endParts.heure;
            document.getElementById('animal_input').value = "";
            document.getElementById('id_animal_hidden').value = "";
        },

        eventClick: function(info) {
            if (info.event.display === 'background' || !info.event.id) {
                return;
            }
            openPopupActions(info.event);
        }
    });

    calendar.render();
    renderWeekendSource(calendar);
    renderHolidaySource(calendar, calendar.view.currentStart, calendar.view.currentEnd);
    renderVacationSource(calendar);
    updateVacationToggleButton();

    if (isPhone) {
        var btnPrev = document.getElementById('mobile-prev');
        var btnNext = document.getElementById('mobile-next');
        var btnToday = document.getElementById('mobile-today');
        var btnDay = document.getElementById('mobile-view-day');
        var btnWeek = document.getElementById('mobile-view-week');
        var btnMonth = document.getElementById('mobile-view-month');
        var btnNew = document.getElementById('mobile-new-rdv');
        var btnVacation = document.getElementById('mobile-vacation-toggle');

        if (btnPrev) btnPrev.addEventListener('click', function() { calendar.prev(); });
        if (btnNext) btnNext.addEventListener('click', function() { calendar.next(); });
        if (btnToday) btnToday.addEventListener('click', function() { calendar.today(); });
        if (btnDay) btnDay.addEventListener('click', function() { calendar.changeView('timeGridDay'); });
        if (btnWeek) btnWeek.addEventListener('click', function() { calendar.changeView('timeGridWeek'); });
        if (btnMonth) btnMonth.addEventListener('click', function() { calendar.changeView('dayGridMonth'); });
        if (btnNew) {
            btnNew.addEventListener('click', function() {
                fillCreateModalFromDate(calendar.getDate());
            });
        }
        if (btnVacation) {
            btnVacation.addEventListener('click', function() {
                vacationModeActive = !vacationModeActive;
                updateVacationToggleButton();
            });
        }
    }

    // Fermer en cliquant à l'extérieur
    ['popupActionsRDV', 'modalRDV', 'modalEditRDV'].forEach(function(id) {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });

    document.getElementById('action-modifier').addEventListener('click', openEditModal);

    document.getElementById('action-supprimer').addEventListener('click', function() {
        if (selectedRdvId && confirm("Supprimer ce rendez-vous ?")) {
            var templateUrl = <?= json_encode(route('appointments.delete', ['id' => '__ID__'])) ?>;
            fetch(templateUrl.replace('__ID__', selectedRdvId), { method: 'POST' })
                .then(function() { window.location.reload(); });
        }
    });

    document.getElementById('animal_input').addEventListener('input', function(e) {
        var options = document.getElementById('liste_animaux_dl').options;
        var hiddenInput = document.getElementById('id_animal_hidden');
        for (var i = 0; i < options.length; i++) {
            if (options[i].value === e.target.value) {
                hiddenInput.value = options[i].getAttribute('data-id');
                return;
            }
        }
        hiddenInput.value = "";
    });

    // Formulaire nouveau RDV : sync hidden avant envoi
    document.getElementById('formRDV').addEventListener('submit', function(e) {
        var currentDate = calendar.getDate();
        document.getElementById('return_view_hidden').value = calendar.view.type;
        document.getElementById('return_date_hidden').value =
            currentDate.getFullYear() + '-' +
            String(currentDate.getMonth() + 1).padStart(2, '0') + '-' +
            String(currentDate.getDate()).padStart(2, '0');

        if (!document.getElementById('id_animal_hidden').value) {
            e.preventDefault();
            alert("Veuillez sélectionner un animal dans la liste.");
            return;
        }
        var ok = syncHiddenFields(
            document.getElementById('input_date_display'),
            document.getElementById('input_heure_debut'),
            document.getElementById('input_heure_fin'),
            document.getElementById('input_debut'),
            document.getElementById('input_fin')
        );
        if (!ok) {
            e.preventDefault();
            alert("Veuillez remplir la date et les heures correctement.\nFormat : jj/mm/aaaa et HH:MM");
        }
    });

    // Formulaire modification RDV : sync hidden avant envoi
    document.getElementById('formEditRDV').addEventListener('submit', function(e) {
        var ok = syncHiddenFields(
            document.getElementById('edit_date_display'),
            document.getElementById('edit_heure_debut'),
            document.getElementById('edit_heure_fin'),
            document.getElementById('edit_debut'),
            document.getElementById('edit_fin')
        );
        if (!ok) {
            e.preventDefault();
            alert("Veuillez remplir la date et les heures correctement.\nFormat : jj/mm/aaaa et HH:MM");
        }
    });
});
</script>

</body>
</html>
