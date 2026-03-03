<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SweetyDog - Facturation</title>
    <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .container-large { max-width: 1400px; padding: 25px; }

        /* TOP BAR */
        .top-bar { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom: 22px; }
        .brand h2 { margin:0; color: var(--vert-fonce); }
        .top-nav { display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
        .top-nav a { text-decoration:none; color:#666; font-size:.9rem; font-weight:600; }
        .top-nav a:hover { color: var(--vert-fonce); }

        /* SÉLECTION CLIENT */
        .client-selector {
            background: white; padding: 25px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #eee;
        }
        .client-selector h3 { margin: 0 0 15px 0; color: var(--vert-fonce); }
        .invoices-section {
            background: white;
            padding: 18px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .invoices-section h3 {
            margin: 0 0 6px 0;
            color: var(--vert-fonce);
            font-size: 1.05rem;
            border: none;
            padding: 0;
            letter-spacing: 0;
            text-transform: none;
        }
        .invoices-section p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }
        .client-search-wrapper { position: relative; width: 100%; }
        .client-search-bar {
            position: relative !important;
            width: 100% !important;
            height: 46px !important;
            display: flex !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .client-search-bar .search-icon {
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
            color: #64748b;
        }
        .client-search-input {
            width: 100% !important; height: 46px !important; padding: 0 44px 0 46px !important;
            margin: 0 !important;
            border: 2px solid #dfe7e2 !important; border-radius: 14px !important;
            background: #fff !important; box-shadow: 0 6px 16px rgba(0,0,0,0.06) !important;
            font-size: .95rem !important; color: #111827 !important; box-sizing: border-box !important;
        }
        .client-search-input:focus {
            outline: none !important; border-color: var(--vert-moyen) !important;
            box-shadow: 0 10px 22px rgba(0,0,0,0.10) !important;
        }
        .client-search-input::placeholder { color: #94a3b8; }
        .client-search-clear {
            position: absolute !important; right: 6px !important;
            top: 50% !important; transform: translateY(-50%) !important;
            width: 40px !important; height: 40px !important;
            display: flex !important; align-items: center !important; justify-content: center !important;
            font-size: 18px !important; font-weight: 900 !important; color: #64748b !important;
            border-radius: 12px !important; cursor: pointer; background: none; border: none;
        }
        .client-search-clear:hover { color: #111827 !important; background: #eef2f7 !important; }

        /* DROPDOWN SUGGESTIONS */
        .search-dropdown {
            display: none;
            position: relative;
            margin-top: 8px;
            width: 100%;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
            max-height: 52vh;
            overflow-y: auto;
            z-index: 20;
        }
        .search-dropdown.active { display: block; }
        .search-dropdown-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 18px;
            cursor: pointer; transition: background 0.15s ease; border-bottom: 1px solid #f1f5f9;
        }
        .search-dropdown-item:last-child { border-bottom: none; }
        .search-dropdown-item:hover, .search-dropdown-item.highlighted {
            background: #f0fdf4;
        }
        .search-dropdown-item .proprio-info {
            font-weight: 700; color: #1e293b; font-size: 0.95rem;
        }
        .search-dropdown-item .animaux-info {
            font-size: 0.82rem; color: #64748b; margin-top: 2px;
        }
        .search-dropdown-item .animaux-info .animal-name {
            display: inline-flex; align-items: center; gap: 3px;
            background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 10px;
            font-weight: 600; font-size: 0.8rem; margin-right: 4px;
        }
        .search-dropdown-item .proprio-avatar {
            width: 38px; height: 38px; border-radius: 50%; background: var(--vert-fonce);
            color: white; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.85rem; flex-shrink: 0;
        }
        .search-dropdown-empty {
            padding: 20px; text-align: center; color: #94a3b8; font-size: 0.9rem;
        }

        /* INFO CLIENT */
        .client-info-banner {
            background: #f0fdf4; border: 1px solid #d1e7dd; border-radius: 12px;
            padding: 15px 20px; margin-bottom: 25px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;
        }
        .client-info-banner .client-name { font-weight: 900; color: var(--vert-fonce); font-size: 1.1rem; }
        .client-info-banner .client-tel { font-family: monospace; font-weight: 800; color: var(--vert-fonce); }
        .btn-change-client {
            padding: 8px 16px; background: white; color: #666; border: 1px solid #ddd; border-radius: 8px;
            text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.2s;
        }
        .btn-change-client:hover { background: #f8f9fa; color: #333; }
        .btn-invoices-access {
            padding: 8px 14px;
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .btn-invoices-access:hover { background: #d8f3dc; color: #1b5e20; }

        /* SÉLECTION ANIMAUX (multi) */
        .animal-selector { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .animal-selector h3 { margin: 0 0 5px 0; color: var(--vert-fonce); }
        .animal-selector .hint { color: #64748b; font-size: 0.85em; margin-bottom: 15px; }
        .animal-cards { display: flex; gap: 12px; flex-wrap: wrap; }
        .animal-card {
            padding: 12px 20px; background: #f8fafc; border: 2px solid #e2e8f0;
            border-radius: 12px; cursor: pointer; transition: all 0.3s ease;
            display: flex; align-items: center; gap: 10px; min-width: 150px; user-select: none;
        }
        .animal-card:hover { border-color: var(--vert-moyen); background: #f0fdf4; }
        .animal-card.selected { border-color: var(--vert-fonce); background: #e8f5e9; }
        .animal-card .animal-icon { font-size: 1.3em; }
        .animal-card .animal-name { font-weight: 700; color: #1e293b; }
        .animal-card .animal-race { font-size: 0.8em; color: #64748b; }
        .animal-card .check-icon { display: none; color: var(--vert-fonce); font-size: 1.1em; margin-left: auto; }
        .animal-card.selected .check-icon { display: inline; }

        /* BLOC SOINS PAR ANIMAL */
        .animal-soin-block {
            background: #fafbfc; border: 2px solid #e8f5e9; border-radius: 12px;
            padding: 20px; margin-bottom: 15px; animation: slideIn 0.3s ease;
        }
        .animal-soin-block .block-header {
            display: flex; align-items: center; gap: 10px; margin-bottom: 15px;
            padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;
        }
        .animal-soin-block .block-header .animal-badge {
            background: var(--vert-fonce); color: white; padding: 5px 14px;
            border-radius: 20px; font-weight: 700; font-size: 0.9em;
            display: inline-flex; align-items: center; gap: 6px;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* FORMULAIRE */
        .prestation-selector { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
        .prestation-selector input[type="checkbox"], .paiement-selector input[type="radio"] { display: none; }
        .prestation-label {
            padding: 8px 16px; background: #f0f0f0; border: 2px solid #ddd;
            border-radius: 20px; cursor: pointer; font-size: 0.9em; transition: all 0.3s ease; color: #555;
        }
        .prestation-selector input[type="checkbox"]:checked + .prestation-label {
            background-color: var(--vert-moyen); border-color: var(--vert-fonce); color: white;
        }
        .paiement-selector { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
        .paiement-label {
            padding: 10px 20px; background: #f8fafc; border: 2px solid #e2e8f0;
            border-radius: 12px; cursor: pointer; font-size: 0.9em; transition: all 0.3s ease;
            color: #475569; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;
        }
        .paiement-label:hover { border-color: #3b82f6; background: #eff6ff; }
        .paiement-selector input[type="radio"]:checked + .paiement-label {
            background: #2563eb; border-color: #2563eb; color: white;
        }
        .tag-soin { background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 6px; font-size: 0.8em; font-weight: 600; border: 1px solid #c8e6c9; margin-right: 5px; }
        .tag-vente { background: #ede9fe; color: #6d28d9; padding: 4px 10px; border-radius: 6px; font-size: 0.8em; font-weight: 600; border: 1px solid #ddd6fe; margin-right: 5px; }
        .tag-animal { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 8px; font-size: 0.78em; font-weight: 700; border: 1px solid #bfdbfe; margin-right: 5px; display:inline-flex; align-items:center; gap:5px; white-space:nowrap; line-height:1.2; }
        .btn-download-pdf { text-decoration: none; background: #e8f5e9; color: #2e7d32; padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: bold; border: 1px solid #c8e6c9; }
        .btn-download-pdf:hover { background: #c8e6c9; }
        .btn-email-invoice { text-decoration: none; background: #e0f2fe; color: #075985; padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: bold; border: 1px solid #bae6fd; margin-left: 6px; }
        .btn-email-invoice:hover { background: #bae6fd; }
        .btn-generate-invoice { text-decoration: none; background: #fff3cd; color: #856404; padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: bold; border: 1px solid #ffeaa7; }
        .btn-generate-invoice:hover { background: #ffeaa7; }

        /* MODAL VENTE */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-content {
            background: white; border-radius: 16px; padding: 30px; width: 420px; max-width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2); position: relative; animation: modalIn 0.3s ease;
        }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .modal-close { position: absolute; top: 12px; right: 15px; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; }
        .modal-close:hover { color: #333; }
        .modal-title { margin: 0 0 20px 0; color: #1e293b; font-size: 1.2rem; }
        .vente-type-selector { display: flex; gap: 12px; margin-bottom: 20px; }
        .vente-type-btn {
            flex: 1; padding: 15px; border: 2px solid #e2e8f0; border-radius: 12px;
            background: #f8fafc; cursor: pointer; text-align: center; transition: all 0.3s ease;
            font-size: 0.95rem; color: #475569;
        }
        .vente-type-btn:hover { border-color: #a78bfa; background: #f5f3ff; }
        .vente-type-btn.selected { border-color: #7c3aed; background: #ede9fe; color: #7c3aed; font-weight: bold; }
        .vente-type-btn .vente-icon { font-size: 1.8rem; display: block; margin-bottom: 6px; }
        .btn-vente-submit {
            background: #7c3aed; color: white; border: none; padding: 12px 30px;
            border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%;
            font-size: 1rem; transition: all 0.3s ease; margin-top: 10px;
        }
        .btn-vente-submit:hover { background: #6d28d9; transform: translateY(-1px); }
        .btn-vente-submit:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }

        /* RESPONSIVE */
        @media (max-width: 600px) {
            body {
                padding: 10px !important;
                background: linear-gradient(180deg, #edf7f1 0%, #f8fafc 45%, #f4f7f6 100%);
            }
            .container-large {
                padding: 10px !important;
                border-radius: 18px !important;
                border: 1px solid #e1ece5;
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            }
            .top-bar { flex-direction: column; align-items: flex-start; gap: 10px; }
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
            .client-selector {
                padding: 15px;
                border-radius: 14px;
                border: 1px solid #e4efe8;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }
            .client-search-wrapper { width: 100%; }
            .client-search-input { height: 50px !important; font-size: 1rem !important; }
            .search-dropdown { max-height: 52vh; }
            .client-info-banner {
                flex-direction: column;
                align-items: flex-start;
                border-radius: 14px;
                border: 1px solid #cde2d5;
                box-shadow: 0 8px 20px rgba(45, 106, 79, 0.08);
            }
            .btn-change-client { width: 100%; text-align: center; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; }
            .btn-invoices-access { width: 100%; justify-content: center; min-height: 44px; }
            .invoices-section { flex-direction: column; align-items: stretch; }
            .animal-selector {
                padding: 15px;
                border-radius: 14px;
                border: 1px solid #e4efe8;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }
            .animal-cards { flex-direction: column; }
            .animal-card { min-width: 0; min-height: 56px; }
            #nouvelle-facture {
                padding: 15px !important;
                border-radius: 14px !important;
                border: 1px solid #e4efe8 !important;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }
            #btn-open-vente {
                width: 100%;
                min-height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .prestation-selector { gap: 6px; }
            .prestation-label { padding: 6px 12px; font-size: 0.8em; }
            .paiement-selector { gap: 6px; }
            .paiement-label { padding: 8px 14px; font-size: 0.8em; }
            .prix-paiement-grid { display: flex !important; flex-direction: column !important; gap: 15px !important; }
            table thead { display: none; }
            table, table tbody, table tr, table td { display: block; width: 100%; }
            table tr { margin-bottom: 12px; border: 1px solid #ebf2ed; border-radius: 14px; overflow: hidden; background: white; box-shadow: 0 8px 16px rgba(15,23,42,0.06); }
            table td { padding: 8px 15px !important; border-bottom: 1px solid #f8f9fa !important; text-align: left !important; }
            table td:before { content: attr(data-label); font-weight: 700; font-size: 0.7rem; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 3px; }
            table td:last-child { border-bottom: none !important; }
            .tag-soin, .tag-vente, .tag-animal { font-size: 0.72em; padding: 3px 8px; margin-bottom: 3px; display: inline-block; }
            .btn-download-pdf, .btn-email-invoice, .btn-generate-invoice { font-size: 0.78em; padding: 4px 8px; min-height: 36px; display: inline-flex; align-items: center; justify-content: center; margin-left: 0; margin-right: 6px; margin-bottom: 4px; }

            #form-facture > button[type="submit"] {
                width: 100%;
                min-height: 50px;
                position: sticky;
                bottom: calc(env(safe-area-inset-bottom) + 8px);
                z-index: 5;
                box-shadow: 0 12px 26px rgba(15,23,42,0.16);
            }

            .modal-overlay.active { align-items: flex-end; }
            .modal-content {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 16px 14px 26px !important;
                border-radius: 18px 18px 0 0 !important;
                max-height: 86vh;
                overflow: auto;
            }
            .modal-close {
                width: 42px;
                height: 42px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }
            .vente-type-selector { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>

<div class="container-large">

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="brand"><h2>🧾 Facturation</h2></div>
        <div class="top-nav">
            <a href="<?= route('clients.index') ?>">🐾 Clients</a>
            <a href="<?= route('appointments.index') ?>">📅 Agenda</a>
            <a href="<?= route('declaration.index') ?>">📊 Déclaration</a>
            <a href="<?= route('settings.index') ?>">⚙️ Paramètres</a>
            <a href="<?= route('logout') ?>" style="color: #e63946;"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
        </div>
    </div>

    <?php if (isset($_GET['success']) && (int)$_GET['success'] === 1): ?>
        <div id="success-banner" style="background: #d4edda; color: #155724; padding: 20px; border: 1px solid #c3e6cb; border-radius: 8px; margin-bottom: 25px; text-align: center;">
            <h3 style="margin: 0 0 5px 0;">Encaissement validé !</h3>
            <p style="margin: 0; font-size: 0.9em;">La facture Factur-X a été générée et transmise à N2F.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['mail'])): ?>
        <?php
            $mailStatus = (string)$_GET['mail'];
            $mailType = in_array($mailStatus, ['sent'], true) ? 'success' : 'error';
            $mailTitle = $mailType === 'success' ? 'Mail envoyé' : 'Envoi du mail impossible';
            $mailMessage = 'La facture a été envoyée au client.';
            $mailDetail = isset($_SESSION['mail_error_detail']) ? (string)$_SESSION['mail_error_detail'] : '';
            unset($_SESSION['mail_error_detail']);
            if ($mailStatus === 'no_email') {
                $mailMessage = "L'adresse email du client est manquante ou invalide.";
            } elseif ($mailStatus === 'send_error') {
                if ($mailDetail === 'smtp_config_missing') {
                    $mailMessage = "Configuration SMTP incomplète. Renseigne MAIL_USERNAME et MAIL_PASSWORD dans code.env.";
                } elseif (strpos($mailDetail, 'smtp_connect_failed') === 0) {
                    $mailMessage = "Connexion SMTP impossible vers Gmail (port bloqué ou DNS).";
                } elseif ($mailDetail === 'smtp_starttls_failed' || $mailDetail === 'smtp_tls_handshake_failed') {
                    $mailMessage = "La négociation TLS a échoué. Essaye le fallback SSL (port 465) ou vérifie OpenSSL.";
                } elseif (strpos($mailDetail, 'smtp_auth_') === 0) {
                    $mailMessage = "Authentification Gmail refusée. Vérifie le mot de passe d'application Google.";
                } elseif ($mailDetail === 'smtp_rcpt_to_failed') {
                    $mailMessage = "Adresse destinataire refusée par Gmail.";
                } elseif ($mailDetail === 'attachment_unreadable') {
                    $mailMessage = "Pièce jointe introuvable ou illisible.";
                } else {
                    $mailMessage = "Le serveur n'a pas pu envoyer l'email. Vérifie la configuration mail.";
                }
            } elseif ($mailStatus === 'not_found') {
                $mailMessage = "La prestation associée à cette facture est introuvable.";
            } elseif ($mailStatus === 'invalid') {
                $mailMessage = "Impossible d'envoyer la facture : identifiant invalide.";
            }
            $mailBg = $mailType === 'success' ? '#d4edda' : '#f8d7da';
            $mailColor = $mailType === 'success' ? '#155724' : '#721c24';
            $mailBorder = $mailType === 'success' ? '#c3e6cb' : '#f5c6cb';
        ?>
        <div style="background: <?= $mailBg ?>; color: <?= $mailColor ?>; padding: 16px; border: 1px solid <?= $mailBorder ?>; border-radius: 8px; margin-bottom: 25px;">
            <strong><?= htmlspecialchars($mailTitle) ?> :</strong>
            <span><?= htmlspecialchars($mailMessage) ?></span>
        </div>
    <?php endif; ?>

    <?php
        $invoiceArchiveQuery = ['from' => 'facturation'];
        if (!empty($id_proprio)) {
            $invoiceArchiveQuery['client'] = (int)$id_proprio;
        }
    ?>

    <section class="invoices-section">
        <div>
            <h3><i class="fa-solid fa-folder-open"></i> Toutes les factures</h3>
            <p>Accéder aux factures classées par année puis par mois.</p>
        </div>
        <a href="<?= route('declaration.invoices', [], $invoiceArchiveQuery) ?>" class="btn-invoices-access">
            <i class="fa-solid fa-file-invoice"></i> Ouvrir les factures
        </a>
    </section>

    <!-- Données clients pour l'autocomplete -->
    <script>
        var __clientsData = <?= json_encode(array_map(function($p) {
            $animauxNoms = array_map(function($a) {
                return ['nom' => $a['nom_animal'], 'espece' => strtolower($a['espece'] ?? 'chien')];
            }, $p['animaux']);
            return [
                'id' => (int)$p['id_proprietaire'],
                'nom' => $p['nom'],
                'prenom' => $p['prenom'],
                'telephone' => $p['telephone'] ?? '',
                'animaux' => $animauxNoms,
            ];
        }, $tous_les_proprios), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>

    <!-- ÉTAPE 1 : SÉLECTION DU CLIENT -->
    <?php if (!$proprio): ?>
        <div class="client-selector">
            <h3><i class="fa-solid fa-receipt"></i> Facturation - Sélectionner un client</h3>
            <div class="client-search-wrapper">
                <div class="client-search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" class="client-search-input" id="client-search-input"
                           placeholder="Rechercher un client ou un animal..." autocomplete="off">
                    <button type="button" class="client-search-clear" id="client-search-clear" style="display:none;">&times;</button>
                </div>
                <div class="search-dropdown" id="search-dropdown"></div>
            </div>
        </div>
    <?php else: ?>

        <!-- BANDEAU CLIENT SÉLECTIONNÉ -->
        <div class="client-info-banner">
            <div>
                <span class="client-name"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($proprio['prenom'] . ' ' . $proprio['nom']) ?></span>
                <?php if (!empty($proprio['telephone'])): ?>
                    <span class="client-tel" style="margin-left: 20px;">
                        <i class="fa-solid fa-phone"></i> <?= htmlspecialchars(wordwrap($proprio['telephone'], 2, ' ', true)) ?>
                    </span>
                <?php endif; ?>
            </div>
            <a href="<?= route('facturation.index') ?>" class="btn-change-client"><i class="fa-solid fa-arrow-rotate-left"></i> Changer de client</a>
        </div>

        <!-- ÉTAPE 2 : SÉLECTION DES ANIMAUX (multi-sélection) -->
        <div class="animal-selector">
            <h3><i class="fa-solid fa-paw"></i> Choisir le(s) animal(aux) à facturer</h3>
            <p class="hint">Cliquez sur un ou plusieurs animaux. Chacun aura ses propres soins et notes.</p>
            <div class="animal-cards">
                <?php foreach ($animaux as $idx => $a): ?>
                    <div class="animal-card"
                         data-id="<?= (int)$a['id_animal'] ?>"
                         data-name="<?= htmlspecialchars($a['nom_animal']) ?>"
                         data-espece="<?= htmlspecialchars(strtolower($a['espece'] ?? 'chien')) ?>">
                        <span class="animal-icon">
                            <?php
                                $esp = strtolower($a['espece'] ?? '');
                                if ($esp === 'chat') echo '<i class="fa-solid fa-cat"></i>';
                                elseif ($esp === 'lapin') echo '<i class="fa-solid fa-rabbit"></i>';
                                else echo '<i class="fa-solid fa-dog"></i>';
                            ?>
                        </span>
                        <div>
                            <div class="animal-name"><?= htmlspecialchars($a['nom_animal']) ?></div>
                            <div class="animal-race"><?= htmlspecialchars($a['race'] ?: $a['espece'] ?? '') ?></div>
                        </div>
                        <i class="fa-solid fa-circle-check check-icon"></i>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- FORMULAIRE NOUVELLE FACTURE (caché tant qu'aucun animal sélectionné) -->
        <div id="nouvelle-facture" style="display: none; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px;">
            <h3 style="margin-bottom: 20px; color: var(--vert-fonce);">
                <i class="fa-solid fa-file-invoice-dollar"></i> Nouvelle facture
            </h3>

            <form action="<?= route('facturation.store') ?>" method="POST" id="form-facture">

                <div style="margin-bottom: 20px;">
                    <label>Date de la visite</label>
                    <input type="date" name="date_soin" value="<?= date('Y-m-d'); ?>" required>
                </div>

                <!-- Zone dynamique : un bloc de soins par animal sélectionné -->
                <div id="animaux-soins-container"></div>

                <!-- Bouton Vente (global) -->
                <div style="margin-bottom: 20px; padding-top: 15px; border-top: 2px solid #f1f5f9;">
                    <button type="button" id="btn-open-vente" style="
                        background: #ede9fe; color: #7c3aed; border: 2px solid #ddd6fe;
                        padding: 10px 22px; border-radius: 20px; cursor: pointer;
                        font-size: 0.9em; font-weight: 600; transition: all 0.3s ease;
                    " onmouseover="this.style.background='#7c3aed'; this.style.color='white'; this.style.borderColor='#7c3aed';"
                       onmouseout="this.style.background='#ede9fe'; this.style.color='#7c3aed'; this.style.borderColor='#ddd6fe';">
                        <i class="fa-solid fa-cart-plus"></i> Ajouter une vente
                    </button>

                    <button type="button" id="btn-open-remise" style="
                        background: #fee2e2; color: #b91c1c; border: 2px solid #fecaca;
                        padding: 10px 22px; border-radius: 20px; cursor: pointer;
                        font-size: 0.9em; font-weight: 600; transition: all 0.3s ease; margin-left: 8px;
                    " onmouseover="this.style.background='#ef4444'; this.style.color='white'; this.style.borderColor='#ef4444';"
                       onmouseout="this.style.background='#fee2e2'; this.style.color='#b91c1c'; this.style.borderColor='#fecaca';">
                        <i class="fa-solid fa-percent"></i> Ajouter une remise
                    </button>

                    <div id="ventes-ajoutees" style="margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap;"></div>
                    <div id="ventes-hidden-inputs"></div>
                    <div id="remises-ajoutees" style="margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap;"></div>
                    <div id="remises-hidden-inputs"></div>
                </div>

                <div class="prix-paiement-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; align-items: end;">
                    <div>
                        <label><i class="fa-solid fa-euro-sign"></i> Total calculé</label>
                        <div id="total-display" style="
                            width: 100%; padding: 12px; border: 2px solid #c8e6c9; border-radius: 12px;
                            background: #e8f5e9; font-size: 1.3rem; font-weight: 900; box-sizing: border-box;
                            color: var(--vert-fonce); text-align: center;
                        ">0,00 €</div>
                        <input type="hidden" name="prix" id="input-prix-total" value="0">
                    </div>
                    <div>
                        <label><i class="fa-solid fa-credit-card"></i> Mode de paiement</label>
                        <div class="paiement-selector" style="margin-top: 8px;">
                            <input type="radio" name="mode_paiement" id="f-pay-cb" value="CB" required>
                            <label for="f-pay-cb" class="paiement-label"><i class="fa-solid fa-credit-card"></i> CB</label>

                            <input type="radio" name="mode_paiement" id="f-pay-cheque" value="Chèque">
                            <label for="f-pay-cheque" class="paiement-label"><i class="fa-solid fa-money-check"></i> Chèque</label>

                            <input type="radio" name="mode_paiement" id="f-pay-especes" value="Espèces">
                            <label for="f-pay-especes" class="paiement-label"><i class="fa-solid fa-coins"></i> Espèces</label>
                        </div>
                    </div>
                </div>

                <button type="submit" style="background: var(--vert-fonce); color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1rem;">
                    <i class="fa-solid fa-check"></i> Valider l'Encaissement
                </button>
            </form>
        </div>

        <!-- HISTORIQUE DES FACTURES DU CLIENT -->
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-clock-rotate-left"></i> Historique des factures de <?= htmlspecialchars($proprio['prenom'] . ' ' . $proprio['nom']) ?></h3>
        <div style="width:100%; overflow-x:visible;">
        <table style="background: white; border-radius: 12px; overflow: hidden; border-collapse: separate; border-spacing: 0; width: 100%; table-layout: auto;">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Date</th>
                    <th style="text-align: left;">Animal</th>
                    <th style="text-align: left;">Prestations</th>
                    <th style="text-align: center;">Temps</th>
                    <th style="text-align: left;">Observations</th>
                    <th style="text-align: left;">Prix</th>
                    <th style="text-align: center;">Paiement</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($historique)): ?>
                <tr><td colspan="8" style="padding: 20px; text-align: center; color: #94a3b8;">Aucune facture enregistrée pour ce client.</td></tr>
            <?php else: ?>
                <?php foreach ($historique as $soin): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td data-label="Date" style="padding: 15px;"><strong><?= date('d/m/Y', strtotime($soin['date_soin'])); ?></strong></td>
                        <td data-label="Animal">
                            <?php
                                $isVenteLigne = (strpos($soin['type_soin'] ?? '', 'Vente') === 0);
                                if ($isVenteLigne):
                            ?>
                                <span class="tag-vente"><i class="fa-solid fa-cart-shopping"></i> Vente</span>
                            <?php else: ?>
                                <span class="tag-animal"><i class="fa-solid fa-paw"></i> <?= htmlspecialchars($soin['nom_animal'] ?? '') ?></span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Prestations">
                            <?php
                            $tags = explode(", ", $soin['type_soin'] ?? '');
                            foreach ($tags as $tag) {
                                $tag = trim($tag);
                                if ($tag === '') continue;
                                if (strpos($tag, 'Vente') === 0) {
                                    echo "<span class='tag-vente'><i class='fa-solid fa-cart-shopping'></i> " . htmlspecialchars($tag) . "</span>";
                                } else {
                                    echo "<span class='tag-soin'>" . htmlspecialchars($tag) . "</span>";
                                }
                            }
                            ?>
                        </td>
                        <?php
                            $notesBrutes = trim((string)($soin['notes'] ?? ''));
                            $notesAffichees = $notesBrutes;

                            $dureeMinutes = (int)($soin['duree_minutes'] ?? 0);
                            if ($dureeMinutes > 0) {
                                $h = intdiv($dureeMinutes, 60);
                                $m = $dureeMinutes % 60;
                                $dureeAffichee = $h . 'h' . str_pad((string)$m, 2, '0', STR_PAD_LEFT);
                            } else {
                                $dureeAffichee = '-';
                                // Compatibilité lecture anciennes notes
                                if ($notesBrutes !== '' && preg_match('/(?:Durée toilettage|Temps toilettage)\s*:\s*(\d+)\s*h\s*(\d{1,2})?/i', $notesBrutes, $mDureeHm)) {
                                    $h = (int)$mDureeHm[1];
                                    $m = isset($mDureeHm[2]) ? (int)$mDureeHm[2] : 0;
                                    if ($m > 59) $m = 59;
                                    $dureeAffichee = $h . 'h' . str_pad((string)$m, 2, '0', STR_PAD_LEFT);
                                    $notesAffichees = trim(preg_replace('/\s*\|?\s*(?:Durée toilettage|Temps toilettage)\s*:\s*\d+\s*h\s*\d{0,2}\s*/i', ' ', $notesBrutes));
                                } elseif ($notesBrutes !== '' && preg_match('/(?:Durée toilettage|Temps toilettage)\s*:\s*(\d+)\s*min/i', $notesBrutes, $mDureeMin)) {
                                    $totalMin = (int)$mDureeMin[1];
                                    $h = intdiv($totalMin, 60);
                                    $m = $totalMin % 60;
                                    $dureeAffichee = $h . 'h' . str_pad((string)$m, 2, '0', STR_PAD_LEFT);
                                    $notesAffichees = trim(preg_replace('/\s*\|?\s*(?:Durée toilettage|Temps toilettage)\s*:\s*\d+\s*min\s*/i', ' ', $notesBrutes));
                                }
                            }

                            if ($notesAffichees === '') {
                                $notesAffichees = '-';
                            }
                        ?>
                        <td data-label="Temps" style="text-align: center; white-space: nowrap;">
                            <span style="display:inline-flex; align-items:center; gap:4px; font-weight:700; color:#475569;">
                                <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($dureeAffichee) ?>
                            </span>
                        </td>
                        <td data-label="Notes" style="color: #7f8c8d; font-size: 0.9em;">
                            <?php
                                $noteTexte = trim((string)$notesAffichees);
                                $noteLen = function_exists('mb_strlen') ? mb_strlen($noteTexte, 'UTF-8') : strlen($noteTexte);
                                $noteLimite = 80;
                                if ($noteTexte !== '-' && $noteLen > $noteLimite) {
                                    $notePreview = function_exists('mb_substr')
                                        ? mb_substr($noteTexte, 0, $noteLimite, 'UTF-8')
                                        : substr($noteTexte, 0, $noteLimite);
                                    echo '<button type="button" class="note-preview-btn" data-full-note="' . htmlspecialchars($noteTexte, ENT_QUOTES, 'UTF-8') . '" style="background:none;border:none;padding:0;color:#64748b;cursor:pointer;text-align:left;font:inherit;display:inline-block;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;vertical-align:middle;">' . htmlspecialchars($notePreview) . '...</button>';
                                } else {
                                    echo htmlspecialchars($noteTexte !== '' ? $noteTexte : '-');
                                }
                            ?>
                        </td>
                        <td data-label="Prix" style="white-space: nowrap;">
                            <?php
                                $prixAffiche = isset($soin['prix_apres_remise'])
                                    ? (float)$soin['prix_apres_remise']
                                    : (float)($soin['prix'] ?? 0);
                                $remiseMontant = (float)($soin['remise_montant'] ?? 0);
                            ?>
                            <span style="font-weight: bold; color: #2e7d32; white-space: nowrap;"><?= number_format($prixAffiche, 2, ',', ' '); ?>&nbsp;&euro;</span>
                            <?php if (abs($remiseMontant) > 0.0001): ?>
                                <div style="font-size: 0.78em; color: #64748b; margin-top: 2px;">
                                    remise: <?= number_format($remiseMontant, 2, ',', ' '); ?>&nbsp;&euro;
                                </div>
                            <?php endif; ?>
                        </td>
                        <td data-label="Paiement" style="text-align: center;">
                            <?php
                                $mp = $soin['mode_paiement'] ?? '';
                                if ($mp === 'CB') {
                                    echo '<span style="background:#dbeafe; color:#1e40af; padding:4px 10px; border-radius:6px; font-size:0.8em; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;"><i class="fa-solid fa-credit-card"></i> CB</span>';
                                } elseif ($mp === 'Chèque') {
                                    echo '<span style="background:#fef3c7; color:#92400e; padding:4px 10px; border-radius:6px; font-size:0.8em; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;"><i class="fa-solid fa-money-check"></i> Chèque</span>';
                                } elseif ($mp === 'Espèces') {
                                    echo '<span style="background:#d1fae5; color:#065f46; padding:4px 10px; border-radius:6px; font-size:0.8em; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;"><i class="fa-solid fa-coins"></i> Espèces</span>';
                                } else {
                                    echo '<span style="color:#94a3b8; font-size:0.8em;">&mdash;</span>';
                                }
                            ?>
                        </td>
                        <td data-label="Actions" style="text-align: center;">
                            <?php
                                $idPrest = (int)($soin['id_prestation'] ?? 0);
                                $groupIds = trim((string)($soin['facture_group_ids'] ?? ''));
                                $pdfQuery = [];
                                if ($groupIds !== '') {
                                    $pdfQuery['group'] = $groupIds;
                                }
                                $pdf_url = route('invoices.download', ['id' => $idPrest], $pdfQuery);

                                $emailQuery = ['from' => 'facturation'];
                                if (!empty($id_proprio)) {
                                    $emailQuery['client'] = (int)$id_proprio;
                                }
                                if ($groupIds !== '') {
                                    $emailQuery['group'] = $groupIds;
                                }
                                $emailUrl = route('invoices.email', ['id' => $idPrest], $emailQuery);

                                $queryGenerate = ['from' => 'facturation'];
                                if ($groupIds !== '') {
                                    $queryGenerate['group'] = $groupIds;
                                }
                                $generateUrl = route('invoices.generate', ['id' => $idPrest], $queryGenerate);
                            ?>
                            <?php if ($idPrest > 0) : ?>
                                <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="btn-download-pdf" title="Ouvrir la facture PDF">
                                    <i class="fa-solid fa-file-pdf"></i> Facture
                                </a>
                                <a href="<?= htmlspecialchars($emailUrl) ?>" class="btn-email-invoice" title="Envoyer la facture par mail">
                                    <i class="fa-solid fa-paper-plane"></i> Envoyer par mail
                                </a>
                            <?php else : ?>
                                <a href="<?= htmlspecialchars($generateUrl) ?>" class="btn-generate-invoice" title="Générer la facture">
                                    <i class="fa-solid fa-gear"></i> Générer
                                </a>
                            <?php endif; ?>
                            <span title="Prestation verrouillée" style="margin-left: 10px; cursor: help; filter: grayscale(100%); opacity: 0.5;"><i class="fa-solid fa-lock"></i></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>

    <?php endif; ?>

</div>

<!-- MODAL VENTE -->
<div class="modal-overlay" id="modal-vente">
    <div class="modal-content">
        <button class="modal-close" id="btn-close-vente">&times;</button>
        <h3 class="modal-title"><i class="fa-solid fa-cart-plus"></i> Ajouter une vente</h3>

        <p style="font-weight: 600; color: #475569; margin-bottom: 10px;">Type de vente</p>
        <div class="vente-type-selector">
            <div class="vente-type-btn" data-type="Jouet">
                <span class="vente-icon">🧸</span>
                Jouet
            </div>
            <div class="vente-type-btn" data-type="Cosmétique">
                <span class="vente-icon">🧴</span>
                Cosmétique
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="font-weight: 600; color: #475569; font-size: 0.9rem;">Détail du produit (optionnel)</label>
            <input type="text" id="vente-detail" placeholder="Ex: Shampoing bio, Balle en caoutchouc..."
                   style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; margin-top: 5px; background: #f8fafc; font-size: 0.95rem;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="font-weight: 600; color: #475569; font-size: 0.9rem;">Prix de la vente (€)</label>
            <input type="number" step="0.01" id="vente-prix" placeholder="Ex: 12.50"
                   style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; margin-top: 5px; background: #f8fafc; font-size: 1.1rem; font-weight: bold; text-align: center;">
        </div>

        <button type="button" class="btn-vente-submit" id="btn-vente-ajouter" disabled>
            <i class="fa-solid fa-check"></i> Ajouter au panier
        </button>
    </div>
</div>

<!-- MODAL REMISE -->
<div class="modal-overlay" id="modal-remise">
    <div class="modal-content">
        <button class="modal-close" id="btn-close-remise">&times;</button>
        <h3 class="modal-title"><i class="fa-solid fa-percent"></i> Ajouter une remise</h3>

        <div style="margin-bottom: 15px;">
            <label style="font-weight: 600; color: #475569; font-size: 0.9rem;">Montant de la remise (€)</label>
            <input type="number" step="0.01" min="0.01" id="remise-prix" placeholder="Ex: 5.00"
                   style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; margin-top: 5px; background: #f8fafc; font-size: 1.1rem; font-weight: bold; text-align: center;">
            <small style="display:block; margin-top:6px; color:#64748b;">Entrez simplement le montant (ex: 5.00), le signe - est ajouté automatiquement.</small>
        </div>

        <button type="button" class="btn-vente-submit" id="btn-remise-ajouter" disabled style="background:#ef4444;">
            <i class="fa-solid fa-check"></i> Ajouter la remise
        </button>
    </div>
</div>

<script>
// === AUTOCOMPLETE RECHERCHE CLIENT ===
(function() {
    var input = document.getElementById('client-search-input');
    var dropdown = document.getElementById('search-dropdown');
    var clearBtn = document.getElementById('client-search-clear');
    if (!input || !dropdown) return; // on est en mode client déjà sélectionné

    var clients = window.__clientsData || [];
    var highlightedIndex = -1;
    var filteredResults = [];

    function normalize(str) {
        return (str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }

    function animalIcon(espece) {
        if (espece === 'chat') return '<i class="fa-solid fa-cat"></i>';
        if (espece === 'lapin') return '<i class="fa-solid fa-rabbit"></i>';
        return '<i class="fa-solid fa-dog"></i>';
    }

    function escHtml(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function filterClients(query) {
        var q = normalize(query);
        if (q.length === 0) return clients.slice(); // montrer tous si vide mais focus

        return clients.filter(function(c) {
            var haystack = normalize(c.nom + ' ' + c.prenom + ' ' + c.telephone);
            // Chercher aussi dans les noms d'animaux
            var animauxStr = c.animaux.map(function(a) { return a.nom; }).join(' ');
            haystack += ' ' + normalize(animauxStr);
            return haystack.indexOf(q) !== -1;
        });
    }

    function renderDropdown(results) {
        filteredResults = results;
        highlightedIndex = -1;

        if (results.length === 0) {
            dropdown.innerHTML = '<div class="search-dropdown-empty"><i class="fa-solid fa-search"></i> Aucun résultat trouvé</div>';
            dropdown.classList.add('active');
            return;
        }

        var html = '';
        results.forEach(function(c, idx) {
            var initials = (c.prenom.charAt(0) + c.nom.charAt(0)).toUpperCase();
            var animauxHtml = '';
            if (c.animaux.length > 0) {
                animauxHtml = c.animaux.map(function(a) {
                    return '<span class="animal-name">' + animalIcon(a.espece) + ' ' + escHtml(a.nom) + '</span>';
                }).join(' ');
            } else {
                animauxHtml = '<span style="color:#cbd5e1; font-style:italic;">Aucun animal</span>';
            }

            html += '<div class="search-dropdown-item" data-index="' + idx + '" data-id="' + c.id + '">';
            html += '<div class="proprio-avatar">' + escHtml(initials) + '</div>';
            html += '<div>';
            html += '<div class="proprio-info">' + escHtml(c.nom + ' ' + c.prenom) + '</div>';
            html += '<div class="animaux-info">' + animauxHtml + '</div>';
            html += '</div>';
            html += '</div>';
        });
        dropdown.innerHTML = html;
        dropdown.classList.add('active');

        // Ajouter les événements click
        dropdown.querySelectorAll('.search-dropdown-item').forEach(function(item) {
            item.addEventListener('click', function() {
                selectClient(parseInt(item.getAttribute('data-id')));
            });
        });
    }

    function selectClient(id) {
        // Rediriger vers la page facturation avec le client sélectionné
        window.location.href = '<?= route('facturation.index') ?>' +
            (('<?= route('facturation.index') ?>').indexOf('?') !== -1 ? '&' : '?') + 'client=' + id;
    }

    function updateHighlight() {
        dropdown.querySelectorAll('.search-dropdown-item').forEach(function(item, i) {
            item.classList.toggle('highlighted', i === highlightedIndex);
            if (i === highlightedIndex) {
                item.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    input.addEventListener('input', function() {
        var q = input.value.trim();
        clearBtn.style.display = q.length > 0 ? 'flex' : 'none';
        var results = filterClients(q);
        renderDropdown(results);
    });

    input.addEventListener('focus', function() {
        var q = input.value.trim();
        var results = filterClients(q);
        renderDropdown(results);
    });

    input.addEventListener('keydown', function(e) {
        if (!dropdown.classList.contains('active')) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (highlightedIndex < filteredResults.length - 1) {
                highlightedIndex++;
                updateHighlight();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (highlightedIndex > 0) {
                highlightedIndex--;
                updateHighlight();
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndex >= 0 && highlightedIndex < filteredResults.length) {
                selectClient(filteredResults[highlightedIndex].id);
            }
        } else if (e.key === 'Escape') {
            dropdown.classList.remove('active');
        }
    });

    clearBtn.addEventListener('click', function() {
        input.value = '';
        clearBtn.style.display = 'none';
        input.focus();
        var results = filterClients('');
        renderDropdown(results);
    });

    // Fermer le dropdown quand on clique en dehors
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.client-search-wrapper')) {
            dropdown.classList.remove('active');
        }
    });
})();

(function() {
    var soinsTypes = ['Bain Brush', 'Coupe Ciseaux', 'Coupe', 'Tonte', 'Épilation', 'Coupe Griffes', 'Retouche', 'New Look'];

    // === MULTI-SÉLECTION DES ANIMAUX ===
    var animalCards = document.querySelectorAll('.animal-card');
    var formSection = document.getElementById('nouvelle-facture');
    var soinsContainer = document.getElementById('animaux-soins-container');
    var selectedAnimals = {}; // { id: { name, espece } }

    function rebuildSoinsBlocks() {
        // Garder les valeurs existantes
        var existingData = {};
        soinsContainer.querySelectorAll('.animal-soin-block').forEach(function(block) {
            var id = block.getAttribute('data-animal-id');
            var checkboxes = block.querySelectorAll('input[type="checkbox"]:checked');
            var soins = [];
            checkboxes.forEach(function(cb) { soins.push(cb.value); });
            var notes = block.querySelector('textarea') ? block.querySelector('textarea').value : '';
            var prixInput = block.querySelector('.prix-animal-input');
            var prix = prixInput ? prixInput.value : '';
            var dureeHInput = block.querySelector('.duree-heures-input');
            var dureeMInput = block.querySelector('.duree-minutes-input');
            var dureeHeures = dureeHInput ? dureeHInput.value : '';
            var dureeMinutes = dureeMInput ? dureeMInput.value : '';
            existingData[id] = { soins: soins, notes: notes, prix: prix, dureeHeures: dureeHeures, dureeMinutes: dureeMinutes };
        });

        soinsContainer.innerHTML = '';
        var ids = Object.keys(selectedAnimals);

        if (ids.length === 0) {
            formSection.style.display = 'none';
            return;
        }

        formSection.style.display = 'block';

        ids.forEach(function(id) {
            var animal = selectedAnimals[id];
            var prev = existingData[id] || { soins: [], notes: '', dureeHeures: '', dureeMinutes: '' };

            var block = document.createElement('div');
            block.className = 'animal-soin-block';
            block.setAttribute('data-animal-id', id);

            var icon = '<i class="fa-solid fa-dog"></i>';
            if (animal.espece === 'chat') icon = '<i class="fa-solid fa-cat"></i>';
            else if (animal.espece === 'lapin') icon = '<i class="fa-solid fa-rabbit"></i>';

            var html = '';
            html += '<div class="block-header">';
            html += '<span class="animal-badge">' + icon + ' ' + escapeHtml(animal.name) + '</span>';
            html += '<input type="hidden" name="animaux[' + id + '][id_animal]" value="' + id + '">';
            html += '</div>';

            // Soins checkboxes
            html += '<div style="margin-bottom: 15px;">';
            html += '<label style="font-weight:600; color:#475569;">Types de soins</label>';
            html += '<div class="prestation-selector">';
            soinsTypes.forEach(function(soin, i) {
                var cbId = 'soin-' + id + '-' + i;
                var checked = prev.soins.indexOf(soin) !== -1 ? ' checked' : '';
                html += '<input type="checkbox" name="animaux[' + id + '][type_soin][]" id="' + cbId + '" value="' + escapeHtml(soin) + '"' + checked + '>';
                html += '<label for="' + cbId + '" class="prestation-label">' + escapeHtml(soin) + '</label>';
            });
            html += '</div></div>';

            // Notes
            html += '<div style="margin-bottom: 15px;">';
            html += '<label style="font-weight:600; color:#475569;">Notes & Observations</label>';
            html += '<textarea name="animaux[' + id + '][notes]" rows="2" style="width:100%; border:1px solid #ddd; border-radius:8px; padding:10px; box-sizing:border-box; margin-top:5px;" placeholder="État du poil, comportement...">' + escapeHtml(prev.notes) + '</textarea>';
            html += '</div>';

            // Durée du toilettage (heures + minutes)
            html += '<div style="margin-bottom: 15px;">';
            html += '<label style="font-weight:600; color:#475569;"><i class="fa-regular fa-clock"></i> Temps de toilettage</label>';
            html += '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:5px;">';
            html += '<div>';
            html += '<input type="number" min="0" step="1" name="animaux[' + id + '][duree_heures]" class="duree-heures-input" placeholder="Heures (ex: 1)"';
            html += ' value="' + (prev.dureeHeures || '') + '"';
            html += ' style="width:100%; padding:10px; border:2px solid #e2e8f0; border-radius:10px; background:#f8fafc; font-size:1rem; box-sizing:border-box;">';
            html += '</div>';
            html += '<div>';
            html += '<input type="number" min="0" max="59" step="1" name="animaux[' + id + '][duree_minutes_part]" class="duree-minutes-input" placeholder="Minutes (ex: 30)"';
            html += ' value="' + (prev.dureeMinutes || '') + '"';
            html += ' style="width:100%; padding:10px; border:2px solid #e2e8f0; border-radius:10px; background:#f8fafc; font-size:1rem; box-sizing:border-box;">';
            html += '</div>';
            html += '</div>';
            html += '<small style="display:block; margin-top:6px; color:#64748b;">Exemple: 1h30 → Heures: 1 / Minutes: 30</small>';
            html += '</div>';

            // Prix par animal
            html += '<div>';
            html += '<label style="font-weight:600; color:#475569;"><i class="fa-solid fa-euro-sign"></i> Prix pour ' + escapeHtml(animal.name) + '</label>';
            html += '<input type="number" step="0.01" name="animaux[' + id + '][prix]" class="prix-animal-input" placeholder="ex: 45.00" required';
            html += ' value="' + (prev.prix || '') + '"';
            html += ' style="width:100%; padding:10px; border:2px solid #e2e8f0; border-radius:10px; background:#f8fafc; font-size:1rem; font-weight:700; box-sizing:border-box; margin-top:5px;">';
            html += '</div>';

            block.innerHTML = html;
            soinsContainer.appendChild(block);
        });

        // Ajouter les listeners pour recalculer le total
        soinsContainer.querySelectorAll('.prix-animal-input').forEach(function(inp) {
            inp.addEventListener('input', recalcTotal);
        });

        // Auto-focus: après saisie des heures, aller sur minutes
        soinsContainer.querySelectorAll('.duree-heures-input').forEach(function(inp) {
            inp.addEventListener('input', function() {
                var val = String(inp.value || '').trim();
                var block = inp.closest('.animal-soin-block');
                var minInput = block ? block.querySelector('.duree-minutes-input') : null;
                if (!minInput) return;
                if (val !== '' && /^\d+$/.test(val)) {
                    minInput.focus();
                    minInput.select();
                }
            });
        });

        recalcTotal();
        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function recalcTotal() {
        var total = 0;
        document.querySelectorAll('.prix-animal-input').forEach(function(inp) {
            var v = parseFloat(inp.value);
            if (!isNaN(v)) total += v;
        });
        // Ajouter les ventes globales
        document.querySelectorAll('input[name="ventes_globales[prix][]"]').forEach(function(inp) {
            var v = parseFloat(inp.value);
            if (!isNaN(v)) total += v;
        });
        // Ajouter les remises globales (montants négatifs)
        document.querySelectorAll('input[name="remises_globales[prix][]"]').forEach(function(inp) {
            var v = parseFloat(inp.value);
            if (!isNaN(v)) total += v;
        });
        var totalDisplay = document.getElementById('total-display');
        var totalInput = document.getElementById('input-prix-total');
        if (totalDisplay) totalDisplay.textContent = total.toFixed(2).replace('.', ',') + ' \u20ac';
        if (totalInput) totalInput.value = total.toFixed(2);
    }

    animalCards.forEach(function(card) {
        card.addEventListener('click', function() {
            var id = card.getAttribute('data-id');
            var name = card.getAttribute('data-name');
            var espece = card.getAttribute('data-espece');

            if (selectedAnimals[id]) {
                // Désélectionner
                delete selectedAnimals[id];
                card.classList.remove('selected');
            } else {
                // Sélectionner
                selectedAnimals[id] = { name: name, espece: espece };
                card.classList.add('selected');
            }

            rebuildSoinsBlocks();
        });
    });

    // === MODAL VENTE ===
    var modal = document.getElementById('modal-vente');
    var btnOpen = document.getElementById('btn-open-vente');
    var btnClose = document.getElementById('btn-close-vente');
    var detailInput = document.getElementById('vente-detail');
    var prixInput = document.getElementById('vente-prix');
    var btnAjouter = document.getElementById('btn-vente-ajouter');
    var typeBtns = document.querySelectorAll('.vente-type-btn');
    var ventesContainer = document.getElementById('ventes-ajoutees');
    var hiddenContainer = document.getElementById('ventes-hidden-inputs');
    var selectedType = '';
    var venteCount = 0;
    var remiseCount = 0;

    // === MODAL REMISE ===
    var remiseModal = document.getElementById('modal-remise');
    var btnOpenRemise = document.getElementById('btn-open-remise');
    var btnCloseRemise = document.getElementById('btn-close-remise');
    var remisePrixInput = document.getElementById('remise-prix');
    var btnAjouterRemise = document.getElementById('btn-remise-ajouter');
    var remisesContainer = document.getElementById('remises-ajoutees');
    var remisesHiddenContainer = document.getElementById('remises-hidden-inputs');

    if (btnOpen) {
        btnOpen.addEventListener('click', function() {
            selectedType = '';
            detailInput.value = '';
            prixInput.value = '';
            btnAjouter.disabled = true;
            typeBtns.forEach(function(b) { b.classList.remove('selected'); });
            modal.classList.add('active');
        });
    }

    if (btnClose) btnClose.addEventListener('click', function() { modal.classList.remove('active'); });
    if (modal) modal.addEventListener('click', function(e) { if (e.target === modal) modal.classList.remove('active'); });

    if (btnOpenRemise) {
        btnOpenRemise.addEventListener('click', function() {
            remisePrixInput.value = '';
            btnAjouterRemise.disabled = true;
            remiseModal.classList.add('active');
        });
    }

    if (btnCloseRemise) btnCloseRemise.addEventListener('click', function() { remiseModal.classList.remove('active'); });
    if (remiseModal) remiseModal.addEventListener('click', function(e) { if (e.target === remiseModal) remiseModal.classList.remove('active'); });

    typeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            typeBtns.forEach(function(b) { b.classList.remove('selected'); });
            btn.classList.add('selected');
            selectedType = btn.getAttribute('data-type');
            checkVenteForm();
        });
    });

    if (prixInput) prixInput.addEventListener('input', checkVenteForm);

    function checkVenteForm() {
        btnAjouter.disabled = !(parseFloat(prixInput.value) > 0 && selectedType !== '');
    }

    function checkRemiseForm() {
        if (!btnAjouterRemise || !remisePrixInput) return;
        var v = parseFloat(remisePrixInput.value);
        btnAjouterRemise.disabled = !( !isNaN(v) && Math.abs(v) > 0 );
    }

    if (remisePrixInput) remisePrixInput.addEventListener('input', checkRemiseForm);

    if (btnAjouter) {
        btnAjouter.addEventListener('click', function() {
            var type = selectedType;
            var detail = detailInput.value.trim();
            var prix = parseFloat(prixInput.value);
            var venteId = 'vente-' + (++venteCount);
            var label = 'Vente ' + type + (detail ? ' (' + detail + ')' : '') + ' \u2014 ' + prix.toFixed(2) + ' \u20ac';

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ventes_globales[type][]';
            input.value = 'Vente ' + type + (detail ? ' : ' + detail : '') + ' (' + prix.toFixed(2) + '\u20ac)';
            input.id = venteId;
            hiddenContainer.appendChild(input);

            var inputPrix = document.createElement('input');
            inputPrix.type = 'hidden';
            inputPrix.name = 'ventes_globales[prix][]';
            inputPrix.value = prix.toFixed(2);
            inputPrix.id = venteId + '-prix';
            hiddenContainer.appendChild(inputPrix);

            var badge = document.createElement('span');
            badge.id = venteId + '-badge';
            badge.style.cssText = 'display:inline-flex; align-items:center; gap:6px; background:#ede9fe; color:#7c3aed; padding:6px 14px; border-radius:20px; font-size:0.85em; font-weight:600; border:1px solid #ddd6fe;';
            badge.innerHTML = '<i class="fa-solid fa-cart-shopping"></i> ' + escapeHtml(label) + ' <button type="button" style="background:none; border:none; color:#a78bfa; cursor:pointer; font-size:1.1em; padding:0 0 0 4px;" data-vente-id="' + venteId + '">&times;</button>';
            ventesContainer.appendChild(badge);

            badge.querySelector('button').addEventListener('click', function() {
                document.getElementById(venteId).remove();
                document.getElementById(venteId + '-prix').remove();
                badge.remove();
                recalcTotal();
            });

            modal.classList.remove('active');
            recalcTotal();
        });
    }

    if (btnAjouterRemise) {
        btnAjouterRemise.addEventListener('click', function() {
            var amountRaw = Math.abs(parseFloat(remisePrixInput.value));
            if (isNaN(amountRaw) || amountRaw <= 0) return;

            var prix = -amountRaw;
            var remiseId = 'remise-' + (++remiseCount);
            var libelle = 'Remise commerciale';
            var label = libelle + ' — ' + prix.toFixed(2) + ' \u20ac';

            var inputType = document.createElement('input');
            inputType.type = 'hidden';
            inputType.name = 'remises_globales[type][]';
            inputType.value = libelle + ' (' + prix.toFixed(2) + '\u20ac)';
            inputType.id = remiseId;
            remisesHiddenContainer.appendChild(inputType);

            var inputPrix = document.createElement('input');
            inputPrix.type = 'hidden';
            inputPrix.name = 'remises_globales[prix][]';
            inputPrix.value = prix.toFixed(2);
            inputPrix.id = remiseId + '-prix';
            remisesHiddenContainer.appendChild(inputPrix);

            var badge = document.createElement('span');
            badge.id = remiseId + '-badge';
            badge.style.cssText = 'display:inline-flex; align-items:center; gap:6px; background:#fee2e2; color:#b91c1c; padding:6px 14px; border-radius:20px; font-size:0.85em; font-weight:600; border:1px solid #fecaca;';
            badge.innerHTML = '<i class="fa-solid fa-percent"></i> ' + escapeHtml(label) + ' <button type="button" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:1.1em; padding:0 0 0 4px;" data-remise-id="' + remiseId + '">&times;</button>';
            remisesContainer.appendChild(badge);

            badge.querySelector('button').addEventListener('click', function() {
                document.getElementById(remiseId).remove();
                document.getElementById(remiseId + '-prix').remove();
                badge.remove();
                recalcTotal();
            });

            remiseModal.classList.remove('active');
            recalcTotal();
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
</script>

<div id="notes-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:10050; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; width:min(680px, 100%); max-height:80vh; overflow:auto; border-radius:14px; box-shadow:0 24px 64px rgba(0,0,0,.25); padding:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:10px;">
            <h4 style="margin:0; color:#1e293b;"><i class="fa-regular fa-note-sticky"></i> Observation complète</h4>
            <button type="button" id="notes-modal-close" style="background:none; border:none; font-size:1.5rem; line-height:1; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <div id="notes-modal-content" style="white-space:pre-wrap; color:#334155; line-height:1.5;"></div>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById('notes-modal');
    var closeBtn = document.getElementById('notes-modal-close');
    var content = document.getElementById('notes-modal-content');
    if (!modal || !closeBtn || !content) return;

    function closeModal() { modal.style.display = 'none'; }
    function openModal(text) {
        content.textContent = text || '-';
        modal.style.display = 'flex';
    }

    document.addEventListener('click', function(e) {
        var trigger = e.target.closest('.note-preview-btn');
        if (trigger) {
            openModal(trigger.getAttribute('data-full-note') || '');
            return;
        }
        if (e.target === modal) {
            closeModal();
        }
    });

    closeBtn.addEventListener('click', closeModal);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });
})();
</script>

</body>
</html>
