<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier client</title>
  <link rel="stylesheet" href="/sweetydog/assets/style.css">
</head>
<body>
<div class="container">
  <h2>✏️ Modifier le client</h2>

  <form action="<?= route('clients.update', ['id' => $proprio['id_proprietaire']]) ?>" method="POST">
    <input type="hidden" name="id_proprietaire" value="<?= (int)$proprio['id_proprietaire'] ?>">

    <div class="form-row">
      <div>
        <label>Prénom</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($proprio['prenom'] ?? '') ?>" required>
      </div>
      <div>
        <label>Nom</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($proprio['nom'] ?? '') ?>" required>
      </div>
    </div>

    <label>Téléphone</label>
    <input type="text" name="tel" value="<?= htmlspecialchars($proprio['telephone'] ?? '') ?>">

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($proprio['email'] ?? '') ?>">

    <label>Adresse</label>
    <textarea name="adresse" rows="2"><?= htmlspecialchars($proprio['adresse'] ?? '') ?></textarea>

    <button type="submit" style="width:100%; padding: 12px;">
      ✅ Enregistrer
    </button>
  </form>

  <p style="margin-top:12px;">
    <a href="<?= route('clients.index') ?>">← Retour liste</a>
  </p>
</div>
</body>
</html>
