<?php

class Router
{
    private $routes = [];
    private $namedRoutes = [];
    private $currentRoute = null;

    public function __construct()
    {
        $this->loadRoutes();
    }

    private function loadRoutes()
    {
        $routesConfig = require __DIR__ . '/../routes.php';

        foreach ($routesConfig as $route) {
            $this->register(
                $route['name'],
                $route['method'],
                $route['action'],
                $route['pattern']
            );
        }
    }

    public function register($name, $methods, $action, $pattern)
    {
        $this->routes[] = [
            'name'    => $name,
            'methods' => explode('|', strtoupper($methods)),
            'action'  => $action,
            'pattern' => $pattern,
            'regex'   => $this->patternToRegex($pattern),
        ];

        $this->namedRoutes[$name] = [
            'pattern' => $pattern,
            'action'  => $action,
        ];
    }

    private function patternToRegex($pattern)
    {
        $regex = preg_quote($pattern, '#');
        $regex = preg_replace('#\\\{(\w+)\\\}#', '(?P<$1>[^/]+)', $regex);
        return '#^' . $regex . '$#';
    }

    private function getCurrentUrl()
    {
        // Si le rewrite met _route (optionnel)
        if (!empty($_GET['_route'])) {
            return '/' . ltrim($_GET['_route'], '/');
        }

        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

        // Retirer le basePath /Sweetydog/public (ou /Sweetydog)
        $scriptDir = str_replace('\\', '/', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
        if ($scriptDir !== '' && substr($scriptDir, -7) === '/public') {
            $scriptDir = substr($scriptDir, 0, -7);
        }
        if ($scriptDir && $scriptDir !== '/' && strpos($url, $scriptDir) === 0) {
            $url = substr($url, strlen($scriptDir));
        }

        return $url ?: '/';
    }

    public function run()
    {
        $url = $this->getCurrentUrl();
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // MATCH des routes "propres"
        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'], true)) {
                continue;
            }

            if (preg_match($route['regex'], $url, $matches)) {
                $this->currentRoute = $route;
                return $this->executeRoute($route, $matches);
            }
        }

        // FALLBACK legacy ?c=&a=
        if (!empty($_GET['c']) && !empty($_GET['a'])) {
            return $this->executeLegacyRoute($_GET['c'], $_GET['a']);
        }

        http_response_code(404);
        echo "404 - Page non trouvée";
        exit;
    }

    private function executeLegacyRoute($controller, $action)
    {
        $controllerName = ucfirst($controller) . 'Controller';
        $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php';

        if (!file_exists($controllerPath)) {
            http_response_code(500);
            die("Contrôleur introuvable: $controllerPath");
        }

        require_once $controllerPath;

        if (!class_exists($controllerName)) {
            http_response_code(500);
            die("Classe non trouvée: $controllerName");
        }

        $instance = new $controllerName();

        if (!method_exists($instance, $action)) {
            http_response_code(500);
            die("Action introuvable: $action");
        }

        call_user_func([$instance, $action]);
    }

    private function executeRoute($route, $matches)
    {
        [$controllerName, $actionName] = explode('@', $route['action'], 2);

        $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php';

        if (!file_exists($controllerPath)) {
            http_response_code(500);
            die("Contrôleur introuvable: $controllerPath");
        }

        require_once $controllerPath;

        if (!class_exists($controllerName)) {
            http_response_code(500);
            die("Classe non trouvée: $controllerName");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $actionName)) {
            http_response_code(500);
            die("Action introuvable: $actionName");
        }

        // Garder uniquement les params nommés (id, etc.)
        $paramsAssoc = array_filter($matches, function ($key) {
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);

        // IMPORTANT : passer dans l'ordre (tracking($id))
        $params = array_values($paramsAssoc);

        call_user_func_array([$controller, $actionName], $params);
    }

    /**
     * Helper route() : génère l'URL à partir du pattern routes.php
     * Ex: route('animals.tracking', ['id'=>3]) -> /Sweetydog/animals/3/tracking
     */
    public function route($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Route nommée introuvable: $name");
        }

        $pattern = $this->namedRoutes[$name]['pattern'];
        $url = $pattern;

        foreach ($params as $key => $value) {
            $url = preg_replace(
                '/\{' . preg_quote($key, '/') . '\}/',
                rawurlencode((string)$value),
                $url
            );
        }

        if (preg_match('/\{\w+\}/', $url)) {
            throw new Exception("Paramètres manquants pour la route $name: $url");
        }

        // Préfixe basePath (/Sweetydog)
        $basePath = str_replace('\\', '/', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
        if ($basePath !== '' && substr($basePath, -7) === '/public') {
            $basePath = substr($basePath, 0, -7);
        }
        if ($basePath && $basePath !== '/') {
            return $basePath . $url;
        }

        return $url;
    }

    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }
}
