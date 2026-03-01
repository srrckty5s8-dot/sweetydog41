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

// ========== 1. DÉMARRER LA SESSION ==========
// La session permet de stocker des données entre les requêtes
// Utilisée pour l'authentification, les messages flash, etc.
session_start();

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
