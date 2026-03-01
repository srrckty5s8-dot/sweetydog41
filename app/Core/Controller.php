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
            ob_start();
            require $path;
            $html = (string)ob_get_clean();
            echo $this->injectGlobalFavicon($html);
            return;
        }

        http_response_code(500);
        die("Vue introuvable : $path");
    }

    private function injectGlobalFavicon(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        if (!preg_match('/<head\b/i', $html) || !preg_match('/<\/head>/i', $html)) {
            return $html;
        }

        $faviconPngHref = $this->resolvePublicAssetUrl([
            '/favicon-32x32.png',
            '/public/favicon-32x32.png',
            '/assets/favicon.png',
            '/public/assets/favicon.png',
        ], 'favicon-32x32.png');

        $favicon16Href = $this->resolvePublicAssetUrl([
            '/favicon-16x16.png',
            '/public/favicon-16x16.png',
            '/assets/favicon.png',
            '/public/assets/favicon.png',
        ], 'favicon-16x16.png');

        $faviconIcoHref = $this->resolvePublicAssetUrl([
            '/favicon.ico',
            '/public/favicon.ico',
        ], 'favicon.ico');

        $appleTouchHref = $this->resolvePublicAssetUrl([
            '/apple-touch-icon.png',
            '/public/apple-touch-icon.png',
            '/apple-touch-icon-180x180.png',
            '/public/apple-touch-icon-180x180.png',
            '/assets/apple-touch-icon-v2.png',
            '/public/assets/apple-touch-icon-v2.png',
        ], 'apple-touch-icon.png');

        $manifestHref = $this->resolvePublicAssetUrl([
            '/site.webmanifest',
            '/public/site.webmanifest',
        ], 'site.webmanifest');

        $tag = '';
        $tag .= "\n    <link rel=\"icon\" type=\"image/png\" sizes=\"32x32\" href=\"" . htmlspecialchars($faviconPngHref, ENT_QUOTES, 'UTF-8') . "\">";
        $tag .= "\n    <link rel=\"icon\" type=\"image/png\" sizes=\"16x16\" href=\"" . htmlspecialchars($favicon16Href, ENT_QUOTES, 'UTF-8') . "\">";
        $tag .= "\n    <link rel=\"icon\" href=\"" . htmlspecialchars($faviconIcoHref, ENT_QUOTES, 'UTF-8') . "\" sizes=\"any\">";
        $tag .= "\n    <link rel=\"shortcut icon\" href=\"" . htmlspecialchars($faviconIcoHref, ENT_QUOTES, 'UTF-8') . "\">";
        $tag .= "\n    <link rel=\"apple-touch-icon\" href=\"" . htmlspecialchars($appleTouchHref, ENT_QUOTES, 'UTF-8') . "\">";
        $tag .= "\n    <link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"" . htmlspecialchars($appleTouchHref, ENT_QUOTES, 'UTF-8') . "\">";
        $tag .= "\n    <link rel=\"apple-touch-icon-precomposed\" href=\"" . htmlspecialchars($appleTouchHref, ENT_QUOTES, 'UTF-8') . "\">";
        $tag .= "\n    <link rel=\"manifest\" href=\"" . htmlspecialchars($manifestHref, ENT_QUOTES, 'UTF-8') . "\">";
        $tag .= "\n    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\">";
        $tag .= "\n    <meta name=\"apple-mobile-web-app-title\" content=\"SweetyDog\">";
        $tag .= "\n    <meta name=\"theme-color\" content=\"#778572\">";

        return (string)preg_replace('/<\/head>/i', $tag . "\n</head>", $html, 1);
    }

    private function resolvePublicAssetUrl(array $absoluteCandidates, string $fallbackRelative): string
    {
        $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/\\') : '';

        if ($docRoot !== '') {
            foreach ($absoluteCandidates as $candidate) {
                $path = '/' . ltrim((string)$candidate, '/');
                if (is_file($docRoot . $path)) {
                    return $path;
                }
            }
        }

        return function_exists('url') ? url($fallbackRelative) : '/' . ltrim($fallbackRelative, '/');
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
