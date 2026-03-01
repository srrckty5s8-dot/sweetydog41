<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier <?= htmlspecialchars($animal['nom_animal']) ?> - SweetyDog</title>
    <link rel="stylesheet" href="/assets/style.css">

    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; color: #333; }
        .form-edit { max-width: 600px; margin: 40px auto; background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        h2, h3 { color: var(--vert-fonce); margin-top: 0; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #555; font-size: 0.9rem; }
        input, select { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; background: #f8fafc; font-size: 1rem; }
        .btn-save { background: var(--vert-fonce); color: white; border: none; padding: 15px; width: 100%; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 25px; transition: 0.3s; }
        .btn-save:hover { background: #1b4332; transform: translateY(-2px); }
        .radio-group { margin-top: 10px; background: #f9f9f9; padding: 15px; border-radius: 10px; border: 1px solid #eee; }

        @media (max-width: 600px) {
            body { padding: 10px !important; }
            .form-edit { margin: 15px auto; padding: 20px !important; }
            h2 { font-size: 1.1rem; }
            input, select { font-size: 0.95rem; }
        }
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
            <select name="espece" id="select-espece">
                <option value="Chien" <?= ($animal['espece'] ?? '') == 'Chien' ? 'selected' : '' ?>>Chien</option>
                <option value="Chat" <?= ($animal['espece'] ?? '') == 'Chat' ? 'selected' : '' ?>>Chat</option>
                <option value="Lapin" <?= ($animal['espece'] ?? '') == 'Lapin' ? 'selected' : '' ?>>Lapin</option>
                <option value="Autre" <?= ($animal['espece'] ?? '') == 'Autre' ? 'selected' : '' ?>>Autre</option>
            </select>

            <label for="race-choice">Race de l'animal</label>
            <input list="races" name="race" id="race-choice" value="<?= htmlspecialchars($animal['race'] ?? '') ?>" placeholder="Tapez pour chercher...">
            <datalist id="races"></datalist>

            <label>Date de naissance</label>
            <?php
                $dnAffichage = '';
                if (!empty($animal['date_naissance'])) {
                    $parts = explode('-', $animal['date_naissance']);
                    if (count($parts) === 3) $dnAffichage = $parts[2] . '/' . $parts[1] . '/' . $parts[0];
                }
            ?>
            <input type="text" id="date_naissance_display" placeholder="jj/mm/aaaa" maxlength="10" autocomplete="off" inputmode="numeric" value="<?= htmlspecialchars($dnAffichage) ?>">
            <input type="hidden" name="date_naissance" id="date_naissance_hidden" value="<?= htmlspecialchars($animal['date_naissance'] ?? '') ?>">

            <label>Poids (kg)</label>
            <input type="number" step="0.1" name="poids" value="<?= htmlspecialchars($animal['poids'] ?? '') ?>" placeholder="Ex: 12.5">

            <p style="margin-top: 15px; font-weight: bold; color: #555;">Sexe de l'animal</p>
            <div class="radio-group">
                <input type="radio" name="sexe" value="M" id="male" style="width: auto;" <?= (($animal['sexe'] ?? '') === 'M') ? 'checked' : '' ?>>
                <label for="male" style="display: inline; margin-right: 20px; font-weight: normal;">♂ Mâle</label>

                <input type="radio" name="sexe" value="F" id="femelle" style="width: auto;" <?= (($animal['sexe'] ?? '') === 'F') ? 'checked' : '' ?>>
                <label for="femelle" style="display: inline; font-weight: normal;">♀ Femelle</label>
            </div>

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

    <!-- Bouton Supprimer (formulaire séparé) -->
    <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #fee2e2;">
        <form action="<?= route('animals.delete', ['id' => $animal['id_animal']]) ?>" method="POST"
              onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer <?= htmlspecialchars(addslashes($animal['nom_animal'])) ?> ?\n\nCette action est irréversible.');">
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
                🗑️ Supprimer cet animal
            </button>
        </form>
    </div>
</div>

<script>
var racesParEspece = {
    Chien: [
        "Affenpinscher","Airedale Terrier","Akita Americain","Akita Inu","Alaskan Malamute",
        "American Bully","American Staffordshire Terrier","Barbet","Basenji","Basset Artesien Normand",
        "Basset Hound","Beagle","Beauceron","Bedlington Terrier","Berger Allemand",
        "Berger Americain Miniature","Berger Australien","Berger Belge Groenendael","Berger Belge Laekenois","Berger Belge Malinois",
        "Berger Belge Tervueren","Berger Blanc Suisse","Berger de Brie","Berger de Picardie","Berger des Pyrenees",
        "Bichon Bolognais","Bichon Frise","Bichon Havanais","Bichon Maltais","Bobtail",
        "Border Collie","Border Terrier","Bouledogue Americain","Bouledogue Anglais","Bouledogue Francais",
        "Bouvier Australien","Bouvier Bernois","Bouvier des Flandres","Boxer","Braque Allemand",
        "Braque de Weimar","Braque Francais","Briquet Griffon Vendeen","Bull Terrier","Bullmastiff",
        "Cairn Terrier","Caniche","Cane Corso","Carlin","Cavalier King Charles",
        "Chien Chinois a Crete","Chien d'Eau Espagnol","Chien de Montagne des Pyrenees","Chien de Saint-Hubert","Chihuahua",
        "Chow-Chow","Cocker Americain","Cocker Anglais","Colley","Corgi",
        "Coton de Tulear","Dalmatien","Dobermann","Dogue Allemand","Dogue Argentin",
        "Dogue de Bordeaux","Epagneul Breton","Epagneul Francais","Epagneul Nain Continental","Eurasier",
        "Fox Terrier","Galgo Espagnol","Golden Retriever","Grand Bouvier Suisse","Grand Caniche",
        "Grand Griffon Vendeen","Griffon Belge","Griffon Bruxellois","Griffon Korthals","Hovawart",
        "Husky Siberien","Irish Wolfhound","Jack Russell Terrier","Kai","Keeshond",
        "Kelpie Australien","Komondor","Kooikerhondje","Labrador","Lagotto Romagnolo",
        "Landseer","Leonberg","Levrier Afghan","Levrier Irlandais","Levrier Italien",
        "Lhassa Apso","Malinois","Mastiff","Mudi","Nova Scotia Duck Tolling Retriever",
        "Old English Bulldog","Otterhound","Papillon","Parson Russell Terrier","Pekinois",
        "Petit Basset Griffon Vendeen","Petit Brabancon","Petit Chien Lion","Petit Spitz","Pinscher Allemand",
        "Pinscher Nain","Pointer","Pointer Anglais","Pudelpointer","Puli",
        "Rottweiler","Saint-Bernard","Samoyede","Schipperke","Schnauzer Geant",
        "Schnauzer Moyen","Schnauzer Nain","Setter Anglais","Setter Gordon","Setter Irlandais",
        "Shar Pei","Shetland Sheepdog","Shiba Inu","Shih Tzu","Spitz Allemand",
        "Spitz Japonais","Spitz Nain","Spitz Pomeranian","Springer Spaniel Anglais","Staffie","Teckel",
        "Terre-Neuve","Tosa Inu","Welsh Corgi Cardigan","Welsh Corgi Pembroke","Westie",
        "Whippet","Yorkshire Terrier"
    ],
    Chat: [
        "Abyssin","American Curl","American Shorthair","Bengal","Birman",
        "Bleu Russe","Bombay","British Longhair","British Shorthair","Burmese",
        "Chartreux","Cornish Rex","Devon Rex","Européen","Exotic Shorthair",
        "Havana Brown","Highland Fold","Khao Manee","Korat","LaPerm",
        "Maine Coon","Mau Égyptien","Munchkin","Norvégien","Ocicat",
        "Oriental","Persan","Peterbald","Ragdoll","Sacré de Birmanie",
        "Savannah","Scottish Fold","Selkirk Rex","Siamois","Sibérien",
        "Singapura","Somali","Sphynx","Thaï","Tonkinois",
        "Toyger","Turc de Van","Turkish Angora"
    ],
    Lapin: [
        "Angora","Bélier Français","Bélier Hollandais","Bélier Nain",
        "Blanc de Hotot","Californien","Chinchilla","Fauve de Bourgogne",
        "Géant des Flandres","Géant Papillon","Hermine","Hollandais",
        "Lionhead","Mini Lop","Mini Rex","Nain de couleur",
        "Néo-Zélandais","Papillon","Rex","Satin","Teddy"
    ],
    Autre: []
};

var raceActuelle = <?= json_encode($animal['race'] ?? '') ?>;

function updateRaces(garderValeur) {
    var espece = document.getElementById('select-espece').value;
    var datalist = document.getElementById('races');
    var input = document.getElementById('race-choice');
    datalist.innerHTML = '';
    if (!garderValeur) input.value = '';
    var races = racesParEspece[espece] || [];
    races.forEach(function(r) {
        var opt = document.createElement('option');
        opt.value = r;
        datalist.appendChild(opt);
    });
}

document.getElementById('select-espece').addEventListener('change', function() {
    updateRaces(false);
});
updateRaces(true);

// === Date de naissance : saisie libre jj/mm/aaaa ===
(function() {
    var display = document.getElementById('date_naissance_display');
    var hidden = document.getElementById('date_naissance_hidden');
    if (!display || !hidden) return;

    display.addEventListener('input', function() {
        var v = display.value.replace(/[^\d]/g, '');
        if (v.length > 8) v = v.substring(0, 8);
        var formatted = '';
        if (v.length > 4) {
            formatted = v.substring(0, 2) + '/' + v.substring(2, 4) + '/' + v.substring(4);
        } else if (v.length > 2) {
            formatted = v.substring(0, 2) + '/' + v.substring(2);
        } else {
            formatted = v;
        }
        display.value = formatted;

        if (v.length === 8) {
            var jour = v.substring(0, 2);
            var mois = v.substring(2, 4);
            var annee = v.substring(4, 8);
            var j = parseInt(jour), m = parseInt(mois), a = parseInt(annee);
            if (j >= 1 && j <= 31 && m >= 1 && m <= 12 && a >= 1900 && a <= new Date().getFullYear()) {
                hidden.value = annee + '-' + mois + '-' + jour;
                display.style.borderColor = '#2e7d32';
            } else {
                hidden.value = '';
                display.style.borderColor = '#e63946';
            }
        } else {
            hidden.value = '';
            display.style.borderColor = '';
        }
    });
})();
</script>
</body>
</html>
