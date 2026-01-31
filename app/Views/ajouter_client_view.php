<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SweetyDog - Nouveau Client</title>
    <link rel="stylesheet" href="/sweetydog/assets/style.css">
    <style>
        input, select, textarea { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        h3 { border-bottom: 2px solid var(--vert-moyen); padding-bottom: 5px; margin-top: 25px; color: #444; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        /* Style pour la zone de recherche */
        .search-container { background: #f0fdf4; padding: 15px; border-radius: 10px; border: 2px solid var(--vert-moyen); margin-bottom: 20px; }
        .badge-client { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-top: 10px; font-weight: bold; display: none; border: 1px solid #166534; }
        .hidden { display: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>🐾 Inscription Compagnon</h2>

    <form action="<?= route('clients.store') ?>" method="POST">
        
        <h3>👤 Le Propriétaire</h3>
        
        <div class="search-container">
            <label>🔍 Chercher un client existant (tapez le nom) :</label>
            <input type="text" id="recherche_client" list="clients_list" placeholder="Ex: Dupont Jean..." autocomplete="off">
            <datalist id="clients_list">
                <?php foreach($tous_les_proprios as $p): ?>
                    <option value="<?= htmlspecialchars(strtoupper($p['nom']) . " " . $p['prenom']) ?> (ID:<?= $p['id_proprietaire'] ?>)">
                <?php endforeach; ?>
            </datalist>
            
            <input type="hidden" name="id_proprietaire_existant" id="id_proprio_cache" value="nouveau">
            
            <div id="badge-client" class="badge-client">
                ✅ Client existant sélectionné.
                <button type="button" onclick="annulerSelection()" style="float:right; background:none; border:none; color:#ef4444; cursor:pointer; font-weight:bold;">Annuler / Nouveau</button>
            </div>
        </div>

        <div id="zone-nouveau-proprio">
            <div class="form-row">
                <div>
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="input-prenom" placeholder="Ex: Jean">
                </div>
                <div>
                    <label>Nom</label>
                    <input type="text" name="nom" id="input-nom" placeholder="Ex: Dupont">
                </div>
            </div>
            <label>Téléphone</label>
            <input type="tel" name="tel" placeholder="06 00 00 00 00">
            <label>Adresse Email</label>
            <input type="email" name="email" placeholder="client@exemple.com">
            <label>Adresse Postale</label>
            <textarea name="adresse" rows="2" placeholder="Rue, Code Postal, Ville..."></textarea>
        </div>

        <h3>🐾 L'Animal</h3>
        <div class="form-group">
            <label>Nom de l'animal</label>
            <input type="text" name="nom_chien" placeholder="Ex: Rex" required>
            
            <label>Espèce</label>
            <select name="espece">
                <option value="Chien">Chien</option>
                <option value="Chat">Chat</option>
                <option value="Lapin">Lapin</option>
                <option value="Autre">Autre</option>
            </select>

            <label for="race-choice">Race de l'animal</label>
            <input list="races" name="race" id="race-choice" placeholder="Tapez pour chercher une race...">
            <datalist id="races">
                <option value="Bichon Frisé"><option value="Bichon Maltais"><option value="Berger Allemand"><option value="Berger Australien"><option value="Bouledogue Français"><option value="Caniche"><option value="Cavalier King Charles"><option value="Chihuahua"><option value="Cocker Spaniel"><option value="Golden Retriever"><option value="Labrador"><option value="Shih Tzu"><option value="Yorkshire Terrier">
            </datalist>

            <label>Poids (kg)</label>
            <input type="number" step="0.1" name="poids" placeholder="Ex: 12.5">

            <p><strong>L'animal est-il stérilisé ?</strong></p>
            <div style="margin-bottom: 20px; background: #f9f9f9; padding: 10px; border-radius: 8px;">
                <input type="radio" name="steril" value="1" id="oui" style="width: auto;">
                <label for="oui" style="margin-right: 20px; font-weight: normal;">Oui</label>
                
                <input type="radio" name="steril" value="0" id="non" checked style="width: auto;">
                <label for="non" style="font-weight: normal;">Non</label>
            </div>
        </div>

        <button type="submit" name="valider" style="width:100%; padding: 15px; background: var(--vert-fonce); color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
            ✨ ENREGISTRER DANS LA BASE
        </button>
    </form>
</div>

<script>
const rechercheInput = document.getElementById('recherche_client');
const idCache = document.getElementById('id_proprio_cache');
const zoneNouveau = document.getElementById('zone-nouveau-proprio');
const badge = document.getElementById('badge-client');

rechercheInput.addEventListener('input', function() {
    const val = this.value;
    // On cherche si la valeur contient "(ID:XX)"
    if (val.includes('(ID:')) {
        const parts = val.split('(ID:');
        const id = parts[1].replace(')', '');
        
        idCache.value = id;
        zoneNouveau.classList.add('hidden');
        badge.style.display = 'block';
        
        // On désactive le required pour les nouveaux champs
        document.getElementById('input-nom').required = false;
        document.getElementById('input-prenom').required = false;
    }
});

function annulerSelection() {
    idCache.value = 'nouveau';
    rechercheInput.value = '';
    zoneNouveau.classList.remove('hidden');
    badge.style.display = 'none';
    document.getElementById('input-nom').required = true;
    document.getElementById('input-prenom').required = true;
}
</script>

</body>
</html>