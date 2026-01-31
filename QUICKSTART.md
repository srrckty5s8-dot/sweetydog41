# 🚀 GUIDE DE DÉMARRAGE RAPIDE

Bienvenue dans Sweetydog ! Ce guide vous aidera à démarrer en 5 minutes.

---

## ⚡ En 2 Minutes

### Qu'est-ce que Sweetydog ?

Application PHP pour gérer :
- 👥 **Clients** (propriétaires d'animaux)
- 🐕 **Animaux** (chiens à toiletter)
- 📅 **Rendez-vous** (calendrier)
- 🧴 **Soins** (suivi des toilettages)

### Installation

```bash
# 1. Cloner le projet
cd /Applications/MAMP/htdocs/Sweetydog

# 2. Configurer la base de données
vim config/db.php

# 3. Lancer MAMP/Apache
# Dans MAMP : Start servers

# 4. Ouvrir dans le navigateur
http://localhost:8888/Sweetydog
# ou
http://localhost/Sweetydog
```

---

## 📖 Structure du Projet

```
Sweetydog/
├── public/          ← Point d'entrée (index.php)
├── app/
│   ├── Core/        ← Routeur, Contrôleur de base
│   ├── Controllers/ ← Logique métier
│   ├── Models/      ← Requêtes base de données
│   ├── routes.php   ← Configuration routes
│   └── helpers.php  ← Fonctions globales
├── app/Views/           ← Templates HTML
├── assets/          ← CSS, images
└── config/          ← Configuration base de données
```

---

## 🔐 Authentification

### Connexion

```
URL : http://localhost:8888/Sweetydog
Utilisateur : (voir base de données)
Mot de passe : (voir base de données)
```

### Session

```php
$_SESSION['admin_connecte']  // true si connecté
$_SESSION['admin_id']        // ID de l'utilisateur
$_SESSION['admin_nom']       // Nom d'affichage
```

---

## 🛣️ Routes Principales

Toutes les routes :

| URL | Méthode | Contrôleur | Action |
|-----|---------|------------|--------|
| `/` | GET | AuthController | redirectHome |
| `/auth/login` | GET/POST | AuthController | login |
| `/auth/logout` | GET | AuthController | logout |
| `/clients` | GET | ClientController | index |
| `/clients/new` | GET | ClientController | create |
| `/clients` | POST | ClientController | store |
| `/clients/{id}/edit` | GET | ClientController | edit |
| `/clients/{id}` | POST | ClientController | update |
| `/clients/{id}/delete` | POST | ClientController | delete |
| `/animals/{id}/edit` | GET | AnimalController | edit |
| `/animals/{id}` | POST | AnimalController | update |
| `/animals/{id}/tracking` | GET | AnimalController | tracking |

### Générer une URL

```php
// Dans une vue ou contrôleur
route('clients.index')                    // /clients
route('clients.edit', ['id' => 5])       // /clients/5/edit
route('animals.tracking', ['id' => 3])   // /animals/3/tracking
```

---

## 🎮 Ajouter Une Nouvelle Page

### Exemple : Page "À Propos"

#### 1️⃣ Ajouter la Route

Fichier : `app/routes.php`

```php
['name' => 'about', 'method' => 'GET', 'action' => 'PageController@about', 'pattern' => '/about'],
```

#### 2️⃣ Créer le Contrôleur

Fichier : `app/Controllers/PageController.php`

```php
<?php

class PageController extends Controller {
    public function about() {
        $this->requireLogin();  // Optionnel : vérifier auth
        
        $page_title = 'À Propos';
        $content = 'Sweetydog est une application...';
        
        $this->view('pages/about', compact('page_title', 'content'));
    }
}
```

#### 3️⃣ Créer la Vue

Fichier : `app/Views/pages/about.php`

```php
<h1><?php echo e($page_title); ?></h1>
<p><?php echo e($content); ?></p>

<a href="<?php echo route('clients.index'); ?>">Retour</a>
```

#### 4️⃣ Ajouter un Lien dans la Navigation

```php
<a href="<?php echo route('about'); ?>">À Propos</a>
```

**Voilà !** 🎉 Visiter `http://localhost:8888/Sweetydog/about`

---

## 📝 Les Fichiers Importants

### app/Core/Router.php
**Le routeur** : Match les URLs avec les contrôleurs

Clés :
- `register()` : Enregistrer une route
- `run()` : Matcher l'URL actuelle
- `route()` : Générer une URL

### app/helpers.php
**Fonctions globales** : Utilisables partout

Principales :
- `route($name, $params)` : Générer une URL
- `redirect($route, $params)` : Rediriger
- `param($key, $default)` : Récupérer GET/POST
- `e($value)` : Échapper pour XSS

### app/routes.php
**Configuration** : Toutes les routes

Format :
```php
[
    'name'    => 'clients.index',
    'method'  => 'GET',
    'action'  => 'ClientController@index',
    'pattern' => '/clients'
]
```

### app/Views/
**Templates** : Fichiers HTML+PHP

Accès aux données : via `extract()`
```php
<?php echo e($client['nom']); ?>
```

---

## 🔒 Sécurité - À Retenir

### ✅ À FAIRE

1. **Échapper tous les affichages**
   ```php
   ✅ <?php echo e($user_input); ?>
   ❌ <?php echo $user_input; ?>
   ```

2. **Vérifier l'authentification**
   ```php
   public function edit($id) {
       $this->requireLogin();  // ← Avant le code
       // ...
   }
   ```

3. **Valider les données**
   ```php
   if (empty($nom)) {
       redirect('clients.create');
       exit;
   }
   ```

### ❌ À ÉVITER

- Faire confiance à `$_GET` ou `$_POST` sans validation
- Afficher les variables sans `e()`
- Utiliser `header()` au lieu de `redirect()`
- Mettre les mots de passe en dur

---

## 🧪 Tester une Modification

Après avoir modifié un fichier :

### 1. Vérifier la Syntaxe PHP

```bash
php -l app/Controllers/ClientController.php
```

### 2. Tester dans le Navigateur

```
http://localhost:8888/Sweetydog/clients
```

### 3. Vérifier les Erreurs

```bash
# Voir les logs d'erreur
tail -f /Applications/MAMP/logs/php_error.log
```

---

## 🐛 Déboguer

### Afficher une Variable

```php
echo "<pre>";
var_dump($data);
echo "</pre>";
die();
```

### Écrire dans les Logs

```php
error_log("Debug: " . json_encode($data));
```

### Afficher le SQL Exécuté

```php
// Dans un modèle
echo "<pre>";
var_dump($query);
echo "</pre>";
```

---

## 📚 Où Trouver Plus d'Infos

| Document | Pour Qui |
|----------|----------|
| **CODE_GUIDE.md** | Comprendre l'architecture |
| **CODE_STRUCTURE.md** | Vue d'ensemble visuelle |
| **DOCUMENTATION.md** | Détail des commentaires |
| **Commentaires dans les fichiers** | Détails techniques |
| **Ce fichier** | Démarrage rapide |

---

## ✨ Prochaines Étapes

### Phase 1 : Découverte
- [ ] Lire CODE_GUIDE.md
- [ ] Explorer les fichiers du projet
- [ ] Tester les routes principales
- [ ] Se connecter et naviguer

### Phase 2 : Apprentissage
- [ ] Étudier le système de routes (Router.php)
- [ ] Comprendre le flux d'une requête
- [ ] Lire les commentaires des contrôleurs
- [ ] Modifier une vue existante

### Phase 3 : Contribution
- [ ] Ajouter une nouvelle route
- [ ] Créer un nouveau contrôleur
- [ ] Implémenter une fonction manquante
- [ ] Ajouter des tests

---

## 💡 Tips & Tricks

### Générer une URL rapidement

```php
// Au lieu de :
<a href="/clients/edit?id=5">Éditer</a>

// Faire :
<a href="<?php echo route('clients.edit', ['id' => 5]); ?>">Éditer</a>

// Avantages :
// - Majs automatiques de routes
// - Typage fort
// - Moins d'erreurs
```

### Rediriger après une action

```php
// Ancien système (éviter)
header('Location: index.php?c=client&a=liste');

// Nouveau système (utiliser)
redirect('clients.index');
```

### Créer un formulaire

```php
// Form pour create
<form action="<?php echo route('clients.store'); ?>" method="POST">
    <!-- Pas d'ID, création -->
</form>

// Form pour update
<form action="<?php echo route('clients.update', ['id' => $id]); ?>" method="POST">
    <!-- Avec ID, modification -->
</form>
```

---

## 🆘 Problèmes Courants

### "404 - Page non trouvée"

**Cause** : Route inexistante

**Solution** :
1. Vérifier que la route existe dans `app/routes.php`
2. Vérifier que le contrôleur existe
3. Vérifier que la méthode existe

### "Erreur base de données"

**Cause** : Connexion BD

**Solution** :
1. Vérifier `config/db.php`
2. Vérifier que le serveur MySQL est actif
3. Vérifier les identifiants

### "Fichier introuvable : Vue"

**Cause** : Chemin vue incorrect

**Solution** :
1. Vérifier le nom du fichier vue
2. Vérifier que le fichier existe dans `app/Views/`
3. Utiliser `$this->view('exact_name', $data);`

### "Pas d'accès après login"

**Cause** : Session ou authentification

**Solution** :
1. Vérifier que `session_start()` est appelé
2. Vérifier que les données sont créées en session
3. Vérifier les cookies navigateur

---

## 📞 Support Ressources

### Documentation PHP
- https://www.php.net/manual/fr/

### PDO (Base de données)
- https://www.php.net/manual/fr/book.pdo.php

### Regex
- https://regexpal.com/

### Apache .htaccess
- https://httpd.apache.org/docs/

---

## 🎓 Concepts Clés

| Terme | Signification |
|-------|--------------|
| **Route** | Lien entre URL et contrôleur |
| **Contrôleur** | Logique métier |
| **Vue** | Template HTML |
| **Modèle** | Requête base de données |
| **Helper** | Fonction globale |
| **Flash Message** | Message temporaire en session |
| **extract()** | Transforme array en variables |
| **e()** | Échappe pour XSS |
| **XSS** | Injection de code JavaScript |
| **PDO** | Interface base de données |

---

Vous êtes prêt ! 🚀

**Commencez par :**
1. Lire CODE_GUIDE.md
2. Explorer les fichiers
3. Faire une modification simple
4. Tester dans le navigateur

Bon courage ! 💪
