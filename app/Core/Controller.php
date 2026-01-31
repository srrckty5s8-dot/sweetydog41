<?php
/**
 * ============================================================
 * CLASSE DE BASE POUR TOUS LES CONTRÔLEURS
 * ============================================================
 * 
 * Cette classe fournit les fonctionnalités communes à tous les contrôleurs :
 * - Afficher les vues (template PHP)
 * - Vérifier l'authentification
 * - Fonctionnalités réutilisables
 * 
 * Tous les contrôleurs doivent hériter de cette classe :
 * 
 * class MonController extends Controller {
 *     public function index() {
 *         return $this->view('nom_vue', ['données' => 'values']);
 *     }
 * }
 */
class Controller
{
    /**
     * Affiche une vue (template PHP)
     * 
     * Le contrôleur appelle cette méthode pour afficher le résultat au client
     * La vue reçoit les données par extraction de variables
     * 
     * Utilisation dans un contrôleur :
     * 
     * public function index($id) {
     *     $client = $this->db->query("SELECT * FROM clients WHERE id = ?", [$id]);
     *     return $this->view('clients/show', [
     *         'client' => $client,
     *         'page_title' => 'Détail client'
     *     ]);
     * }
     * 
     * Dans la vue (app/Views/clients/show.php) :
     * Les variables seront directement accessibles :
     * - $client sera disponible
     * - $page_title sera disponible
     * 
     * @param string $view - Chemin de la vue sans extension (ex: 'clients/edit')
     * @param array  $data - Données à passer à la vue (deviennent des variables PHP)
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $path = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($path)) {
            require $path;
            return;
        }

        http_response_code(500);
        die("Vue introuvable : $path");
    }


    /**
     * Vérifie que l'utilisateur est connecté
     * 
     * À appeler au début des actions qui nécessitent une authentification
     * Si l'utilisateur n'est pas connecté, redirige vers la page de login
     * 
     * Utilisation dans un contrôleur :
     * 
     * public function edit($id) {
     *     $this->requireLogin();  // Vérifier que l'utilisateur est connecté
     *     // ... code de modification ...
     * }
     * 
     * À AMÉLIORER :
     * - Utiliser la nouvelle fonction redirect() au lieu de header()
     * - Implémenter des rôles/permissions plus granulaires
     */
    protected function requireLogin()
    {
        // Vérifier que la clé 'admin_connecte' existe en session
        // Cette clé est définie lors de la connexion dans AuthController
        if (!isset($_SESSION['admin_connecte'])) {
            // L'utilisateur n'est pas connecté, le rediriger vers le login
            // À améliorer : utiliser redirect('login') au lieu d'une URL hard-codée
            redirect('login');
        }
    }
}
