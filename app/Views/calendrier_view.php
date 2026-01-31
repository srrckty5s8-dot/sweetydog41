<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Agenda | SweetyDog</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(url('assets/style.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <style>
        #calendar { max-width: 1100px; margin: 20px auto; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        #modalRDV { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); }
        .modal-content { background:white; max-width:450px; margin:5% auto; padding:30px; border-radius:15px; position:relative; }
        
        .search-input { width:100%; padding:12px; margin:10px 0; border:1px solid #ddd; border-radius:6px; box-sizing: border-box; font-size: 1rem; }
        label { font-weight: bold; color: #555; font-size: 0.9rem; display: block; margin-top: 10px; }
        .btn-confirm { width:100%; background:var(--vert-fonce); color:white; padding:15px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; margin-top:20px; font-size:1rem; transition: 0.3s; }
        .btn-confirm:hover { background: #1b4332; }
    </style>
</head>
<body>

<div class="container-large">
    <div class="header-flex" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <h2>📅 Agenda SweetyDog</h2>
        <a href="<?= htmlspecialchars(route('clients.index')) ?>" class="btn-edit" style="text-decoration:none;">← Retour</a>
    </div>

    <div id="calendar"></div>
</div>

<div id="modalRDV">
    <div class="modal-content">
        <span onclick="document.getElementById('modalRDV').style.display='none'" style="position:absolute; right:20px; top:15px; cursor:pointer; font-size:24px;">&times;</span>
        <h3>Nouveau Rendez-vous</h3>
        
        <form action="<?= htmlspecialchars(route('appointments.create')) ?>" method="POST" id="formRDV">
            
            <label>Animal (Recherche par nom ou propriétaire)</label>
            <input list="liste_animaux_dl" id="animal_input" class="search-input" placeholder="Tapez le nom du chien..." required autocomplete="off">
            
            <input type="hidden" name="id_animal" id="id_animal_hidden">

            <datalist id="liste_animaux_dl">
                <?php foreach($liste_animaux as $a): ?>
                    <?php 
                        // On crée une ligne unique : "Bobby (Proprio: Jean DUPONT - Caniche)"
                        $info_complet = htmlspecialchars($a['nom_animal']) . " (Proprio: " . htmlspecialchars($a['prenom_client'] . " " . $a['nom_client']) . " - " . htmlspecialchars($a['race']) . ")"; 
                    ?>
                    <option data-id="<?php echo $a['id_animal']; ?>" value="<?php echo $info_complet; ?>">
                <?php endforeach; ?>
            </datalist>

            <label>Type de soin</label>
            <input type="text" name="titre" placeholder="Ex: Toilettage complet + Coupe" required class="search-input">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label>Début</label>
                    <input type="datetime-local" name="date_debut" id="input_debut" required class="search-input">
                </div>
                <div>
                    <label>Fin prévue</label>
                    <input type="datetime-local" name="date_fin" id="input_fin" required class="search-input">
                </div>
            </div>

            <button type="submit" class="btn-confirm">CONFIRMER LE RENDEZ-VOUS</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'fr',
        slotMinTime: '08:00:00',
        slotMaxTime: '19:00:00',
        selectable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?php echo json_encode($events); ?>,
        
        select: function(info) {
            document.getElementById('modalRDV').style.display = 'block';
            document.getElementById('input_debut').value = info.startStr.slice(0, 16);
            document.getElementById('input_fin').value = info.endStr.slice(0, 16);
            
            // Reset de la recherche
            document.getElementById('animal_input').value = "";
            document.getElementById('id_animal_hidden').value = "";
        },

        eventClick: function(info) {
            if (confirm("Voulez-vous supprimer le rendez-vous de " + info.event.title + " ?")) {
                deleteAppointment(info.event.id);
            }
        }
    });
    
    calendar.render();

    // GESTION DU CHOIX DANS LE DATALIST
    document.getElementById('animal_input').addEventListener('input', function(e) {
        var input = e.target;
        var listId = input.getAttribute('list');
        var options = document.getElementById(listId).options;
        var hiddenInput = document.getElementById('id_animal_hidden');
        
        for (var i = 0; i < options.length; i++) {
            if (options[i].value === input.value) {
                hiddenInput.value = options[i].getAttribute('data-id');
                return;
            }
        }
        hiddenInput.value = ""; // Vide si rien ne correspond
    });

    // Suppression via POST (MVC)
    function deleteAppointment(id) {
        var templateUrl = <?= json_encode(route('appointments.delete', ['id' => '__ID__'])) ?>;
        var deleteUrl = templateUrl.replace('__ID__', encodeURIComponent(id));

        fetch(deleteUrl, { method: 'POST' })
            .then(function() { window.location.reload(); })
            .catch(function() {
                alert("Erreur lors de la suppression du rendez-vous.");
            });
    }

    // Blocage si l'animal n'est pas valide
    document.getElementById('formRDV').addEventListener('submit', function(e) {
        if (!document.getElementById('id_animal_hidden').value) {
            e.preventDefault();
            alert("Erreur : Vous devez sélectionner un animal dans la liste proposée.");
        }
    });
});
</script>

</body>
</html>
