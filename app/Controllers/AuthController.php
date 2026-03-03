<?php
/**
 * ============================================================
 * CONTRÔLEUR D'AUTHENTIFICATION (LOGIN/LOGOUT)
 * ============================================================
 * 
 * Gère :
 * - L'affichage du formulaire de connexion
 * - La vérification des identifiants
 * - Le hachage du mot de passe avec password_verify()
 * - La création de la session utilisateur
 * - La redirection selon l'état d'authentification
 * - La déconnexion
 * 
 * Sécurité :
 * - Utilise password_verify() pour vérifier les mots de passe (plus sûr que ==)
 * - Les identifiants sont vérifiés en base de données
 * - Les sessions PHP protègent contre les attaques CSRF
 */
class AuthController extends Controller
{
    /**
     * Redirige vers la page d'accueil appropriée
     * 
     * Logique :
     * - Si connecté → rediriger vers le dashboard (clients.index)
     * - Si pas connecté → afficher le formulaire de login
     * 
     * Cette route est assignée à la route '/' (racine de l'application)
     * Pour une meilleure UX : l'utilisateur est automatiquement redirigé
     */
    public function redirectHome()
    {
        // Vérifier si l'utilisateur a une session active
        if (!empty($_SESSION['admin_connecte'])) {
            // L'utilisateur est connecté, le rediriger vers le dashboard
            redirect('clients.index');
        }
        
        // L'utilisateur n'est pas connecté, afficher le formulaire de login
        $this->login();
    }

    /**
     * Affiche le formulaire de login ou traite la connexion
     * 
     * GET : affiche le formulaire vierge
     * POST : vérifie les identifiants et crée la session
     * 
     * Base de données :
     * Table : Utilisateurs
     * Colonnes : id_utilisateur, identifiant, mot_de_passe
     */
    public function login()
    {
        // Charger la configuration de la base de données
        require_once __DIR__ . '/../../config/db.php';

        // Variable pour afficher les messages d'erreur
        $erreur = null;

        // Traiter le formulaire POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifier le token CSRF du formulaire
            if (!csrf_verify($_POST['_csrf'] ?? null)) {
                $erreur = "Session expirée. Rechargez la page puis réessayez.";
                $this->view('login_view', compact('erreur'));
                return;
            }

            // Récupérer les données du formulaire
            $identifiant = $_POST['identifiant'] ?? '';
            $mdp = $_POST['mdp'] ?? '';

            // Vérifier que les champs ne sont pas vides
            if ($identifiant && $mdp) {
                // Préparer une requête pour chercher l'utilisateur
                // Utiliser la préparation pour éviter les injections SQL
                $query = $bdd->prepare(
                    "SELECT * FROM Utilisateurs WHERE identifiant = :id"
                );
                $query->execute(['id' => $identifiant]);
                
                // Récupérer le résultat en array associatif
                $user = $query->fetch(PDO::FETCH_ASSOC);

                // Vérifier que l'utilisateur existe ET que le mot de passe est correct
                // password_verify() compare un mot de passe en clair avec son hash
                // (plus sûr que simplement comparer les strings)
                if ($user && password_verify($mdp, $user['mot_de_passe'])) {
                    // Authentification réussie : créer la session
                    $_SESSION['admin_connecte'] = true;           // Flag de connexion
                    $_SESSION['admin_id'] = $user['id_utilisateur'];  // ID de l'utilisateur
                    $_SESSION['admin_nom'] = $user['identifiant'];    // Nom d'affichage

                    // Durcir la session à la connexion
                    session_regenerate_id(true);
                    csrf_rotate();

                    // Rediriger vers le dashboard
                    redirect('clients.index');
                    exit;
                }

                // Identifiants incorrects
                $erreur = "Identifiant ou mot de passe incorrect.";
            } else {
                // Champs vides
                $erreur = "Veuillez remplir tous les champs.";
            }
        }

        // Afficher le formulaire de login
        // Passer la variable $erreur pour afficher les messages d'erreur éventuels
        $this->view('login_view', compact('erreur'));
    }

    /**
     * Déconnecte l'utilisateur et le redirige vers le login
     * 
     * Processus :
     * 1. Détruire la session (session_destroy)
     * 2. Rediriger vers la page de login
     * 
     * Session :
     * session_destroy() supprime le fichier de session serveur ET le cookie client
     */
    public function logout()
    {
        // Supprimer toutes les données de session
        $_SESSION = [];

        // Expirer le cookie de session côté navigateur
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/', $params['domain'] ?? '', (bool)($params['secure'] ?? false), (bool)($params['httponly'] ?? true));
        }

        // Supprimer complètement la session
        session_destroy();

        // Rediriger vers le formulaire de login
        redirect('login');
    }
}
