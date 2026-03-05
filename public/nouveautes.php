<?php
$path = __DIR__ . '/data/nouveautes.json';
$items = [];
if (is_file($path)) {
    $raw = file_get_contents($path);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $items = $decoded;
    }
}
?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SweetyDog — Nouveautés</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f8fafc;color:#111827;padding:24px;max-width:900px;margin:auto}
    h1{margin:0 0 12px}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;margin-bottom:10px}
    .date{font-size:.85rem;color:#6b7280}
  </style>
</head>
<body>
  <h1>🦴 Nouveautés SweetyDog</h1>
  <p>Flux auto (cron toutes les 10 min) — mode démo.</p>
  <?php if (!$items): ?>
    <div class="card">Aucune nouveauté pour le moment.</div>
  <?php else: ?>
    <?php foreach ($items as $it): ?>
      <div class="card">
        <strong><?= htmlspecialchars($it['title'] ?? 'Mise à jour') ?></strong><br>
        <div><?= htmlspecialchars($it['body'] ?? '') ?></div>
        <div class="date">Mis à jour le <?= htmlspecialchars($it['updated_at'] ?? '') ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
