<?php
/**
 * ============================================================
 * HELPERS - Fonctions utilitaires pour l'application
 * ============================================================
 * 
 * Ce fichier contient les fonctions helper qui facilitent :
 * - La génération d'URLs avec les routes nommées
 * - Les redirections
 * - L'accès aux paramètres et données de session
 * - L'affichage sécurisé des données (XSS protection)
 * 
 * Ces fonctions sont globales et accessibles partout :
 * - Dans les vues PHP
 * - Dans les contrôleurs
 * - Dans n'importe quel fichier incluant ce fichier
 */

// Instance globale du routeur stockée pour que les helpers y accèdent
$GLOBALS['router'] = null;

/**
 * Génère une URL à partir du nom d'une route nommée
 * 
 * C'est la fonction principale pour créer des liens dans l'application.
 * Remplace le système ancien avec ?c=controller&a=action
 * 
 * Exemples d'utilisation :
 * - route('clients.index')                 → '/clients'
 * - route('clients.edit', ['id' => 5])     → '/clients/5/edit'
 * - route('animals.tracking', ['id' => 3]) → '/animals/3/tracking'
 * 
 * Dans une vue :
 * <a href="<?php echo route('clients.edit', ['id' => $client['id_proprietaire']]); ?>">
 *     Éditer
 * </a>
 * 
 * Ou dans un formulaire :
 * <form action="<?php echo route('clients.store'); ?>" method="POST">
 * 
 * @param string $name        - Nom unique de la route (ex: 'clients.edit')
 * @param array  $params      - Paramètres à injecter dans l'URL (ex: ['id' => 5])
 * @param array  $queryParams - Paramètres GET optionnels (ex: ['from' => 'facturation'])
 * @return string        - URL générée (ex: '/clients/5/edit')
 * @throws Exception     - Si la route n'existe pas
 */
function route($name, $params = [], $queryParams = [])
{
    // Vérifier que le routeur est initialisé (ce qui se fait dans public/index.php)
    if (!isset($GLOBALS['router'])) {
        throw new Exception("Router non initialisé");
    }

    // Déléguer au routeur pour générer l'URL
    $url = $GLOBALS['router']->route($name, $params);

    if (!empty($queryParams)) {
        $separator = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $separator . http_build_query($queryParams);
    }

    return $url;
}

/**
 * Génère une URL absolue avec basePath
 * 
 * Utile pour :
 * - Les inclusions CSS/JS : url('assets/style.css')
 * - Les images : url('images/logo.png')
 * - Les fichiers PDF : url('factures/Facture_123.pdf')
 * - Obtenir l'URL complète d'une ressource
 * 
 * Détecte automatiquement le basePath selon l'installation
 * (fonctionne si l'app est dans /Sweetydog, /myapp, ou à la racine)
 * 
 * Exemple :
 * <link rel="stylesheet" href="<?php echo url('assets/style.css'); ?>">
 * <a href="<?php echo url('factures/invoice.pdf'); ?>">Télécharger</a>
 * 
 * @param string $path - Chemin relatif (ex: 'assets/style.css')
 * @return string      - URL absolue (ex: '/Sweetydog/assets/style.css')
 */
function url($path = '')
{
    // ✅ CORRECTION : Utiliser la même logique que Router::route()
    // pour garantir la cohérence du basePath
    
    $basePath = str_replace('\\', '/', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $normalizedBasePath = strtolower($basePath);
    
    // Si on est dans /public, déterminer si on doit utiliser /public ou le dossier parent
    if ($basePath !== '' && substr($normalizedBasePath, -7) === '/public') {
        $publicPath = $basePath;
        $resolvedPath = $publicPath;
        
        // Si l'URL actuelle ne contient pas /public, utiliser le dossier parent
        if ($requestPath === '' || strpos(strtolower($requestPath), strtolower($publicPath)) !== 0) {
            $resolvedPath = substr($publicPath, 0, -7);
        }
        $basePath = $resolvedPath;
    }
    
    // Construire l'URL complète
    if ($basePath && $basePath !== '/') {
        return $basePath . '/' . ltrim($path, '/');
    }
    
    return '/' . ltrim($path, '/');
}

/**
 * Redirige vers une route ou une URL
 * 
 * Exemples :
 * - redirect('clients.index')                                    → Redirige vers /clients
 * - redirect('clients.edit', ['id' => 5])                       → Redirige vers /clients/5/edit
 * - redirect('animals.tracking', ['id' => 5], ['success' => 1]) → Redirige vers /animals/5/tracking?success=1
 * - redirect('/absolute/path')                                   → Redirige vers /absolute/path
 * - redirect('https://example.com')                              → Redirige vers URL externe
 * 
 * Utilisé après une action (créer, modifier, supprimer) :
 * 
 * public function store($data) {
 *     // ... Sauvegarder le client en base ...
 *     redirect('clients.index');
 * }
 * 
 * @param string $route       - Nom de la route ou URL complète
 * @param array  $params      - Paramètres pour générer l'URL (si c'est une route nommée)
 * @param array  $queryParams - Paramètres GET optionnels (ex: ['success' => 1])
 */
function redirect($route, $params = [], $queryParams = [])
{
    // Déterminer si c'est une URL absolue ou une route nommée
    // URLs absolues : commencent par / ou http://
    // Routes nommées : clients.index, animals.edit, etc.
    $url = (strpos($route, '/') === 0 || strpos($route, 'http') === 0) 
        ? $route                           // C'est une URL absolue, l'utiliser telle quelle
        : route($route, $params);          // C'est une route nommée, générer l'URL
    
    // ✅ Ajouter les paramètres GET si fournis
    if (!empty($queryParams)) {
        $url .= '?' . http_build_query($queryParams);
    }
    
    // Envoyer le header de redirection et arrêter l'exécution
    header('Location: ' . $url);
    exit;
}


/**
 * Retourne l'URL actuelle (relative au basePath)
 * 
 * Utile pour vérifier sur quelle page on est actuellement
 * 
 * Exemple :
 * - Si l'utilisateur visite '/clients', retourne '/clients'
 * - Si l'utilisateur visite '/clients/5/edit', retourne '/clients/5/edit'
 * 
 * Usage :
 * $current = currentUrl();
 * if (strpos($current, '/admin') === 0) {
 *     // On est dans la section admin
 * }
 * 
 * @return string - URL actuelle (ex: '/clients')
 */
function currentUrl()
{
    // Extraire le chemin de REQUEST_URI (sans le domaine)
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Récupérer le basePath (dossier contenant index.php)
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Supprimer le basePath de l'URL pour obtenir l'URL relative
    if (!empty($scriptPath) && $scriptPath !== '/' && strpos($url, $scriptPath) === 0) {
        $url = substr($url, strlen($scriptPath));
    }
    
    // Retourner '/' si l'URL est vide
    return $url ?: '/';
}

/**
 * Vérifie si la route actuelle correspond à un nom
 * 
 * Utile pour :
 * - Mettre en évidence le lien actif dans la navigation
 * - Masquer/afficher du contenu selon la page actuelle
 * 
 * Exemples :
 * 
 * // Dans la navigation :
 * <a href="<?php echo route('clients.index'); ?>" 
 *    class="<?php echo isCurrentRoute('clients.index') ? 'active' : ''; ?>">
 *     Clients
 * </a>
 * 
 * // En condition :
 * <?php if (isCurrentRoute('clients.edit')) : ?>
 *     <p>Vous êtes en train de modifier un client</p>
 * <?php endif; ?>
 * 
 * @param string $name - Nom de la route (ex: 'clients.edit')
 * @return bool        - true si c'est la route actuelle, false sinon
 */
function isCurrentRoute($name)
{
    // Vérifier que le routeur est initialisé
    if (!isset($GLOBALS['router'])) {
        return false;
    }
    
    // Récupérer la route actuelle depuis le routeur
    $current = $GLOBALS['router']->getCurrentRoute();
    
    // Retourner true si la route actuelle correspond au nom demandé
    return $current && $current['name'] === $name;
}

/**
 * Récupère un paramètre GET ou POST
 * 
 * Remplace directement $_GET et $_POST avec contrôle des valeurs
 * Retourne une valeur par défaut si le paramètre n'existe pas
 * 
 * Exemples :
 * - param('id')                     → $_GET['id'] ou $_POST['id'] ou null
 * - param('search', '')             → $_GET['search'] ou '' si absent
 * - param('email', 'default@mail')  → $_GET['email'] ou 'default@mail'
 * 
 * Usage dans les contrôleurs :
 * $id = param('id');
 * $search = param('search', '');
 * 
 * Usage dans les vues (remplissage de formulaires) :
 * <input type="text" value="<?php echo e(param('name', '')); ?>">
 * 
 * @param string $key     - Clé du paramètre
 * @param mixed  $default - Valeur par défaut si absent
 * @return mixed          - Valeur du paramètre ou la valeur par défaut
 */
function param($key, $default = null)
{
    // Chercher d'abord en GET, puis en POST, puis retourner la valeur par défaut
    return $_GET[$key] ?? $_POST[$key] ?? $default;
}

/**
 * Échappe une valeur pour éviter les failles XSS
 * 
 * XSS (Cross-Site Scripting) = injection de code JavaScript dans l'HTML
 * Cette fonction convertit les caractères spéciaux en entités HTML
 * 
 * Exemples :
 * - e('<script>alert("hack")</script>')  → '&lt;script&gt;alert(&quot;hack&quot;)&lt;/script&gt;'
 * - e("O'Brien")                         → 'O&#039;Brien'
 * - e('<img src=x onerror=alert(1)>')    → '&lt;img src=x onerror=alert(1)&gt;'
 * 
 * IMPORTANT : À utiliser chaque fois qu'on affiche des données utilisateur :
 * 
 * ❌ DANGEREUX :
 * <p><?php echo $_GET['name']; ?></p>
 * 
 * ✅ SÉCURISÉ :
 * <p><?php echo e($_GET['name']); ?></p>
 * 
 * Utilisation avec le helper param() :
 * <input type="text" value="<?php echo e(param('email')); ?>">
 * 
 * @param mixed $value - Valeur à échapper (string ou array)
 * @return mixed       - Valeur échappée
 */
function e($value)
{
    // Si c'est un array, appliquer recursively la fonction e() à chaque élément
    if (is_array($value)) {
        return array_map('e', $value);
    }
    
    // Convertir les caractères spéciaux en entités HTML
    // ENT_QUOTES : échappe aussi les guillemets simples et doubles
    // UTF-8 : encodage utilisé pour les caractères spéciaux
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Flash messages - Messages stockés en session pour affichage à la redirection
 * 
 * Utilisé pour afficher des messages de confirmation/erreur après une action
 * Le message s'affiche UNE FOIS puis est supprimé
 * 
 * Workflow typique :
 * 1. Dans le contrôleur : flashMessage('success', 'Client créé avec succès')
 * 2. Redirection : redirect('clients.index')
 * 3. Dans la vue : afficher le message avec getAllFlashMessages()
 * 
 * Avantage : le message persiste à travers la redirection (pas d'URL parameter)
 * 
 * @param string $type    - Type de message : 'success', 'error', 'warning', 'info'
 * @param string $message - Contenu du message
 */
function flashMessage($type, $message)
{
    // Initialiser le tableau des flash messages s'il n'existe pas
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    // Stocker le message dans la session
    $_SESSION['flash_messages'][$type] = $message;
}

/**
 * Récupère un flash message et le supprime de la session
 * 
 * Exemple :
 * $successMsg = getFlashMessage('success');
 * if ($successMsg) {
 *     echo '<div class="alert-success">' . e($successMsg) . '</div>';
 * }
 * 
 * @param string $type - Type de message
 * @return string|null - Le message ou null si absent
 */
function getFlashMessage($type)
{
    // Récupérer le message s'il existe
    $message = $_SESSION['flash_messages'][$type] ?? null;
    
    // Supprimer le message immédiatement (pour qu'il n'apparaisse qu'une fois)
    unset($_SESSION['flash_messages'][$type]);
    
    return $message;
}

/**
 * Récupère TOUS les flash messages en une seule fois
 * 
 * Utile pour afficher tous les messages (succès et erreurs) dans une section
 * 
 * Exemple :
 * <?php foreach (getAllFlashMessages() as $type => $message) : ?>
 *     <div class="alert alert-<?php echo e($type); ?>">
 *         <?php echo e($message); ?>
 *     </div>
 * <?php endforeach; ?>
 * 
 * @return array - Array associatif avec type => message (vide si aucun message)
 */
function getAllFlashMessages()
{
    // Récupérer tous les messages
    $messages = $_SESSION['flash_messages'] ?? [];
    
    // Vider le tableau (tous les messages vont être affichés une seule fois)
    $_SESSION['flash_messages'] = [];
    
    return $messages;
}

/**
 * Vérifie si l'utilisateur a une permission
 * 
 * À étendre selon le système d'authentification/autorisation
 * 
 * Exemples :
 * - can('edit_clients')      → vérifier si peut éditer les clients
 * - can('delete_animals')    → vérifier si peut supprimer des animaux
 * - can('admin')             → vérifier si est administrateur
 * 
 * Utilisation dans les vues :
 * <?php if (can('edit_clients')) : ?>
 *     <a href="<?php echo route('clients.edit', ['id' => $id]); ?>">Éditer</a>
 * <?php endif; ?>
 * 
 * Implémentation :
 * À développer selon votre système de rôles/permissions
 * Pour l'instant, on vérifie simplement si l'utilisateur est connecté
 * 
 * @param string $permission - Nom de la permission
 * @return bool              - true si l'utilisateur a la permission, false sinon
 */
function can($permission)
{
    // Pour l'instant : vérifier simplement si l'utilisateur est connecté
    // À développer avec un vrai système de permissions
    return isset($_SESSION['user']);
}

/**
 * Retourne (et crée si besoin) le token CSRF de session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

/**
 * Champ hidden prêt à injecter dans un <form method="POST">.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Vérifie un token CSRF transmis.
 */
function csrf_verify(?string $token): bool
{
    $token = (string)$token;
    $sessionToken = (string)($_SESSION['_csrf_token'] ?? '');

    if ($token === '' || $sessionToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $token);
}

/**
 * Regénère explicitement le token CSRF (utile après login/logout).
 */
function csrf_rotate(): void
{
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}
