<?php
$projectRoot = realpath(__DIR__ . '/../../') ?: dirname(__DIR__, 2);
$publicDirs = [];
$pushPublicDir = static function ($dir) use (&$publicDirs): void {
    if (!$dir) {
        return;
    }
    $real = realpath($dir);
    if ($real && is_dir($real) && !in_array($real, $publicDirs, true)) {
        $publicDirs[] = $real;
    }
};
$pushPublicDir($projectRoot . '/public');
$pushPublicDir($projectRoot . '/public_html');
$pushPublicDir($_SERVER['DOCUMENT_ROOT'] ?? null);

$logoCandidates = [];
$logoRelativeCandidates = [
    'assets/images/patte.png',
    'assets/images/patte.jpg',
    'assets/images/patte.jpeg',
    'assets/images/patte.svg',
    'assets/Sans titre-1.png',
];
foreach ($publicDirs as $publicDir) {
    foreach ($logoRelativeCandidates as $relativePath) {
        $logoCandidates[] = $publicDir . '/' . $relativePath;
    }
}
$logoPath = null;
foreach ($logoCandidates as $candidate) {
    $real = realpath($candidate);
    if ($real && is_file($real)) {
        $logoPath = $real;
        break;
    }
}
$logoDataUri = null;
if ($logoPath && is_readable($logoPath)) {
    $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
    $mime = 'image/png';
    if ($ext === 'jpg' || $ext === 'jpeg') $mime = 'image/jpeg';
    if ($ext === 'svg') $mime = 'image/svg+xml';
    $bin = file_get_contents($logoPath);
    if ($bin !== false) {
        $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode($bin);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - SweetyDog</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
            background: #ffffff;
            color: #111;
        }

        .container-facture {
            padding: 0;
            background: #ffffff;
        }
        .top-zone {
            padding: 18px 0 0 20px;
        }

        .accent {
            color: #778572;
        }

        .top-layout {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 34px;
        }
        .top-layout td {
            vertical-align: top;
        }
        .brand-row {
            border-collapse: collapse;
            margin-left: -60px;
        }

        .brand-title {
            margin: -54px 0 0 0;
            font-size: 60px;
            line-height: 0.85;
            letter-spacing: 0;
            font-family: "Beloved Script", "Brush Script MT", "Segoe Script", "DejaVu Sans", cursive;
            font-weight: 700;
        }
        .brand-phone {
            margin-top: -10px;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.1;
        }
        .brand-box {
            vertical-align: top;
            padding-top: 0;
        }
        .paw-box {
            width: 96px;
            text-align: center;
            line-height: 1;
            vertical-align: top;
            color: #000;
            padding-right: 8px;
        }
        .paw-logo {
            width: 128px;
            height: auto;
            display: block;
            margin-top: -18px;
        }
        .paw-fallback {
            font-size: 46px;
            line-height: 1;
            display: inline-block;
        }
        .seller-info {
            margin-top: 12px;
            font-size: 11px;
            line-height: 1.4;
        }

        .client-card {
            width: calc(100% + 56px);
            max-width: none;
            display: block;
            margin-left: auto;
            margin-right: -56px;
            margin-top: 0;
        }
        .client-title {
            background: #778572;
            color: #000;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            line-height: 1.1;
            padding: 4px 0;
            margin: 0 0 8px 0;
        }
        .client-text {
            font-size: 12px;
            line-height: 1.45;
            text-align: left;
        }

        .invoice-meta {
            width: 48%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .invoice-zone {
            margin-left: -44px;
            margin-right: -44px;
            margin-top: 52px;
        }
        .invoice-meta th {
            background: #778572;
            color: #000;
            border: 2px solid #111;
            font-size: 17px;
            line-height: 1.1;
            padding: 4px 8px;
            text-align: center;
            font-weight: 700;
        }
        .invoice-meta td {
            border: 2px solid #111;
            background: #ffffff;
            font-size: 13px;
            line-height: 1.15;
            text-align: center;
            padding: 5px 8px;
            font-weight: 500;
        }

        .line-top {
            height: 6px;
            background: #778572;
            margin: 8px 0 0 0;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .items th {
            border: 2px solid #111;
            font-size: 14px;
            text-align: center;
            padding: 6px 6px;
            font-weight: 700;
            background: #ffffff;
        }
        .items td {
            font-size: 13px;
            border-bottom: 1px solid #cfcfcf;
            padding: 7px 8px;
            vertical-align: middle;
            background: #ffffff;
        }
        .items td.col-left {
            text-align: left;
            padding-left: 14px;
            font-weight: 600;
        }
        .items td.col-center {
            text-align: center;
        }
        .items td.col-right {
            text-align: center;
            font-weight: 600;
        }

        .line-bottom {
            height: 6px;
            background: #778572;
            margin: 0 0 8px 0;
        }
        .items tr.striped.pale td {
            /* #778572 avec ~37% d'opacité sur fond blanc */
            background: #cdd2cb;
        }
        .items tr.filler-row td {
            padding: 0;
            height: 27px;
            border-bottom: 1px solid #cfcfcf;
            background: #ffffff;
        }

        .totals {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
        }
        .totals td {
            text-align: right;
            font-size: 14px;
            font-weight: 700;
            padding-right: 6px;
        }

        .vat {
            text-align: right;
            font-size: 10px;
            margin-top: 5px;
            padding-right: 6px;
        }
        .legal-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 12px;
            text-align: center;
            color: #333;
            margin: 0;
            padding: 0 24px;
        }
        .legal-id {
            font-size: 9px;
            margin: 0 0 2px 0;
        }
        .late-fee {
            font-size: 9px;
            margin: 0;
        }
        .payment-mode {
            text-align: right;
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
            padding-right: 6px;
        }
    </style>
</head>
<body>

<div class="container-facture">
    <?php
        $factureDate = isset($data['date_soin']) ? date('d.m.Y', strtotime($data['date_soin'])) : date('d.m.Y');
        $numeroCourt = (string)($data['numero_facture_formate'] ?? '');
        if ($numeroCourt === '') {
            $numeroCourt = str_pad((string)($data['id_prestation'] ?? 0), 9, '0', STR_PAD_LEFT);
        }
        $lignes = (!empty($data['is_multi']) && !empty($data['prestations'])) ? $data['prestations'] : [$data];
        $totalAffiche = !empty($data['prix_total']) ? (float)$data['prix_total'] : (float)($data['prix'] ?? 0);

        // Regrouper uniquement si même libellé ET même prix unitaire
        $lignesRegroupees = [];
        foreach ($lignes as $ligne) {
            $description = trim((string)($ligne['type_soin'] ?? 'Toilettage'));
            if ($description === '') {
                $description = 'Toilettage';
            }

            $prixUnitaire = (float)($ligne['prix'] ?? 0);
            $prixKey = number_format($prixUnitaire, 2, '.', '');
            $descriptionKey = function_exists('mb_strtolower')
                ? mb_strtolower($description, 'UTF-8')
                : strtolower($description);
            $key = $descriptionKey . '|' . $prixKey;

            if (!isset($lignesRegroupees[$key])) {
                $lignesRegroupees[$key] = [
                    'description' => $description,
                    'prix_unitaire' => $prixUnitaire,
                    'quantite' => 0,
                    'montant' => 0.0,
                ];
            }

            $lignesRegroupees[$key]['quantite']++;
            $lignesRegroupees[$key]['montant'] += $prixUnitaire;
        }

        $modePaiements = [];
        foreach ($lignes as $ligneMode) {
            $rawMode = trim((string)($ligneMode['mode_paiement'] ?? ''));
            if ($rawMode === '') {
                continue;
            }

            if ($rawMode === 'CB') {
                $labelMode = 'Carte bancaire';
            } elseif ($rawMode === 'Chèque') {
                $labelMode = 'Chèque';
            } elseif ($rawMode === 'Espèces') {
                $labelMode = 'Espèces';
            } else {
                $labelMode = $rawMode;
            }

            $modePaiements[$labelMode] = true;
        }
        $modeReglement = empty($modePaiements) ? '-' : implode(' / ', array_keys($modePaiements));
    ?>

    <div class="top-zone">
        <table class="top-layout">
            <tr>
                <td style="width: 58%;">
                    <table class="brand-row">
                        <tr>
                            <td class="paw-box">
                                <?php if ($logoDataUri): ?>
                                    <img class="paw-logo" src="<?php echo htmlspecialchars($logoDataUri, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                                <?php else: ?>
                                    <span class="paw-fallback">🐾</span>
                                <?php endif; ?>
                            </td>
                            <td class="brand-box">
                                <h1 class="brand-title">SweetyDog</h1>
                                <div class="brand-phone">06 44 01 56 86</div>
                            </td>
                        </tr>
                    </table>

                    <div class="seller-info">
                        Delenclos Johanna<br>
                        La Pommerie<br>
                        41200, Pruniers-en-Sologne<br>
                        Sweetydog41@gmail.com
                    </div>
                </td>
                <td style="width: 42%; padding-left: 0; padding-top: 186px; padding-right: 0; text-align: right;">
                    <div class="client-card">
                        <div class="client-title">Client</div>
                        <div class="client-text">
                            <?php echo htmlspecialchars(trim(($data['nom'] ?? '') . ' ' . ($data['prenom'] ?? '')) ?: 'Client'); ?><br>
                            <?php if (!empty($data['rue'])): ?>
                                <?php echo htmlspecialchars($data['rue']); ?><br>
                            <?php endif; ?>
                            <?php
                                $cpVille = trim(($data['code_postal'] ?? '') . ' ' . ($data['ville'] ?? ''));
                                if ($cpVille !== ''):
                            ?>
                                <?php echo htmlspecialchars($cpVille); ?>
                            <?php else: ?>
                                <?php echo htmlspecialchars($data['telephone'] ?? ''); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="invoice-zone">
        <table class="invoice-meta">
            <tr>
                <th>Facture</th>
                <th>Date</th>
            </tr>
                <tr>
                    <td>N°<?php echo $numeroCourt; ?></td>
                    <td><?php echo $factureDate; ?></td>
                </tr>
        </table>

        <div class="line-top"></div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 48%;">Description</th>
                    <th style="width: 12%;">Quantité</th>
                    <th style="width: 20%;">Prix unitaire €</th>
                    <th style="width: 20%;">Montant €</th>
                </tr>
            </thead>
            <tbody>
                <?php $lignesAffichees = array_values($lignesRegroupees); ?>
                <?php foreach ($lignesAffichees as $index => $ligne): ?>
                    <?php $rowClass = ((int)$index % 2 === 1) ? 'striped pale' : 'striped'; ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td class="col-left"><?php echo htmlspecialchars((string)$ligne['description']); ?></td>
                        <td class="col-center"><?php echo (int)$ligne['quantite']; ?></td>
                        <td class="col-right"><?php echo number_format((float)$ligne['prix_unitaire'], 2, ',', ' '); ?></td>
                        <td class="col-right"><?php echo number_format((float)$ligne['montant'], 2, ',', ' '); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php $rowOffset = count($lignesAffichees); ?>
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <?php $fillClass = ((($rowOffset + $i) % 2) === 1) ? 'filler-row striped pale' : 'filler-row striped'; ?>
                    <tr class="<?php echo $fillClass; ?>">
                        <td colspan="4">&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        <div class="line-bottom"></div>

        <table class="totals">
            <tr>
                <td>TOTAL: <?php echo number_format($totalAffiche, 2, ',', ' '); ?> €</td>
            </tr>
        </table>
        <div class="payment-mode">Règlement : <?php echo htmlspecialchars($modeReglement); ?></div>
        <div class="vat">TVA non applicable, article 293 B du CGI</div>
        <div class="legal-footer">
            <div class="legal-id">N° Siret: 925 223 372 00010 - Entreprise individuelle</div>
            <div class="late-fee">Indemnité forfaitaire de 40 € pour frais de recouvrement en cas de retard de paiement.</div>
        </div>
    </div>
</div>

</body>
</html>
