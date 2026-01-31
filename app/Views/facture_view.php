<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture - SweetyDog</title>
    <style>

        /* Styles optimisés pour DomPDF */
        body { font-family: 'DejaVu Sans', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        
        .container-facture {
            padding: 20px;
            background: white;
            border-top: 8px solid #2e7d32;
        }

        .facture-header { width: 100%; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .facture-header td { vertical-align: top; }

        .details-table { width: 100%; margin-bottom: 30px; }
        .details-table td { width: 50%; vertical-align: top; }

        h3 { border-bottom: 2px solid #f8f9fa; padding-bottom: 5px; color: #2e7d32; font-size: 16px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.items th { background: #f8f9fa; padding: 10px; text-align: left; border-bottom: 2px solid #eee; font-size: 14px; }
        table.items td { padding: 12px 10px; border-bottom: 1px solid #eee; font-size: 14px; }

        .total-box { 
            background: #f8f9fa; 
            padding: 15px; 
            text-align: right; 
            font-size: 1.4em; 
            color: #2e7d32;
            border: 1px solid #eee;
            margin-top: 10px;
        }

        .footer { margin-top: 40px; font-size: 10px; color: #777; text-align: center; }
        
        /* Cacher les boutons à l'impression écran, mais DomPDF ne les verra pas de toute façon */
        .no-print { display: none; }
    </style>
</head>
<body>

<div class="container-facture">
    <table class="facture-header">
        <tr>
            <td>
                <h1 style="color: #2e7d32; margin: 0; font-size: 24px;">SweetyDog41</h1>
                <p style="margin: 5px 0; font-size: 12px;">Salon de Toilettage<br>La Pommerie, Romorantin 41200</p>
            </td>
            <td style="text-align: right;">
                <h2 style="margin: 0; color: #666; font-size: 20px;">FACTURE</h2>
                <p style="margin: 5px 0; font-size: 12px;">
                    N° <?php echo date('Y') . '-' . ($data['id_prestation'] ?? '0'); ?><br>
                    Date : <?php echo isset($data['date_soin']) ? date('d/m/Y', strtotime($data['date_soin'])) : date('d/m/Y'); ?>
                </p>
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td>
                <h3>Client</h3>
                <p style="font-size: 13px;">
                    <strong><?php echo htmlspecialchars(($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? 'Client Inconnu')); ?></strong><br>
                    Tél : <?php echo htmlspecialchars($data['telephone'] ?? 'Non renseigné'); ?>
                </p>
            </td>
            <td>
                <h3>Animal</h3>
                <p style="font-size: 13px;">
                    Nom : <strong><?php echo htmlspecialchars($data['nom_animal'] ?? 'Non renseigné'); ?></strong><br>
                    Type : <?php echo htmlspecialchars($data['espece'] ?? 'Non précisé'); ?>
                </p>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description du soin</th>
                <th style="text-align: right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($data['type_soin'] ?? 'Soin de toilettage'); ?></td>
                <td style="text-align: right; font-weight: bold;">
                    <?php echo number_format($data['prix'] ?? 0, 2, ',', ' '); ?> €
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <strong>Total à régler : <?php echo number_format($data['prix'] ?? 0, 2, ',', ' '); ?> €</strong>
    </div>

    <div class="footer">
        <p>Merci de votre confiance et à bientôt chez SweetyDog41 !</p>
        <p><em>Facture émise électroniquement - Conforme au format Factur-X (Profil Minimum)</em></p>
    </div>
</div>

</body>
</html>