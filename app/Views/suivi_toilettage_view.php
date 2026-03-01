
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi - <?= htmlspecialchars($animal['nom_animal'] ?? 'Animal'); ?></title>

    <!-- ✅ CSS : chemin ABSOLU (sinon cassé sur /animals/33/tracking) -->
    <link rel="stylesheet" href="<?= url('assets/style.css') ?>">


    <style>
        .prestation-selector { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
        .prestation-selector input[type="checkbox"], .paiement-selector input[type="radio"] { display: none; }
        .prestation-label {
            padding: 8px 16px; background: #f0f0f0; border: 2px solid #ddd;
            border-radius: 20px; cursor: pointer; font-size: 0.9em; transition: all 0.3s ease;
            color: #555;
        }
        .prestation-selector input[type="checkbox"]:checked + .prestation-label {
            background-color: var(--vert-moyen); border-color: var(--vert-fonce); color: white;
        }
        /* Mode de paiement */
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
        .tag-soin {
            background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 6px;
            font-size: 0.8em; font-weight: 600; border: 1px solid #c8e6c9; margin-right: 5px;
        }
        .tag-vente {
            background: #ede9fe; color: #6d28d9; padding: 4px 10px; border-radius: 6px;
            font-size: 0.8em; font-weight: 600; border: 1px solid #ddd6fe; margin-right: 5px;
        }
        .info-bandeau {
            background: #fff; padding: 15px; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px;
            display: flex; gap: 20px; flex-wrap: wrap; border: 1px solid #eee;
        }
        .info-box { flex: 1; min-width: 120px; }
        .info-box strong { display: block; color: #7f8c8d; font-size: 0.75rem; text-transform: uppercase; }
        .btn-download-pdf {
            text-decoration: none;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: bold;
            border: 1px solid #c8e6c9;
        }
        .btn-download-pdf:hover { background: #c8e6c9; }
        .btn-email-invoice {
            text-decoration: none;
            background: #e0f2fe;
            color: #075985;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: bold;
            border: 1px solid #bae6fd;
            margin-left: 6px;
        }
        .btn-email-invoice:hover { background: #bae6fd; }
        .btn-generate-invoice {
            text-decoration: none;
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: bold;
            border: 1px solid #ffeaa7;
        }
        .btn-generate-invoice:hover { background: #ffeaa7; }
        .upcoming-rdv-card {
            background: #ffffff;
            border: 1px solid #e8efe9;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 18px;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        }
        .upcoming-rdv-title {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 1rem;
        }
        .upcoming-rdv-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 8px;
        }
        .upcoming-rdv-link {
            display: block;
            text-decoration: none;
            color: inherit;
        }
        .upcoming-rdv-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            background: #f8faf9;
            border: 1px solid #e5ece7;
            border-radius: 10px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
        }
        .upcoming-rdv-link:hover .upcoming-rdv-item {
            border-color: #b8d8c4;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }
        .upcoming-rdv-main {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .upcoming-rdv-date {
            font-weight: 800;
            color: #166534;
            font-size: 0.92rem;
            white-space: nowrap;
        }
        .upcoming-rdv-time {
            color: #475569;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        .upcoming-rdv-type {
            color: #1f2937;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: right;
            max-width: 45%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .upcoming-rdv-open {
            color: #0f766e;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
            margin-left: 8px;
        }
        .upcoming-rdv-empty {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        /* === Modal Vente === */
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
        .modal-close {
            position: absolute; top: 12px; right: 15px; background: none; border: none;
            font-size: 1.5rem; cursor: pointer; color: #94a3b8; transition: color 0.2s;
        }
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

        /* ============================
           RESPONSIVE MOBILE
           ============================ */
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

            .header-flex {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 10px;
                background: linear-gradient(180deg, #ffffff 0%, #f5fbf7 100%);
                border: 1px solid #e6efe9;
                border-radius: 14px;
                padding: 12px;
            }
            .header-flex h2 { font-size: 1.1rem; }
            .header-flex .btn-edit {
                width: 100%;
                text-align: center;
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .info-bandeau {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                padding: 0;
                background: transparent;
                border: none;
                box-shadow: none;
            }
            .info-box {
                min-width: auto;
                background: #fff;
                border: 1px solid #e4efe8;
                border-radius: 12px;
                padding: 10px;
                box-shadow: 0 6px 14px rgba(15,23,42,0.05);
            }

            /* Formulaire nouvelle visite */
            #nouvelle-visite {
                padding: 15px !important;
                border-radius: 14px !important;
                border: 1px solid #e4efe8 !important;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }

            .prestation-selector { gap: 6px; }
            .prestation-label { padding: 6px 12px; font-size: 0.8em; }

            .paiement-selector { gap: 6px; }
            .paiement-label { padding: 8px 14px; font-size: 0.8em; }

            /* Prix + Paiement en colonne sur mobile */
            #nouvelle-visite div[style*="grid-template-columns: 1fr 1fr"] {
                display: flex !important;
                flex-direction: column !important;
                gap: 15px !important;
            }

            /* Tableau historique en cartes */
            table { border-spacing: 0; }
            table thead { display: none; }
            table, table tbody, table tr, table td { display: block; width: 100%; }
            table tr {
                margin-bottom: 12px;
                border: 1px solid #ebf2ed;
                border-radius: 14px;
                overflow: hidden;
                background: white;
                box-shadow: 0 8px 16px rgba(15,23,42,0.06);
            }
            table td {
                padding: 8px 15px !important;
                border-bottom: 1px solid #f8f9fa !important;
                text-align: left !important;
            }
            table td:before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 0.7rem;
                text-transform: uppercase;
                color: #94a3b8;
                display: block;
                margin-bottom: 3px;
            }
            table td:last-child { border-bottom: none !important; }

            .tag-soin, .tag-vente { font-size: 0.72em; padding: 3px 8px; margin-bottom: 3px; display: inline-block; }
            .upcoming-rdv-card {
                padding: 12px;
                border-radius: 14px;
            }
            .upcoming-rdv-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }
            .upcoming-rdv-type {
                max-width: 100%;
                text-align: left;
                white-space: normal;
            }
            .upcoming-rdv-open {
                margin-left: 0;
            }

            .btn-download-pdf, .btn-email-invoice, .btn-generate-invoice {
                font-size: 0.78em;
                padding: 4px 8px;
                min-height: 36px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-left: 0;
                margin-right: 6px;
                margin-bottom: 4px;
            }

            /* Modal vente */
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
            .btn-vente-submit {
                position: sticky;
                bottom: calc(env(safe-area-inset-bottom) + 8px);
                z-index: 5;
                box-shadow: 0 12px 26px rgba(15,23,42,0.16);
            }
            .vente-type-selector { flex-direction: column; gap: 8px; }
        }

        @media (max-width: 480px) {
            .header-flex h2 { font-size: 1rem; }
            .info-bandeau { font-size: 0.85rem; }
            .info-bandeau { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php if (isset($_GET['success']) && (int)$_GET['success'] === 1): ?>
    <div id="success-banner" style="background: #d4edda; color: #155724; padding: 20px; border: 1px solid #c3e6cb; border-radius: 8px; margin: 20px auto; max-width: 800px; text-align: center; font-family: sans-serif;">
        <h2 style="margin: 0 0 10px 0;">✅ Encaissement validé !</h2>
        <p style="margin: 5px 0;">La facture Factur-X a été générée et transmise à N2F.</p>

        <?php if (!empty($download_link)): ?>
            <p style="font-size: 0.9em; color: #666;">
                Le téléchargement de la facture va démarrer... <br>
                <small>Si rien ne se passe, <a href="<?= htmlspecialchars($download_link) ?>" download style="color: #155724; text-decoration: underline;">cliquez ici pour la récupérer</a>.</small>
            </p>

            <script>
                window.addEventListener('load', function() {
                    setTimeout(function() {
                        const link = document.createElement('a');
                        link.href = <?= json_encode($download_link) ?>;
                        link.download = <?= json_encode($nomFichierPDF ?? 'facture.pdf') ?>;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }, 500);
                });
            </script>
        <?php endif; ?>
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
    <div style="background: <?= $mailBg ?>; color: <?= $mailColor ?>; padding: 16px; border: 1px solid <?= $mailBorder ?>; border-radius: 8px; margin: 20px auto; max-width: 800px;">
        <strong><?= htmlspecialchars($mailTitle) ?> :</strong>
        <span><?= htmlspecialchars($mailMessage) ?></span>
    </div>
<?php endif; ?>

<div class="container-large">
    <div class="header-flex" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h2 style="margin-bottom: 5px;">🧼 Dossier de <?= htmlspecialchars($animal['nom_animal'] ?? ''); ?></h2>
            <span style="color: #7f8c8d;">
                Propriétaire : <strong><?= htmlspecialchars(($animal['prenom'] ?? '') . ' ' . ($animal['nom'] ?? '')); ?></strong>
            </span>
        </div>

        <!-- ✅ Retour : utiliser une route -->
        <a href="<?= route('clients.index') ?>" class="btn-edit" style="text-decoration: none; background: #eee; color: #333;">← Retour</a>
    </div>

    <div class="info-bandeau">
        <div class="info-box">
            <strong>Animal / Race</strong>
            🐾 <?= htmlspecialchars($animal['espece'] ?? ''); ?> (<?= htmlspecialchars($animal['race'] ?? 'Inconnue'); ?>)
        </div>
        <div class="info-box">
            <strong>Poids</strong>
            ⚖️ <?= !empty($animal['poids']) ? htmlspecialchars($animal['poids']).' kg' : '---'; ?>
        </div>
        <div class="info-box">
            <strong>Sexe</strong>
            <?php
                $sexe = $animal['sexe'] ?? '';
                if ($sexe === 'M') {
                    echo '<span style="color: #2980b9; font-weight: bold;">♂ Mâle</span>';
                } elseif ($sexe === 'F') {
                    echo '<span style="color: #e84393; font-weight: bold;">♀ Femelle</span>';
                } else {
                    echo '<span style="color: #7f8c8d;">---</span>';
                }
            ?>
        </div>
        <div class="info-box">
            <strong>Stérilisé</strong>
            <?php if(($animal['steril'] ?? 0) == 1): ?>
                <span style="color: #2e7d32; font-weight: bold;">✅ OUI</span>
            <?php else: ?>
                <span style="color: #e67e22; font-weight: bold;">❌ NON</span>
            <?php endif; ?>
        </div>
        <div class="info-box">
            <strong>Date de naissance</strong>
            <?php if (!empty($animal['date_naissance'])): ?>
                <?php
                    $dateNaiss = new DateTime($animal['date_naissance']);
                    $aujourdHui = new DateTime();
                    $diff = $dateNaiss->diff($aujourdHui);
                    if ($diff->y > 0) {
                        $age = $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
                        if ($diff->m > 0) $age .= ' ' . $diff->m . ' mois';
                    } else {
                        $age = $diff->m . ' mois';
                    }
                ?>
                🎂 <?= $dateNaiss->format('d/m/Y') ?> <span style="color: #2e7d32; font-weight: bold;">(<?= $age ?>)</span>
            <?php else: ?>
                <span style="color: #7f8c8d;">---</span>
            <?php endif; ?>
        </div>
        <div class="info-box">
            <strong>Téléphone</strong>
            📞 <?= !empty($animal['telephone']) ? htmlspecialchars(wordwrap($animal['telephone'], 2, ' ', true)) : '---'; ?>
        </div>
    </div>

    <div class="upcoming-rdv-card">
        <h3 class="upcoming-rdv-title">📅 Prochains rendez-vous</h3>
        <?php if (empty($prochains_rdv)): ?>
            <p class="upcoming-rdv-empty">Aucun rendez-vous à venir pour cet animal.</p>
        <?php else: ?>
            <ul class="upcoming-rdv-list">
                <?php foreach ($prochains_rdv as $rdv): ?>
                    <?php
                        $dateDebut = !empty($rdv['date_debut']) ? strtotime($rdv['date_debut']) : false;
                        $dateFin = !empty($rdv['date_fin']) ? strtotime($rdv['date_fin']) : false;
                        $agendaLink = null;
                        if ($dateDebut) {
                            $agendaLink = route('appointments.index') . '?' . http_build_query([
                                'calendar_view' => 'timeGridDay',
                                'calendar_date' => date('Y-m-d', $dateDebut),
                            ]);
                        }
                    ?>
                    <li>
                        <?php if ($agendaLink): ?>
                            <a class="upcoming-rdv-link" href="<?= htmlspecialchars($agendaLink); ?>" title="Ouvrir dans l'agenda">
                        <?php endif; ?>
                        <div class="upcoming-rdv-item">
                            <div class="upcoming-rdv-main">
                                <span class="upcoming-rdv-date">
                                    <?= $dateDebut ? date('d/m/Y', $dateDebut) : 'Date non définie'; ?>
                                </span>
                                <span class="upcoming-rdv-time">
                                    <?= $dateDebut ? date('H:i', $dateDebut) : '--:--'; ?>
                                    <?= $dateFin ? ' - ' . date('H:i', $dateFin) : ''; ?>
                                </span>
                            </div>
                            <span class="upcoming-rdv-type"><?= htmlspecialchars($rdv['titre'] ?? 'RDV'); ?></span>
                            <?php if ($agendaLink): ?>
                                <span class="upcoming-rdv-open">Voir agenda</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($agendaLink): ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <h3 id="historique-soins" style="margin-bottom: 15px;">Historique des soins</h3>
    <?php
        $invoiceFileExists = static function (string $fileName): bool {
            if ($fileName === '') {
                return false;
            }

            static $cache = [];
            if (array_key_exists($fileName, $cache)) {
                return $cache[$fileName];
            }

            $baseDirs = [
                __DIR__ . '/../../Factures',
                __DIR__ . '/../../factures',
            ];

            foreach ($baseDirs as $baseDir) {
                $flatPath = $baseDir . '/' . $fileName;
                if (is_file($flatPath)) {
                    $cache[$fileName] = true;
                    return true;
                }

                $matches = glob($baseDir . '/*/*/' . $fileName) ?: [];
                if (!empty($matches)) {
                    $cache[$fileName] = true;
                    return true;
                }
            }

            $cache[$fileName] = false;
            return false;
        };
    ?>
    <table style="background: white; border-radius: 12px; overflow: hidden; border-collapse: separate; border-spacing: 0; width: 100%;">
        <thead style="background: #f8f9fa;">
            <tr>
                <th style="padding: 15px; text-align: left;">Date</th>
                <th style="text-align: left;">Prestations</th>
                <th style="text-align: left;">Observations</th>
                <th style="text-align: left;">Prix</th>
                <th style="text-align: center;">Paiement</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(empty($historique)): ?>
            <tr><td colspan="6" style="padding: 20px; text-align: center;">Aucun soin enregistré.</td></tr>
        <?php else: ?>
            <?php foreach ($historique as $soin): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td data-label="Date" style="padding: 15px;"><strong><?= date('d/m/Y', strtotime($soin['date_soin'])); ?></strong></td>
                    <td data-label="Prestations">
                        <?php
                        $tags = explode(", ", $soin['type_soin'] ?? '');
                        foreach($tags as $tag){
                            $tag = trim($tag);
                            if($tag === '') continue;
                            if(strpos($tag, 'Vente') === 0) {
                                echo "<span class='tag-vente'>🛒 " . htmlspecialchars($tag) . "</span>";
                            } else {
                                echo "<span class='tag-soin'>" . htmlspecialchars($tag) . "</span>";
                            }
                        }
                        ?>
                    </td>
                    <td data-label="Notes" style="color: #7f8c8d; font-size: 0.9em;"><?= htmlspecialchars($soin['notes'] ?? '-'); ?></td>
                    <td data-label="Prix" style="white-space: nowrap;">
                        <?php
                            $prixAffiche = isset($soin['prix_apres_remise'])
                                ? (float)$soin['prix_apres_remise']
                                : (float)($soin['prix'] ?? 0);
                            $remiseMontant = (float)($soin['remise_montant'] ?? 0);
                        ?>
                        <span style="font-weight: bold; color: #2e7d32; white-space: nowrap;"><?= number_format($prixAffiche, 2, ',', ' '); ?>&nbsp;€</span>
                        <?php if (abs($remiseMontant) > 0.0001): ?>
                            <div style="font-size: 0.78em; color: #64748b; margin-top: 2px;">
                                remise: <?= number_format($remiseMontant, 2, ',', ' '); ?>&nbsp;€
                            </div>
                        <?php endif; ?>
                    </td>
                    <td data-label="Paiement" style="text-align: center;">
                        <?php
                            $mp = $soin['mode_paiement'] ?? '';
                            if ($mp === 'CB') {
                                echo '<span style="background:#dbeafe; color:#1e40af; padding:4px 10px; border-radius:6px; font-size:0.8em; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">💳 CB</span>';
                            } elseif ($mp === 'Chèque') {
                                echo '<span style="background:#fef3c7; color:#92400e; padding:4px 10px; border-radius:6px; font-size:0.8em; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">📝 Chèque</span>';
                            } elseif ($mp === 'Espèces') {
                                echo '<span style="background:#d1fae5; color:#065f46; padding:4px 10px; border-radius:6px; font-size:0.8em; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">💶 Espèces</span>';
                            } else {
                                echo '<span style="color:#94a3b8; font-size:0.8em;">—</span>';
                            }
                        ?>
                    </td>
                   <td data-label="Actions" style="text-align: center; white-space: nowrap;">
  <?php
    $idPrest = (int)($soin['id_prestation'] ?? 0);
    $numeroFacture = (int)($soin['numero_facture'] ?? 0);
    $numeroFactureFormate = $numeroFacture > 0
        ? str_pad((string)$numeroFacture, 9, '0', STR_PAD_LEFT)
        : '';
    $annee   = date('Y', strtotime($soin['date_soin'] ?? 'now'));

    // ✅ Nouveau + ancien nom de fichier pour compatibilité
    $nomFichierNew = $numeroFactureFormate !== '' ? "Facture_SweetyDog_{$numeroFactureFormate}.pdf" : '';
    $nomFichierLegacy = "Facture_SweetyDog_{$annee}-{$idPrest}.pdf";
    $hasPdf = ($nomFichierNew !== '' && $invoiceFileExists($nomFichierNew))
        || $invoiceFileExists($nomFichierLegacy);

    $groupIds = trim((string)($soin['facture_group_ids'] ?? ''));

    $pdfQuery = [];
    if ($groupIds !== '') {
        $pdfQuery['group'] = $groupIds;
    }
    // 🔒 URL SÉCURISÉE : passe par le contrôleur au lieu d'un accès direct
    $pdf_url = route('invoices.download', ['id' => $idPrest], $pdfQuery);

    $emailQuery = ['from' => 'tracking'];
    if ($groupIds !== '') {
        $emailQuery['group'] = $groupIds;
    }
    $emailUrl = route('invoices.email', ['id' => $idPrest], $emailQuery);

    $queryGenerate = [];
    if ($groupIds !== '') {
        $queryGenerate['group'] = $groupIds;
    }
    $generateUrl = route('invoices.generate', ['id' => $idPrest], $queryGenerate);
  ?>

  <?php if ($idPrest > 0 && $hasPdf) : ?>
    <a href="<?= htmlspecialchars($pdf_url) ?>"
       target="_blank"
       class="btn-download-pdf"
       title="Ouvrir la facture PDF">
      📄 Facture
    </a>
    <a href="<?= htmlspecialchars($emailUrl) ?>"
       class="btn-email-invoice"
       title="Envoyer la facture par mail">
      ✉️ Envoyer par mail
    </a>
  <?php elseif ($idPrest > 0) : ?>
    <a href="<?= htmlspecialchars($generateUrl) ?>"
       class="btn-generate-invoice"
       title="Générer la facture">
      ⚙️ Générer
    </a>
    <a href="<?= htmlspecialchars($emailUrl) ?>"
       class="btn-email-invoice"
       title="Envoyer la facture par mail">
      ✉️ Envoyer par mail
    </a>
  <?php else : ?>
    <span style="color:#94a3b8; font-size:0.85em;">—</span>
  <?php endif; ?>

  <span title="Prestation verrouillée"
        style="margin-left: 10px; cursor: help; filter: grayscale(100%); opacity: 0.5;">🔒</span>
</td>


                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ===== MODAL VENTE ===== -->
<div class="modal-overlay" id="modal-vente">
    <div class="modal-content">
        <button class="modal-close" id="btn-close-vente">&times;</button>
        <h3 class="modal-title">🛒 Ajouter une vente</h3>

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
            ✅ Ajouter au panier
        </button>
    </div>
</div>

<script>
(function() {
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

    // Ouvrir la modale
    btnOpen.addEventListener('click', function() {
        // Reset les champs
        selectedType = '';
        detailInput.value = '';
        prixInput.value = '';
        btnAjouter.disabled = true;
        typeBtns.forEach(function(b) { b.classList.remove('selected'); });
        modal.classList.add('active');
    });

    // Fermer la modale
    btnClose.addEventListener('click', function() {
        modal.classList.remove('active');
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.classList.remove('active');
    });

    // Sélection du type de vente
    typeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            typeBtns.forEach(function(b) { b.classList.remove('selected'); });
            btn.classList.add('selected');
            selectedType = btn.getAttribute('data-type');
            checkForm();
        });
    });

    prixInput.addEventListener('input', checkForm);

    function checkForm() {
        var prixOk = parseFloat(prixInput.value) > 0;
        var typeOk = selectedType !== '';
        btnAjouter.disabled = !(prixOk && typeOk);
    }

    // Ajouter la vente au formulaire principal
    btnAjouter.addEventListener('click', function() {
        var type = selectedType;
        var detail = detailInput.value.trim();
        var prix = parseFloat(prixInput.value);
        var venteId = 'vente-' + (++venteCount);
        var label = 'Vente ' + type + (detail ? ' (' + detail + ')' : '') + ' — ' + prix.toFixed(2) + ' €';

        // Créer le input hidden dans le formulaire principal
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'type_soin[]';
        input.value = 'Vente ' + type + (detail ? ' : ' + detail : '') + ' (' + prix.toFixed(2) + '€)';
        input.id = venteId;
        hiddenContainer.appendChild(input);

        // Aussi stocker le prix de la vente pour l'ajouter au total
        var inputPrix = document.createElement('input');
        inputPrix.type = 'hidden';
        inputPrix.name = 'vente_prix[]';
        inputPrix.value = prix.toFixed(2);
        inputPrix.id = venteId + '-prix';
        hiddenContainer.appendChild(inputPrix);

        // Afficher le badge visuel
        var badge = document.createElement('span');
        badge.id = venteId + '-badge';
        badge.style.cssText = 'display:inline-flex; align-items:center; gap:6px; background:#ede9fe; color:#7c3aed; padding:6px 14px; border-radius:20px; font-size:0.85em; font-weight:600; border:1px solid #ddd6fe;';
        badge.innerHTML = '🛒 ' + escapeHtml(label) + ' <button type="button" style="background:none; border:none; color:#a78bfa; cursor:pointer; font-size:1.1em; padding:0 0 0 4px;" data-vente-id="' + venteId + '">&times;</button>';
        ventesContainer.appendChild(badge);

        // Bouton supprimer le badge
        badge.querySelector('button').addEventListener('click', function() {
            document.getElementById(venteId).remove();
            document.getElementById(venteId + '-prix').remove();
            badge.remove();
        });

        // Fermer la modale
        modal.classList.remove('active');
    });

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
</script>

</body>
</html>
