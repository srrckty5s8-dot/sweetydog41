<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SweetyDog - Nouveau Client</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        input, select, textarea { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        h3 { border-bottom: 2px solid var(--vert-moyen); padding-bottom: 5px; margin-top: 25px; color: #444; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr !important; gap: 5px; }
            .container { padding: 20px 15px !important; }
            h2 { font-size: 1.1rem; }
            h3 { font-size: 0.95rem; }
            .search-container { padding: 12px; }
        }
        
        /* Style pour la zone de recherche */
        .search-container { background: #f0fdf4; padding: 15px; border-radius: 10px; border: 2px solid var(--vert-moyen); margin-bottom: 20px; }
        .badge-client { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-top: 10px; font-weight: bold; display: none; border: 1px solid #166534; }
        .hidden { display: none; }
        .btn-retour-client {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: var(--vert-fonce);
            border: 2px solid var(--vert-fonce);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-retour-client:hover {
            background-color: var(--vert-fonce);
            color: white;
        }
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
            <label>Rue</label>
            <input type="text" name="rue" placeholder="Ex: 12 rue des Lilas">
            <div class="form-row">
                <div>
                    <label>Code Postal</label>
                    <input type="text" name="code_postal" placeholder="Ex: 41200" maxlength="5">
                </div>
                <div>
                    <label>Ville</label>
                    <input type="text" name="ville" list="villes-romorantin-30km" placeholder="Ex: Romorantin" autocomplete="off">
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
        </div>

        <h3>🐾 L'Animal</h3>
        <div class="form-group">
            <label>Nom de l'animal</label>
            <input type="text" name="nom_chien" placeholder="Ex: Rex" required>
            
            <label>Espèce</label>
            <select name="espece" id="select-espece">
                <option value="Chien">Chien</option>
                <option value="Chat">Chat</option>
                <option value="Lapin">Lapin</option>
                <option value="Autre">Autre</option>
            </select>

            <label for="race-choice">Race de l'animal</label>
            <input list="races" name="race" id="race-choice" placeholder="Tapez pour chercher une race...">
            <datalist id="races"></datalist>

            <label>Date de naissance</label>
            <input type="text" id="date_naissance_display" placeholder="jj/mm/aaaa" maxlength="10" autocomplete="off" inputmode="numeric">
            <input type="hidden" name="date_naissance" id="date_naissance_hidden">

            <label>Poids (kg)</label>
            <input type="number" step="0.1" name="poids" placeholder="Ex: 12.5">

            <p><strong>Sexe de l'animal</strong></p>
            <div style="margin-bottom: 20px; background: #f9f9f9; padding: 10px; border-radius: 8px;">
                <input type="radio" name="sexe" value="M" id="male" style="width: auto;">
                <label for="male" style="margin-right: 20px; font-weight: normal;">♂ Mâle</label>

                <input type="radio" name="sexe" value="F" id="femelle" style="width: auto;">
                <label for="femelle" style="font-weight: normal;">♀ Femelle</label>
            </div>

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

    <a href="<?= route('clients.index') ?>" class="btn-retour-client">← Retour liste</a>
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

// Races par espèce
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

function updateRaces() {
    var espece = document.getElementById('select-espece').value;
    var datalist = document.getElementById('races');
    var input = document.getElementById('race-choice');
    datalist.innerHTML = '';
    input.value = '';
    var races = racesParEspece[espece] || [];
    races.forEach(function(r) {
        var opt = document.createElement('option');
        opt.value = r;
        datalist.appendChild(opt);
    });
}

document.getElementById('select-espece').addEventListener('change', updateRaces);
updateRaces();

// Majuscule automatique sur la première lettre de certains champs
(function() {
    function capitalizeFirstLetter(value) {
        return value.replace(/^(\s*)([a-zà-öø-ÿ])/u, function(_, spaces, firstLetter) {
            return spaces + firstLetter.toUpperCase();
        });
    }

    function bindAutoCapitalize(selector) {
        var input = document.querySelector(selector);
        if (!input) return;

        input.addEventListener('input', function() {
            var current = input.value;
            var next = capitalizeFirstLetter(current);

            if (next !== current) {
                var start = input.selectionStart;
                var end = input.selectionEnd;
                input.value = next;
                if (start !== null && end !== null) {
                    input.setSelectionRange(start, end);
                }
            }
        });
    }

    bindAutoCapitalize('#input-prenom');
    bindAutoCapitalize('#input-nom');
    bindAutoCapitalize('input[name="nom_chien"]');
    bindAutoCapitalize('input[name="ville"]');
})();

// === Date de naissance : saisie libre jj/mm/aaaa ===
(function() {
    var display = document.getElementById('date_naissance_display');
    var hidden = document.getElementById('date_naissance_hidden');
    if (!display || !hidden) return;

    display.addEventListener('input', function(e) {
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

        // Convertir en YYYY-MM-DD pour le hidden
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
