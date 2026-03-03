<?php
/**
 * ============================================================
 * POINT D'ENTRÉE PRINCIPAL DE L'APPLICATION
 * ============================================================
 * 
 * Ce fichier est le seul fichier accédé directement.
 * Tous les requêtes HTTP sont redirigées vers ce fichier via .htaccess
 * 
 * Flux :
 * 1. Utilisateur visite /clients
 * 2. .htaccess redirige vers /public/index.php
 * 3. Ce fichier initialise l'application
 * 4. Router analyse l'URL et appelle le contrôleur approprié
 * 
 * Étapes d'initialisation :
 * 1. Démarrer la session PHP
 * 2. Enregistrer l'autoloader (chargement automatique des classes)
 * 3. Charger les helpers (fonctions globales)
 * 4. Créer et lancer le routeur
 */

// ========== 1. DÉMARRER LA SESSION (VERSION DURCIE) ==========
// La session permet de stocker des données entre les requêtes
// Utilisée pour l'authentification, les messages flash, etc.
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443)
    || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if ($isHttps) {
    ini_set('session.cookie_secure', '1');
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

if (empty($_SESSION['_session_bootstrap_done'])) {
    session_regenerate_id(true);
    $_SESSION['_session_bootstrap_done'] = time();
}

// Headers de sécurité (gains rapides, faible risque)
if (!headers_sent()) {
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' data: https://cdnjs.cloudflare.com; connect-src 'self' https:; frame-ancestors 'self'; base-uri 'self'; form-action 'self';");
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=15552000; includeSubDomains');
    }
}

// ========== ENCODAGE UTF-8 ==========
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// ========== 2. ENREGISTRER L'AUTOLOADER COMPOSER ==========
/**
 * Chargement automatique des classes via Composer
 * 
 * Les classes du dossier app/ sont chargées par classmap
 * Les helpers sont chargés via la directive "files" de composer.json
 */
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    die('Autoloader Composer introuvable. Lancez "composer install" ou "composer dump-autoload".');
}
require_once $autoloadPath;

// ========== 2bis. PROTECTION CSRF (sur formulaires protégés) ==========
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $requiresCsrf = false;

    // Quick win : protéger immédiatement les formulaires sensibles
    if (strpos($requestPath, '/auth/login') !== false || strpos($requestPath, '/settings') !== false) {
        $requiresCsrf = true;
    }

    if ($requiresCsrf) {
        $csrfFromRequest = (string)($_POST['_csrf'] ?? '');
        if (!function_exists('csrf_verify') || !csrf_verify($csrfFromRequest)) {
            http_response_code(419);
            echo "Session expirée ou requête invalide. Rechargez la page puis réessayez.";
            exit;
        }
    }
}

// ========== 3. CRÉER ET LANCER LE ROUTEUR ==========
/**
 * Créer une instance du routeur
 * Le routeur va :
 * 1. Charger la configuration des routes depuis app/routes.php
 * 2. Analyser l'URL actuelle
 * 3. Trouver la route correspondante
 * 4. Instancier le contrôleur et appeler l'action
 */
$router = new Router();

// Stocker le routeur dans $GLOBALS pour que les helpers y accèdent
// route(), redirect(), isCurrentRoute() ont besoin du routeur
$GLOBALS['router'] = $router;

// ========== 4. LANCER LE ROUTAGE ==========
/**
 * Exécuter le routeur
 * C'est ici que l'application fait vraiment quelque chose
 */

$router->run();
