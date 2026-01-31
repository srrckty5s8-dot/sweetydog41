<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres | SweetyDog</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/style.css')) ?>">
</head>
<body>

<div class="container-large">
    <div class="header-flex" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>⚙️ Paramètres du compte</h2>
        <a href="<?= htmlspecialchars(route('clients.index')) ?>" class="btn-edit" style="text-decoration: none; background:#eee; color:#333;">← Retour</a>
    </div>

    <div class="container" style="max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <h3>Changer le mot de passe</h3>
        
        <?php if ($message): ?>
            <p style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px;"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <p style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px;"><?php echo $erreur; ?></p>
        <?php endif; ?>

        <form action="<?= htmlspecialchars(route('settings.index')) ?>" method="POST">
            <div style="margin-bottom: 15px;">
                <label>Ancien mot de passe</label>
                <input type="password" name="ancien_mdp" required style="width:100%; padding:10px; margin-top:5px; border:1px solid #ddd; border-radius:6px;">
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <div style="margin-bottom: 15px;">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" required style="width:100%; padding:10px; margin-top:5px; border:1px solid #ddd; border-radius:6px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label>Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirm_mdp" required style="width:100%; padding:10px; margin-top:5px; border:1px solid #ddd; border-radius:6px;">
            </div>

            <button type="submit" style="background: var(--vert-fonce); color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold;">
                Enregistrer le nouveau mot de passe
            </button>
        </form>
    </div>
</div>

</body>
</html>
