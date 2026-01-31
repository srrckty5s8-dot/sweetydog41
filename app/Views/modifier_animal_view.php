<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier <?= htmlspecialchars($animal['nom_animal']) ?> - SweetyDog</title>
    <link rel="stylesheet" href="/sweetydog/assets/style.css">

    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #333; }
        .form-edit { max-width: 600px; margin: 40px auto; background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        h2, h3 { color: var(--vert-fonce); margin-top: 0; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #555; font-size: 0.9rem; }
        input, select { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; background: #f8fafc; font-size: 1rem; }
        .btn-save { background: var(--vert-fonce); color: white; border: none; padding: 15px; width: 100%; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 25px; transition: 0.3s; }
        .btn-save:hover { background: #1b4332; transform: translateY(-2px); }
        .radio-group { margin-top: 10px; background: #f9f9f9; padding: 15px; border-radius: 10px; border: 1px solid #eee; }
    </style>
</head>
<body>

<div class="form-edit">
    <div style="text-align: center; margin-bottom: 20px;">
        <span style="font-size: 3rem;">🐶</span>
        <h2>Modifier l'animal</h2>
        <p style="color: #666;">Propriétaire : <strong><?= htmlspecialchars($animal['prenom'] . ' ' . $animal['nom']) ?></strong></p>
    </div>

    <form action="<?= route('animals.update', ['id' => $animal['id_animal']]) ?>" method="POST">

        <h3>🐾 L'Animal</h3>
        
        <div class="form-group">
            <label>Nom de l'animal</label>
            <input type="text" name="nom_animal" value="<?= htmlspecialchars($animal['nom_animal']) ?>" required>
            
            <label>Espèce</label>
            <select name="espece">
                <option value="Chien" <?= ($animal['espece'] ?? '') == 'Chien' ? 'selected' : '' ?>>Chien</option>
                <option value="Chat" <?= ($animal['espece'] ?? '') == 'Chat' ? 'selected' : '' ?>>Chat</option>
                <option value="Lapin" <?= ($animal['espece'] ?? '') == 'Lapin' ? 'selected' : '' ?>>Lapin</option>
                <option value="Autre" <?= ($animal['espece'] ?? '') == 'Autre' ? 'selected' : '' ?>>Autre</option>
            </select>

            <label for="race-choice">Race de l'animal</label>
            <input list="races" name="race" id="race-choice" value="<?= htmlspecialchars($animal['race'] ?? '') ?>" placeholder="Tapez pour chercher...">
            <datalist id="races">
                <option value="Bichon Frisé"><option value="Bichon Maltais"><option value="Berger Allemand"><option value="Berger Australien"><option value="Bouledogue Français"><option value="Caniche"><option value="Cavalier King Charles"><option value="Chihuahua"><option value="Cocker Spaniel"><option value="Golden Retriever"><option value="Labrador"><option value="Shih Tzu"><option value="Yorkshire Terrier">
            </datalist>

            <label>Poids (kg)</label>
            <input type="number" step="0.1" name="poids" value="<?= htmlspecialchars($animal['poids'] ?? '') ?>" placeholder="Ex: 12.5">

            <p style="margin-top: 15px; font-weight: bold; color: #555;">L'animal est-il stérilisé ?</p>
            <div class="radio-group">
                <input type="radio" name="steril" value="1" id="oui" style="width: auto;" <?= (($animal['steril'] ?? 0) == 1) ? 'checked' : '' ?>>
                <label for="oui" style="display: inline; margin-right: 20px; font-weight: normal;">Oui</label>
                
                <input type="radio" name="steril" value="0" id="non" style="width: auto;" <?= (($animal['steril'] ?? 0) == 0) ? 'checked' : '' ?>>
                <label for="non" style="display: inline; font-weight: normal;">Non</label>
            </div>
        </div>

        <button type="submit" name="modifier" class="btn-save">
            ✨ ENREGISTRER LES MODIFICATIONS
        </button>
        
        <a href="<?= route('clients.index') ?>" style="display:block; text-align:center; margin-top:15px; color:#94a3b8; text-decoration:none; font-size: 0.9rem;">
  Annuler et retourner à la liste
</a>

    </form>
</div>

</body>
</html>