<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier client</title>
  <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
  
  <style>
    /* FIX : box-sizing pour éviter les débordements */
    * {
      box-sizing: border-box;
    }
    
    /* Style pour les champs sur 2 colonnes */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 25px;
    }
    
    .form-row > div {
      min-width: 0; /* Empêche le débordement */
    }
    
    .form-row label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--vert-fonce);
    }
    
    .form-row input {
      width: 100%;
    }
    
    /* Style pour les champs pleine largeur */
    .form-full {
      margin-bottom: 25px;
    }
    
    .form-full label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--vert-fonce);
    }
    
    /* Textarea */
    textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #d8e2dc;
      border-radius: 8px;
      background-color: #fff;
      font-family: inherit;
      transition: border-color 0.3s ease;
      resize: vertical;
      box-sizing: border-box;
    }
    
    textarea:focus {
      outline: none;
      border-color: var(--vert-moyen);
    }
    
    /* Override pour s'assurer que les inputs ne débordent pas */
    input[type="text"],
    input[type="email"],
    input[type="tel"] {
      box-sizing: border-box;
    }
    
    /* Bouton retour */
    .btn-back {
      display: inline-block;
      text-decoration: none;
      color: var(--vert-fonce);
      border: 2px solid var(--vert-fonce);
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
      transition: all 0.3s ease;
      margin-top: 20px;
    }
    
    .btn-back:hover {
      background-color: var(--vert-fonce);
      color: white;
    }

    @media (max-width: 600px) {
      .form-row { grid-template-columns: 1fr !important; gap: 10px; }
      .container { padding: 20px 15px !important; }
      h2 { font-size: 1.1rem; }
    }
  </style>
</head>
<body>
<div class="container">
  <h2>✏️ Modifier le client</h2>

  <form action="<?= route('clients.update', ['id' => $proprio['id_proprietaire']]) ?>" method="POST">
    <input type="hidden" name="id_proprietaire" value="<?= (int)$proprio['id_proprietaire'] ?>">

    <!-- Prénom et Nom sur 2 colonnes -->
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

    <!-- Téléphone et Email sur 2 colonnes -->
    <div class="form-row">
      <div>
        <label>Téléphone</label>
        <input type="tel" name="tel" value="<?= htmlspecialchars($proprio['telephone'] ?? '') ?>">
      </div>
      <div>
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($proprio['email'] ?? '') ?>">
      </div>
    </div>

    <!-- Adresse décomposée -->
    <div class="form-full">
      <label>Rue</label>
      <input type="text" name="rue" value="<?= htmlspecialchars($proprio['rue'] ?? '') ?>" placeholder="Ex: 12 rue des Lilas">
    </div>
    <div class="form-row">
      <div>
        <label>Code Postal</label>
        <input type="text" name="code_postal" value="<?= htmlspecialchars($proprio['code_postal'] ?? '') ?>" placeholder="Ex: 41200" maxlength="5">
      </div>
      <div>
        <label>Ville</label>
        <input type="text" name="ville" value="<?= htmlspecialchars($proprio['ville'] ?? '') ?>" list="villes-romorantin-30km" placeholder="Ex: Romorantin" autocomplete="off">
        <datalist id="villes-romorantin-30km">
          <option value="Romorantin-Lanthenay">
          <option value="Pruniers-en-Sologne">
          <option value="Villefranche-sur-Cher">
          <option value="Villeherviers">
          <option value="Gièvres">
          <option value="Châtres-sur-Cher">
          <option value="Billy">
          <option value="Mennetou-sur-Cher">
          <option value="Langon-sur-Cher">
          <option value="Selles-sur-Cher">
          <option value="Valençay">
          <option value="Chémery">
          <option value="Sassay">
          <option value="Le Controis-en-Sologne">
          <option value="Maray">
          <option value="Fontguenand">
          <option value="Poulaines">
          <option value="Luçay-le-Mâle">
          <option value="Thénioux">
          <option value="Gy-en-Sologne">
          <option value="Mur-de-Sologne">
          <option value="Soings-en-Sologne">
          <option value="Millancay">
          <option value="Veilleins">
          <option value="Vernou-en-Sologne">
          <option value="Loreux">
          <option value="La Ferté-Imbault">
          <option value="Salbris">
          <option value="Theillay">
          <option value="Orçay">
          <option value="Nouan-le-Fuzelier">
          <option value="Nançay">
          <option value="Saint-Julien-sur-Cher">
          <option value="Chabris">
          <option value="Meusnes">
          <option value="Noyers-sur-Cher">
          <option value="Couffy">
          <option value="Saint-Loup-sur-Cher">
        </datalist>
      </div>
    </div>

    <!-- Bouton Enregistrer -->
    <button type="submit" class="btn" style="width:100%;">
      ✅ Enregistrer
    </button>
  </form>

  <!-- Bouton Supprimer (formulaire séparé) -->
  <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #fee2e2;">
    <form action="<?= route('clients.delete', ['id' => $proprio['id_proprietaire']]) ?>" method="POST"
          onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer la fiche de <?= htmlspecialchars(addslashes(($proprio['prenom'] ?? '') . ' ' . ($proprio['nom'] ?? ''))) ?> ?\n\nTous les animaux, rendez-vous et prestations liés seront supprimés.\n\nCette action est irréversible.');">
      <button type="submit" style="
          background: #fee2e2;
          color: #dc2626;
          border: 2px solid #fecaca;
          padding: 12px 30px;
          border-radius: 10px;
          font-weight: bold;
          cursor: pointer;
          font-size: 0.95rem;
          transition: all 0.3s ease;
      "
      onmouseover="this.style.background='#dc2626'; this.style.color='white'; this.style.borderColor='#dc2626';"
      onmouseout="this.style.background='#fee2e2'; this.style.color='#dc2626'; this.style.borderColor='#fecaca';">
          🗑️ Supprimer ce client
      </button>
    </form>
  </div>

  <!-- Bouton Retour stylé -->
  <a href="<?= route('clients.index') ?>" class="btn-back">← Retour liste</a>
</div>
</body>
</html>
