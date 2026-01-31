# 📇 INDEX COMPLET DE SWEETYDOG

Guide de tous les fichiers du projet avec leurs emplacements et responsabilités.

---

## 📂 Structure Complète

### 🏢 Racine du Projet
```
/Sweetydog/
│
├── 📄 QUICKSTART.md           ← LIRE EN PREMIER ! (Ce fichier)
├── 📄 CODE_GUIDE.md           ← Guide complet de l'architecture
├── 📄 CODE_STRUCTURE.md       ← Vue d'ensemble visuelle
├── 📄 DOCUMENTATION.md        ← Détail des commentaires
├── 📄 INDEX.md                ← Ce fichier
│
├── 📁 public/                 ← Dossier web (serveur Apache pointe ici)
│   ├── 📄 index.php           ← Point d'entrée UNIQUE de l'app
│   ├── 📄 .htaccess           ← URL rewriting (/.htaccess depuis racine)
│   └── 📁 assets/             ← Ressources publiques
│       └── 📄 style.css       ← Feuille de styles principale
│
├── 📁 app/                    ← Code métier de l'application
│
│   ├── 📁 Core/               ← Framework interne
│   │   ├── 📄 Router.php      ← Moteur de routage ⭐
│   │   ├── 📄 Controller.php  ← Classe de base pour contrôleurs
│   │   └── 📄 Database.php    ← Connexion à la BD
│   │
│   ├── 📁 Controllers/        ← Logique métier (CRUD)
│   │   ├── 📄 AuthController.php      ← Login/Logout
│   │   ├── 📄 ClientController.php    ← Gestion clients
│   │   └── 📄 AnimalController.php    ← Gestion animaux
│   │
│   ├── 📁 Models/             ← Requêtes base de données
│   │   ├── 📄 Client.php      ← Modèle Propriétaire
│   │   ├── 📄 Animal.php      ← Modèle Animal
│   │   └── 📄 RendezVous.php  ← Modèle Rendez-vous
│   │
│   ├── 📄 routes.php          ← Configuration des routes ⭐
│   └── 📄 helpers.php         ← Fonctions globales ⭐
│
├── 📁 app/Views/                  ← Templates HTML+PHP
│   ├── 📄 login_view.php      ← Formulaire de connexion
│   ├── 📄 liste_clients_view.php    ← Dashboard principal
│   ├── 📄 ajouter_client_view.php   ← Créer client
│   ├── 📄 modifier_client_view.php  ← Modifier client
│   ├── 📄 modifier_animal_view.php  ← Modifier animal
│   ├── 📄 calendrier_view.php       ← Calendrier rendez-vous
│   ├── 📄 facture_view.php          ← Génération factures
│   ├── 📄 parametres_view.php       ← Paramètres app
│   └── 📄 suivi_toilettage_view.php ← Historique soins
│
├── 📁 config/                 ← Configuration
│   └── 📄 db.php              ← Connexion base de données
│
└── 📁 factures/               ← Fichiers factures générées

```

---

## 🎯 Fichiers Par Catégorie

### 🌟 Fichiers Essentiels (À Connaître)

| Fichier | Rôle | Priorité |
|---------|------|----------|
| `public/index.php` | Point d'entrée | 🔴 CRITIQUE |
| `app/routes.php` | Config des routes | 🔴 CRITIQUE |
| `app/Core/Router.php` | Moteur de routage | 🔴 CRITIQUE |
| `app/helpers.php` | Fonctions globales | 🔴 CRITIQUE |
| `app/Controllers/AuthController.php` | Authentification | 🟠 Majeur |
| `app/Controllers/ClientController.php` | Clients CRUD | 🟠 Majeur |
| `app/Views/liste_clients_view.php` | Dashboard | 🟠 Majeur |

### 📖 Fichiers de Documentation (À Lire)

| Fichier | Contenu |
|---------|---------|
| **QUICKSTART.md** | Démarrage rapide en 5 min |
| **CODE_GUIDE.md** | Guide complet d'architecture |
| **CODE_STRUCTURE.md** | Vue d'ensemble visuelle |
| **DOCUMENTATION.md** | Résumé des commentaires |
| **INDEX.md** | Ce fichier (référence) |

### 🔧 Fichiers de Configuration

| Fichier | Utilité |
|---------|---------|
| `config/db.php` | Identifiants base de données |
| `public/.htaccess` | URL rewriting |
| `.htaccess` (racine) | Redirect vers /public |

### 🎨 Fichiers Front-end

| Fichier | Rôle |
|---------|------|
| `app/Views/login_view.php` | Page de login |
| `app/Views/liste_clients_view.php` | Dashboard principal |
| `assets/style.css` | Styles CSS |

### 🗄️ Modèles (Base de Données)

| Fichier | Représente |
|---------|-----------|
| `app/Models/Client.php` | Propriétaires |
| `app/Models/Animal.php` | Animaux |
| `app/Models/RendezVous.php` | Rendez-vous |

---

## 📊 Dépendances Entre Fichiers

```
public/index.php
    ↓ require
    ├→ app/helpers.php
    │   └→ Utilise : Router instance
    │
    └→ app/Core/Router.php
        ├→ Charge : app/routes.php
        └→ Instancie : Controllers (auto-load)
            │
            ├→ app/Controllers/AuthController.php
            │   ├→ Hérite : app/Core/Controller.php
            │   └→ Utilise : app/helpers.php
            │
            ├→ app/Controllers/ClientController.php
            │   ├→ Hérite : app/Core/Controller.php
            │   ├→ Utilise : app/Models/Client.php
            │   ├→ Utilise : app/Models/RendezVous.php
            │   └→ Affiche : app/Views/*_view.php
            │
            └→ app/Controllers/AnimalController.php
                ├→ Hérite : app/Core/Controller.php
                ├→ Utilise : app/Models/Animal.php
                └→ Affiche : app/Views/*_view.php

app/Views/*_view.php
    ├→ Utilise : app/helpers.php (route, e, etc)
    ├→ Inclut : assets/style.css
    └→ Reçoit : Données extraites par extract()
```

---

## 🔄 Flux Par Fonctionnalité

### 🔐 Authentification
```
GET /auth/login
    ↓
public/index.php::Router::run()
    ↓
AuthController::login()
    ↓
view('login_view')

POST /auth/login (formulaire)
    ↓
AuthController::login() POST
    ↓
password_verify() + $_SESSION
    ↓
redirect('clients.index')
```

### 👥 Gestion Clients
```
GET /clients
    ↓
ClientController::index() → liste()
    ↓
Client::getAllWithAnimaux() (models)
    ↓
view('liste_clients_view', [$clients, $rdv])
    ↓
Utilise route() pour générer les URLs

GET /clients/new
    ↓
ClientController::create()
    ↓
view('ajouter_client_view')

POST /clients
    ↓
ClientController::store()
    ↓
Client::createProprietaire() + Client::createAnimal()
    ↓
redirect('clients.index')
```

---

## 🎓 Apprentissage Recommandé

### Pour les Débutants

**Jour 1 :**
- [ ] Lire QUICKSTART.md
- [ ] Lire CODE_GUIDE.md
- [ ] Explorer l'arborescence

**Jour 2 :**
- [ ] Lire les commentaires de public/index.php
- [ ] Comprendre le flux de requête
- [ ] Tracer une action (ex: /clients)

**Jour 3 :**
- [ ] Lire Router.php
- [ ] Comprendre les routes nommées
- [ ] Étudier les helpers

**Jour 4 :**
- [ ] Lire un contrôleur complet
- [ ] Comprendre CRUD
- [ ] Étudier les vues

**Jour 5 :**
- [ ] Implémenter une fonction simple
- [ ] Tester dans le navigateur
- [ ] Déboguer un problème

### Pour les Avancés

- [ ] Implémenter les méthodes manquantes (delete, tracking)
- [ ] Créer un nouveau contrôleur (SettingsController)
- [ ] Ajouter un système de permissions
- [ ] Implémenter les caches
- [ ] Ajouter des tests unitaires

---

## 🔍 Où Chercher...

### Pour ajouter une nouvelle route
→ `app/routes.php`

### Pour ajouter la logique
→ `app/Controllers/XxxController.php`

### Pour modifier l'affichage
→ `app/Views/xxx_view.php`

### Pour la base de données
→ `app/Models/Xxx.php`

### Pour les styles
→ `assets/style.css`

### Pour les fonctions globales
→ `app/helpers.php`

### Pour le moteur de routage
→ `app/Core/Router.php`

### Pour la configuration
→ `config/db.php`

---

## 📋 Checklist Avant Modifications

- [ ] Ai-je lu les commentaires du fichier ?
- [ ] Ai-je compris l'architecture générale ?
- [ ] Vais-je suivre les patterns existants ?
- [ ] Ai-je testé ma modification ?
- [ ] Ai-je vérifiée la syntaxe PHP ?
- [ ] Ai-je utilisé les helpers existants ?
- [ ] Ai-je échappé les données affichées ?
- [ ] Ai-je validé les entrées ?

---

## 🆘 Débogage - Où Regarder

| Problème | Fichier à Vérifier |
|---------|-------------------|
| URL ne fonctionne pas | `app/routes.php` |
| Contrôleur non trouvé | `app/Controllers/` |
| Vue non trouvée | `app/Views/` |
| Données non affichées | `app/Controllers/` + `app/Views/` |
| Erreur base de données | `app/Models/` + `config/db.php` |
| Erreur SQL | `app/Models/` |
| Erreur d'authentification | `app/Controllers/AuthController.php` |
| Styles non appliqués | `assets/style.css` |
| Erreur 404 | `.htaccess` ou `public/index.php` |

---

## 📈 Taille du Projet

| Catégorie | Fichiers | Lignes |
|-----------|----------|--------|
| Configuration | 2 | 50 |
| Framework Core | 3 | 350 |
| Contrôleurs | 3 | 200 |
| Modèles | 3 | 300 |
| Vues | 8 | 500 |
| Documentation | 5 | 1000+ |
| **Total** | **27** | **2400+** |

---

## 🎯 Objectifs d'Apprentissage

Après avoir explorer le projet, vous devriez pouvoir :

✅ Comprendre le flux d'une requête HTTP
✅ Créer une nouvelle route
✅ Implémenter un contrôleur simple
✅ Créer une vue avec les helpers
✅ Faire une requête base de données
✅ Utiliser le système d'authentification
✅ Déboguer un problème courant
✅ Ajouter une nouvelle fonctionnalité

---

## 🔗 Liens Rapides

| Besoin | Aller à |
|--------|---------|
| Démarrer | QUICKSTART.md |
| Comprendre l'archi | CODE_GUIDE.md |
| Vue visuelle | CODE_STRUCTURE.md |
| Détail commentaires | DOCUMENTATION.md |
| Toutes les routes | app/routes.php |
| Tous les helpers | app/helpers.php |
| Moteur routage | app/Core/Router.php |

---

## 💪 Bonnes Pratiques à Retenir

### Routing
```php
✅ route('clients.edit', ['id' => 5])
❌ 'clients.php?id=5'
```

### Affichage
```php
✅ <?php echo e($data); ?>
❌ <?php echo $data; ?>
```

### Redirection
```php
✅ redirect('clients.index');
❌ header('Location: /clients');
```

### Validation
```php
✅ if (empty($nom)) die("Erreur");
❌ $nom = $_POST['nom'];
```

---

## 🎊 Fin de l'Index

Vous avez maintenant une **compréhension complète de la structure du projet** !

**Prochains pas :**
1. Lire QUICKSTART.md
2. Explorer les fichiers
3. Faire une modification
4. Tester dans le navigateur

**Bon courage !** 🚀

---

**Version** : 1.0
**Date** : 2024
**Auteur** : Équipe Sweetydog
**Licencé sous** : MIT
