<?php
/**
 * ============================================================
 * CONFIGURATION DES ROUTES DE L'APPLICATION
 * ============================================================
 * 
 * Ce fichier définit TOUTES les routes de l'application.
 * Chaque route établit une correspondance entre :
 * - Une URL (pattern)
 * - Une méthode HTTP (GET, POST, etc.)
 * - Un contrôleur et une action
 * - Un nom unique (pour générer les URLs avec route())
 * 
 * FORMAT DE CHAQUE ROUTE :
 * [
 *     'name'    => 'route.name',                    // Identifiant unique (ex: 'clients.edit')
 *     'method'  => 'GET|POST|PUT|DELETE',           // Méthode(s) HTTP supportée(s)
 *     'action'  => 'ControllerName@methodName',      // Contrôleur et action à exécuter
 *     'pattern' => '/path/{id}/action'               // Pattern de l'URL avec paramètres
 * ]
 * 
 * SYNTAXE DES PATTERNS :
 * - Chemin static : '/clients'                   → /clients uniquement
 * - Chemin avec ID : '/clients/{id}/edit'        → /clients/1/edit, /clients/99/edit, etc.
 * - Plusieurs params : '/animals/{id}/owner/{owner_id}'
 * 
 * UTILISATION DANS LES VUES :
 * 
 * Générer une URL :
 * <a href="<?php echo route('clients.edit', ['id' => 5]); ?>">Éditer</a>
 * 
 * Vérifier la page actuelle :
 * <?php if (isCurrentRoute('clients.edit')) : ?>
 *     <span>Vous êtes sur la page de modification</span>
 * <?php endif; ?>
 * 
 * ============================================================
 */

return [
    // ========== AUTHENTIFICATION ==========
    /**
     * Formulaire de connexion
     * GET : affiche le formulaire
     * POST : traite la connexion
     */
    ['name' => 'login', 'method' => 'GET|POST', 'action' => 'AuthController@login', 'pattern' => '/auth/login'],
    
    /**
     * Déconnexion
     * Détruit la session et redirige vers le login
     */
    ['name' => 'logout', 'method' => 'GET', 'action' => 'AuthController@logout', 'pattern' => '/auth/logout'],
    
    /**
     * Page d'accueil / Route par défaut
     * Redirige vers le dashboard ou le login selon l'authentification
     */
    ['name' => 'home', 'method' => 'GET', 'action' => 'AuthController@redirectHome', 'pattern' => '/'],

    // ========== GESTION DES CLIENTS ==========
    /**
     * Liste de tous les clients
     * Affiche le dashboard avec la liste des clients et animaux
     */
    ['name' => 'clients.index', 'method' => 'GET', 'action' => 'ClientController@index', 'pattern' => '/clients'],
    
    /**
     * Formulaire de création de client
     * Affiche le formulaire vierge pour ajouter un nouveau client
     */
    ['name' => 'clients.create', 'method' => 'GET', 'action' => 'ClientController@create', 'pattern' => '/clients/new'],
    
    /**
     * Sauvegarde d'un nouveau client
     * Traite le formulaire POST et insère en base de données
     */
    ['name' => 'clients.store', 'method' => 'POST', 'action' => 'ClientController@store', 'pattern' => '/clients'],
    
    /**
     * Formulaire de modification de client
     * {id} = ID du client à modifier
     */
    ['name' => 'clients.edit', 'method' => 'GET', 'action' => 'ClientController@edit', 'pattern' => '/clients/{id}/edit'],
    
    /**
     * Mise à jour d'un client
     * {id} = ID du client à modifier
     * Traite le formulaire POST
     */
    ['name' => 'clients.update', 'method' => 'POST', 'action' => 'ClientController@update', 'pattern' => '/clients/{id}'],
    
    /**
     * Suppression d'un client
     * {id} = ID du client à supprimer
     */
    ['name' => 'clients.delete', 'method' => 'POST', 'action' => 'ClientController@delete', 'pattern' => '/clients/{id}/delete'],

    // ========== GESTION DES ANIMAUX ==========
    /**
     * Formulaire de modification d'un animal
     * {id} = ID de l'animal (id_animal en base de données)
     */
    ['name' => 'animals.edit', 'method' => 'GET', 'action' => 'AnimalController@edit', 'pattern' => '/animals/{id}/edit'],
    
    /**
     * Mise à jour d'un animal
     * {id} = ID de l'animal
     * Traite le formulaire POST
     */
    ['name' => 'animals.update', 'method' => 'POST', 'action' => 'AnimalController@update', 'pattern' => '/animals/{id}'],
    
    /**
     * Suivi des toilettages d'un animal
     * {id} = ID de l'animal
     * Affiche l'historique des soins pour cet animal
     */
    ['name' => 'animals.tracking', 'method' => 'GET', 'action' => 'AnimalController@tracking', 'pattern' => '/animals/{id}/tracking'],

    // ========== GESTION DES RENDEZ-VOUS ==========
    /**
     * Liste des rendez-vous
     * Affiche le calendrier avec tous les rendez-vous
     */
    ['name' => 'appointments.index', 'method' => 'GET', 'action' => 'AppointmentController@index', 'pattern' => '/appointments'],
    
    /**
     * Création d'un rendez-vous
     * Traite le formulaire POST pour ajouter un rendez-vous
     */
    ['name' => 'appointments.create', 'method' => 'POST', 'action' => 'AppointmentController@create', 'pattern' => '/appointments'],
    
    /**
     * Suppression d'un rendez-vous
     * {id} = ID du rendez-vous à supprimer
     */
    ['name' => 'appointments.delete', 'method' => 'POST', 'action' => 'AppointmentController@delete', 'pattern' => '/appointments/{id}/delete'],

    // ========== PARAMÈTRES ET CONFIGURATION ==========
    /**
     * Page des paramètres/configuration
     * Permet de configurer les paramètres de l'application
     */
    ['name' => 'settings.index', 'method' => 'GET|POST', 'action' => 'SettingsController@index', 'pattern' => '/settings'],

    ['name' => 'invoices.generate', 'method' => 'GET', 'action' => 'InvoiceController@generate', 'pattern' => '/invoices/{id}/generate'],


    //prestation
    ['name' => 'prestations.store','method' => 'POST','action' => 'PrestationController@store','pattern' => '/animals/{id}/prestations'],

];
