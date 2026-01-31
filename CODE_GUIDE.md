# 📚 GUIDE DE COMPRÉHENSION DU CODE - SWEETYDOG

Bienvenue ! Ce document vous aidera à naviguer dans la codebase professionnelle de Sweetydog.

---

## 🏗️ Architecture Générale

```
Sweetydog/
├── public/
│   ├── index.php          ← Point d'entrée unique
│   └── .htaccess          ← Rewrite rules pour les URLs propres
│
├── app/
│   ├── Core/              ← Cœur du framework
│   │   ├── Router.php     ← Moteur de routage avec routes nommées
│   │   ├── Controller.php ← Classe de base pour tous les contrôleurs
│   │   └── Database.php   ← Connexion à la base de données
│   │
│   ├── Controllers/       ← Logique métier
│   │   ├── AuthController.php      ← Authentification
│   │   ├── ClientController.php    ← Gestion des clients
│   │   └── AnimalController.php    ← Gestion des animaux
│   │
│   ├── Models/            ← Accès à la base de données
│   │   ├── Client.php
│   │   ├── Animal.php
│   │   └── RendezVous.php
│   │
│   ├── routes.php         ← Configuration de toutes les routes
│   ├── helpers.php        ← Fonctions globales utiles
│
├── app/Views/                 ← Templates HTML/PHP
│   ├── login_view.php
│   ├── liste_clients_view.php
│   ├── modifier_client_view.php
│   └── ...
│
├── config/
│   └── db.php             ← Configuration base de données
│
└── assets/
    ├── style.css          ← Feuille de styles
    └── ...
```

---

## 🔄 Flux de Requête

Voici ce qui se passe quand un utilisateur visite `/clients` :

```
1. Requête HTTP
   └─ GET /clients

2. Apache .htaccess
   └─ Redirige vers public/index.php

3. public/index.php
   ├─ Démarre la session
   ├─ Charge l'autoloader
   ├─ Charge les helpers
   └─ Lance le Router

4. Router.php
   ├─ Charge app/routes.php
   ├─ Compare l'URL avec les patterns
   ├─ Trouve : 'clients.index' → 'ClientController@index'
   └─ Lance l'action

5. ClientController.php
   ├─ Vérifie l'authentification
   ├─ Récupère les données en base
   └─ Appelle $this->view()

6. Controller.php::view()
   ├─ Extrait les variables
   └─ Inclut app/Views/liste_clients_view.php

7. app/Views/liste_clients_view.php
   └─ Affiche le HTML avec les données

8. Réponse HTTP au navigateur
```

---

## 🛣️ Le Système de Routes

### Qu'est-ce qu'une Route ?

Une route lie une URL à un contrôleur et une action.

**Ancien système (obsolète)** :
```php
// URL : index.php?c=client&a=edit&id=5
$_GET['c']  // contrôleur
$_GET['a']  // action
$_GET['id'] // paramètre
```

**Nouveau système (actuel)** :
```php
// URL : /clients/5/edit
// Route : 'clients.edit' → '/clients/{id}/edit'
route('clients.edit', ['id' => 5])  // Génère : /clients/5/edit
```

### Comment ça marche ?

Fichier : `app/routes.php`

```php
[
    'name'    => 'clients.edit',           // Identifiant unique
    'method'  => 'GET',                    // Méthode HTTP
    'action'  => 'ClientController@edit',  // Contrôleur@action
    'pattern' => '/clients/{id}/edit'      // Pattern avec paramètres
]
```

**Conversion en Regex** :
```
Pattern : /clients/{id}/edit
Regex   : #^/clients/(?P<id>[^/]+)/edit$#

URL visitée : /clients/5/edit
Match       : Oui ! {id} = '5'
```

---

## 🎮 Les Contrôleurs

### Structure d'un Contrôleur

Fichier : `app/Controllers/ClientController.php`

```php
class ClientController extends Controller {
    // Hérité : $this->view() et $this->requireLogin()
    
    public function index() {
        // GET /clients
        // Afficher la liste
    }
    
    public function create() {
        // GET /clients/new
        // Afficher le formulaire de création
    }
    
    public function store() {
        // POST /clients
        // Sauvegarder en base
    }
    
    public function edit($id) {
        // GET /clients/{id}/edit
        // Afficher le formulaire d'édition
        // $id reçoit le paramètre {id} automatiquement
    }
    
    public function update($id) {
        // POST /clients/{id}
        // Mettre à jour en base
    }
}
```

### Pattern CRUD Standard

Chaque ressource suit ce pattern :

| HTTP | Route | Action | Rôle |
|------|-------|--------|------|
| GET | /clients | index | Lister |
| GET | /clients/new | create | Afficher formulaire |
| POST | /clients | store | Créer |
| GET | /clients/{id}/edit | edit | Afficher formulaire |
| POST | /clients/{id} | update | Modifier |
| POST | /clients/{id}/delete | delete | Supprimer |

---

## 📄 Les Vues (Templates)

Fichier : `app/Views/liste_clients_view.php`

```php
<?php
// $clients et $rdv_du_jour sont disponibles grâce à extract()

foreach ($clients as $client) : ?>
    <div class="client">
        <h3><?php echo e($client['prenom']); ?></h3>
        
        <!-- Générer une URL avec la route helper -->
        <a href="<?php echo route('clients.edit', ['id' => $client['id_proprietaire']]); ?>">
            Éditer
        </a>
    </div>
<?php endforeach; ?>
```

**Fonctions importantes** :
- `e()` : Échappe le texte pour éviter les XSS
- `route()` : Génère une URL depuis une route nommée
- `isCurrentRoute()` : Vérifie si on est sur la page actuelle
- `param()` : Récupère un GET/POST
- `flashMessage()` : Stocker un message entre les requêtes

---

## 🔐 L'Authentification

### Session de l'Utilisateur

Créée dans `AuthController::login()` :

```php
$_SESSION['admin_connecte'] = true;      // Flag de connexion
$_SESSION['admin_id'] = 123;             // ID de l'utilisateur
$_SESSION['admin_nom'] = 'john';         // Nom d'affichage
```

### Vérifier que l'utilisateur est connecté

Dans n'importe quel contrôleur :

```php
class ClientController extends Controller {
    public function index() {
        $this->requireLogin();  // Redirige vers login si pas connecté
        // Code protégé...
    }
}
```

---

## 🗄️ Les Modèles

Fichier : `app/Models/Client.php`

Les modèles gèrent l'accès à la base de données.

```php
class Client {
    // Requêtes de lecture
    public static function getAllWithAnimaux($search) {
        // Récupère tous les clients avec leurs animaux
    }
    
    public static function findProprietaire($id) {
        // Récupère un client par ID
    }
    
    // Requêtes de modification
    public static function createProprietaire($data) {
        // Crée un nouveau client
    }
    
    public static function updateProprietaire($id, $data) {
        // Met à jour un client
    }
}
```

---

## 🛠️ Les Helpers (Fonctions Globales)

Fichier : `app/helpers.php`

### Génération d'URLs

```php
// Créer un lien
route('clients.index')                    // /clients
route('clients.edit', ['id' => 5])       // /clients/5/edit

// Rediriger
redirect('clients.index');                // Redirige vers /clients
redirect('clients.edit', ['id' => 5]);   // Redirige vers /clients/5/edit
```

### Paramètres et Données

```php
param('id');                    // Récupère GET['id'] ou POST['id']
param('search', '')             // Défaut à '' s'il n'existe pas
e($text);                       // Échappe pour XSS
```

### Messages Flash (Temporaires)

```php
// Dans le contrôleur
flashMessage('success', 'Client créé !');
redirect('clients.index');

// Dans la vue
$messages = getAllFlashMessages();
foreach ($messages as $type => $text) {
    echo "<div class='alert-$type'>$text</div>";
}
```

---

## 🎨 CSS et Styling

Fichier : `assets/style.css`

**Variables CSS disponibles** :

```css
:root {
    --vert-fonce: #1b4332;      /* Vert foncé */
    --vert-moyen: #2d6a4f;      /* Vert moyen */
    --blanc-casse: #f8f9f2;     /* Blanc cassé */
}
```

**Classe standard** :
- `.container` : Conteneur principal
- `.form-group` : Groupe de formulaire
- `.btn`, `.btn-primary`, `.btn-danger` : Boutons
- `.alert`, `.alert-success`, `.alert-error` : Alertes

---

## 🔍 Comment Ajouter une Nouvelle Page

Exemple : Créer une page "Statistiques"

### 1. Créer la Route

Fichier : `app/routes.php`

```php
[
    'name'    => 'stats.index',
    'method'  => 'GET',
    'action'  => 'StatsController@index',
    'pattern' => '/statistics'
]
```

### 2. Créer le Contrôleur

Fichier : `app/Controllers/StatsController.php`

```php
class StatsController extends Controller {
    public function index() {
        $this->requireLogin();
        
        // Récupérer les données
        $stats = [
            'total_clients' => 50,
            'total_animals' => 120
        ];
        
        // Afficher la vue
        $this->view('stats_view', compact('stats'));
    }
}
```

### 3. Créer la Vue

Fichier : `app/Views/stats_view.php`

```php
<h1>Statistiques</h1>
<p>Clients : <?php echo e($stats['total_clients']); ?></p>
<p>Animaux : <?php echo e($stats['total_animals']); ?></p>

<a href="<?php echo route('clients.index'); ?>">Retour</a>
```

### 4. Ajouter un Lien dans la Navigation

```php
<a href="<?php echo route('stats.index'); ?>">Statistiques</a>
```

---

## 🐛 Débogage

### Voir les Erreurs

```php
// Afficher une variable
var_dump($data);

// Arrêter l'exécution
die("Message d'erreur");

// Logger une valeur
error_log("Debug: " . json_encode($data));
```

### Tester une Route

URL : `/clients` ou `/clients/5/edit`

Vérifier :
1. La route existe dans `app/routes.php`
2. Le contrôleur et la méthode existent
3. La vue existe dans `app/Views/`

---

## 📋 Checklist Avant la Production

- [ ] Tous les `header()` remplacés par `redirect()`
- [ ] Toutes les données affichées avec `e()`
- [ ] Authentification vérifiée avec `requireLogin()`
- [ ] Paramètres validés avant utilisation
- [ ] Messages d'erreur de base de données cachés
- [ ] HTTPS activé sur le serveur
- [ ] Session sécurisée configurée (httponly, secure)

---

## 📚 Ressources

- **Routes** : Voir `app/routes.php` pour la liste complète
- **Contrôleurs** : Voir `app/Controllers/` pour tous les exemples
- **Modèles** : Voir `app/Models/` pour les requêtes BD
- **Helpers** : Voir `app/helpers.php` pour toutes les fonctions

---

## 💡 Bonnes Pratiques

✅ **À FAIRE** :
- Utiliser les routes nommées : `route('clients.edit', ['id' => $id])`
- Valider les données : `if (empty($name)) { die("Erreur"); }`
- Échapper le texte : `e($user_input)`
- Utiliser des noms explicites : `$total_clients` au lieu de `$t`
- Commenter le code complexe

❌ **À ÉVITER** :
- Utiliser `header('Location:')` directement → utiliser `redirect()`
- Afficher les variables sans `e()` → risque XSS
- Faire confiance aux données de l'utilisateur
- Rendre le code trop complexe
- Ne pas tester avant de pousser en production

---

Bonne chance ! 🚀
