<?php
/**
 * ============================================================
 * CONTRÔLEUR DES CLIENTS
 * ============================================================
 * 
 * Gère toutes les opérations CRUD (Create, Read, Update, Delete) 
 * concernant les clients (propriétaires) et leurs animaux.
 * 
 * Fonctionnalités :
 * - Lister les clients avec recherche
 * - Créer un client (propriétaire) + animal
 * - Modifier un client
 * - Supprimer un client
 * - Utiliser un propriétaire existant ou en créer un nouveau
 * 
 * Base de données :
 * - Table Proprietaires : informations du client
 * - Table Animaux : informations des animaux du client
 * - Relation : 1 propriétaire → N animaux
 */
class ClientController extends Controller
{
    /**
     * Affiche la liste des clients (alias pour liste())
     * Route : GET /clients
     */
    public function index()
    {
        $this->liste();
    }

    /**
     * Affiche le dashboard avec la liste de tous les clients et leurs animaux
     * 
     * Fonctionnalités :
     * - Affiche tous les clients avec leurs animaux
     * - Support de la recherche par nom
     * - Affiche les rendez-vous du jour
     * - Vérifie l'authentification
     * 
     * Route : GET /clients
     */
    public function liste()
    {
        // Vérifier que l'utilisateur est connecté
        $this->requireLogin();
        
        // Configurer le fuseau horaire pour les dates
        date_default_timezone_set('Europe/Paris');

        // Charger la base de données
        require_once __DIR__ . '/../../config/db.php';

        // Récupérer le terme de recherche depuis GET
        // Utilisateur peut filtrer les clients par son nom
        $search = $_GET['search'] ?? '';

        try {
            // Récupérer tous les clients avec leurs animaux (filtrés par recherche)
            // Client::getAllWithAnimaux() retourne un array de clients avec leurs animaux
            $clients = Client::getAllWithAnimaux($search);
            
            // Récupérer les rendez-vous d'aujourd'hui pour affichage
            $rdv_du_jour = RendezVous::getToday();
        } catch (Exception $e) {
            // Erreur de base de données
            die("Erreur base de données");
        }

        // Afficher la vue avec les données
        $this->view('liste_clients_view', compact('clients', 'rdv_du_jour'));
    }

    /**
     * Affiche le formulaire de création d'un client
     * 
     * Le formulaire permet :
     * - De sélectionner un propriétaire existant OU d'en créer un nouveau
     * - De remplir les informations du propriétaire
     * - D'ajouter un animal
     * 
     * Route : GET /clients/new
     */
    public function create()
    {
        // Vérifier l'authentification
        $this->requireLogin();

        // Récupérer la liste de tous les propriétaires existants
        // Cela permet à l'utilisateur de sélectionner un propriétaire existant
        $tous_les_proprios = Client::getAllProprietaires();
        
        // Afficher le formulaire
        $this->view('ajouter_client_view', compact('tous_les_proprios'));
    }

    /**
     * Sauvegarde un nouveau client et son animal en base de données
     * 
     * Logique :
     * 1. Vérifier si on utilise un propriétaire existant ou en créer un nouveau
     * 2. Récupérer/créer les données du propriétaire
     * 3. Ajouter l'animal au propriétaire
     * 4. Rediriger vers la liste des clients
     * 
     * Route : POST /clients
     */
    public function store()
    {
        // Vérifier l'authentification
        $this->requireLogin();

        // 1) PROPRIÉTAIRE : Existant ou nouveau ?
        $idProprioExistant = $_POST['id_proprietaire_existant'] ?? 'nouveau';

        if ($idProprioExistant !== 'nouveau') {
            // Utiliser un propriétaire existant
            $idProprio = (int)$idProprioExistant;
        } else {
            // Créer un nouveau propriétaire
            // Récupérer les données du formulaire
            $prenom      = trim($_POST['prenom'] ?? '');
            $nom         = trim($_POST['nom'] ?? '');
            $tel         = trim($_POST['tel'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $rue         = trim($_POST['rue'] ?? '');
            $code_postal = trim($_POST['code_postal'] ?? '');
            $ville       = trim($_POST['ville'] ?? '');
            $adresse     = implode("\n", array_filter([$rue, trim($code_postal . ' ' . $ville)]));

            // Validation : nom et prénom obligatoires
            if ($nom === '' || $prenom === '') {
                redirect('clients.create');
                exit;
            }

            // Créer le propriétaire en base et récupérer son ID
            $idProprio = Client::createProprietaire([
                'nom' => $nom,
                'prenom' => $prenom,
                'telephone' => $tel,
                'email' => $email,
                'adresse' => $adresse,
            ]);
        }

        // 2) ANIMAL : Récupérer les données du formulaire
        $nomAnimal      = trim($_POST['nom_chien'] ?? '');
        $espece         = trim($_POST['espece'] ?? '');
        $race           = trim($_POST['race'] ?? '');
        $poids          = $_POST['poids'] !== '' ? (float)$_POST['poids'] : null;
        $steril         = isset($_POST['steril']) ? (int)$_POST['steril'] : 0;
        $sexe           = trim($_POST['sexe'] ?? '');
        $date_naissance = trim($_POST['date_naissance'] ?? '');

        // Validation : le nom de l'animal est obligatoire
        if ($nomAnimal === '') {
            redirect('clients.create');
            exit;
        }

        // Créer l'animal en base de données
        Client::createAnimal([
            'id_proprietaire' => $idProprio,
            'nom_animal' => $nomAnimal,
            'espece' => $espece,
            'race' => $race,
            'poids' => $poids,
            'sterilise' => $steril,
            'sexe' => $sexe ?: null,
            'date_naissance' => $date_naissance ?: null,
        ]);

        // Rediriger vers la liste des clients
        redirect('clients.index');
        exit;
    }

    /**
     * Affiche le formulaire de modification d'un client
     * 
     * Route : GET /clients/{id}/edit
     * 
     * @param int $id - ID du propriétaire à modifier
     */
    public function edit($id = 0)
    {
        // Vérifier l'authentification
        $this->requireLogin();

        // Récupérer l'ID depuis le paramètre de route
        $id = (int)$id;
        
        // Validation : ID requis et valide
        if ($id <= 0) {
            // ID invalide, rediriger vers la liste
            redirect('clients.index');
            exit;
        }

        // Récupérer les données du propriétaire
        $proprio = Client::findProprietaire($id);

        // Vérifier que le propriétaire existe
        if (!$proprio) {
            redirect('clients.index');
            exit;
        }

        // Décomposer l'adresse en rue / code postal / ville
        $lignes = explode("\n", $proprio['adresse'] ?? '');
        $proprio['rue'] = trim($lignes[0] ?? '');
        if (!empty($lignes[1]) && preg_match('/^(\d{5})\s*(.*)$/', trim($lignes[1]), $m)) {
            $proprio['code_postal'] = $m[1];
            $proprio['ville'] = $m[2];
        } else {
            $proprio['code_postal'] = '';
            $proprio['ville'] = trim($lignes[1] ?? '');
        }

        // Afficher le formulaire de modification
        $this->view('modifier_client_view', compact('proprio'));
    }

    /**
     * Sauvegarde les modifications d'un client
     * 
     * Route : POST /clients/{id}
     * 
     * @param int $id - ID du propriétaire à modifier
     */
    public function update($id = 0)
    {
        // Vérifier l'authentification
        $this->requireLogin();

        // Récupérer l'ID du propriétaire depuis le paramètre de route
        $id = (int)$id;
        
        // Validation : ID requis et valide
        if ($id <= 0) {
            redirect('clients.index');
            exit;
        }

        // Récupérer les données du formulaire
        $nom         = trim($_POST['nom'] ?? '');
        $prenom      = trim($_POST['prenom'] ?? '');
        $telephone   = trim($_POST['tel'] ?? $_POST['telephone'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $rue         = trim($_POST['rue'] ?? '');
        $code_postal = trim($_POST['code_postal'] ?? '');
        $ville       = trim($_POST['ville'] ?? '');
        $adresse     = implode("\n", array_filter([$rue, trim($code_postal . ' ' . $ville)]));

        // Validation : nom et prénom obligatoires
        if ($nom === '' || $prenom === '') {
            redirect('clients.edit', ['id' => $id]);
            exit;
        }

        // Mettre à jour le propriétaire en base de données
        Client::updateProprietaire($id, [
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'email' => $email,
            'adresse' => $adresse,
        ]);

        // Afficher un message de succès et rediriger vers la liste
        flashMessage('success', "✅ Client modifié.");
        redirect('clients.index');
        exit;
    }

    /**
     * Supprime un client
     * 
     * Route : POST /clients/{id}/delete
     * 
     * @param int $id - ID du propriétaire à supprimer
     */
    public function delete($id = 0)
    {
        // Vérifier l'authentification
        $this->requireLogin();

        // Récupérer l'ID du propriétaire depuis le paramètre de route
        $id = (int)$id;

        // Validation : ID requis et valide
        if ($id <= 0) {
            redirect('clients.index', [], ['error' => 'delete_failed']);
            exit;
        }

        // Supprimer le propriétaire et toutes ses données liées
        $deleted = Client::deleteProprietaire($id);

        if ($deleted) {
            redirect('clients.index', [], ['success' => 'deleted']);
            exit;
        }

        redirect('clients.index', [], ['error' => 'delete_failed']);
        exit;
    }
}
