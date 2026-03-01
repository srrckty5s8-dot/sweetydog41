# SweetyDog - Documentation Technique

## Sommaire

1. [Presentation du projet](#1-presentation-du-projet)
2. [Architecture technique](#2-architecture-technique)
3. [Structure des fichiers](#3-structure-des-fichiers)
4. [Base de donnees](#4-base-de-donnees)
5. [Systeme de routage](#5-systeme-de-routage)
6. [Controleurs](#6-controleurs)
7. [Modeles](#7-modeles)
8. [Vues](#8-vues)
9. [Fonctions utilitaires (Helpers)](#9-fonctions-utilitaires-helpers)
10. [Authentification](#10-authentification)
11. [Systeme de facturation](#11-systeme-de-facturation)
12. [Dependances externes](#12-dependances-externes)
13. [Configuration](#13-configuration)
14. [Securite](#14-securite)

---

## 1. Presentation du projet

**SweetyDog** est une application web de gestion pour salon de toilettage canin. Elle permet de :

- Gerer les clients (proprietaires) et leurs animaux
- Planifier des rendez-vous via un calendrier interactif
- Enregistrer les prestations de toilettage
- Generer des factures conformes au format Factur-X
- Transmettre automatiquement les factures a N2F

**Stack technique :** PHP 8+ / MySQL / MVC custom / DomPDF / FullCalendar.js

---

## 2. Architecture technique

L'application suit le pattern **MVC (Model-View-Controller)** avec un framework custom leger.

```
Navigateur
    |
    v
public/index.php  (point d'entree unique)
    |
    v
Router::run()  (analyse l'URL, trouve la route)
    |
    v
Controller@action  (logique metier)
    |       |
    v       v
  Model   View
  (PDO)   (PHP template)
```

**Flux d'une requete :**

1. Toutes les requetes passent par `public/index.php`
2. Le `Router` analyse l'URL et la fait correspondre a une route nommee
3. Le controleur correspondant est instancie, la methode appelee
4. Le controleur interroge les modeles (base de donnees via PDO)
5. Le controleur rend une vue avec les donnees

---

## 3. Structure des fichiers

```
Sweetydog/
|
|-- app/
|   |-- Core/
|   |   |-- Router.php           # Routeur URL -> Controleur
|   |   |-- Controller.php       # Classe de base des controleurs
|   |   |-- Database.php         # Connexion PDO (singleton)
|   |
|   |-- Controllers/
|   |   |-- AuthController.php          # Connexion / Deconnexion
|   |   |-- ClientController.php        # CRUD proprietaires + animaux
|   |   |-- AnimalController.php        # Edition animal + suivi toilettage
|   |   |-- AppointmentController.php   # Gestion calendrier / RDV
|   |   |-- PrestationController.php    # Enregistrement des soins
|   |   |-- InvoiceController.php       # Generation et telechargement factures
|   |   |-- SettingsController.php      # Parametres (mot de passe)
|   |
|   |-- Models/
|   |   |-- Client.php        # Proprietaires + animaux
|   |   |-- Animal.php        # Animaux
|   |   |-- RendezVous.php    # Rendez-vous
|   |   |-- Prestation.php    # Prestations de soins
|   |   |-- Soin.php          # Historique des soins
|   |   |-- User.php          # Utilisateurs admin
|   |
|   |-- Services/
|   |   |-- PisteService.php  # Integration OAuth PISTE (gouv.fr)
|   |
|   |-- Views/
|   |   |-- login_view.php                # Page de connexion
|   |   |-- liste_clients_view.php        # Dashboard / liste clients
|   |   |-- ajouter_client_view.php       # Formulaire ajout client
|   |   |-- modifier_client_view.php      # Formulaire edition client
|   |   |-- modifier_animal_view.php      # Formulaire edition animal
|   |   |-- calendrier_view.php           # Agenda interactif
|   |   |-- suivi_toilettage_view.php     # Suivi soins + facturation
|   |   |-- facture_view.php              # Template PDF facture
|   |   |-- parametres_view.php           # Page parametres
|   |
|   |-- routes.php        # Definition de toutes les routes
|   |-- helpers.php        # Fonctions globales utilitaires
|
|-- config/
|   |-- db.php             # Configuration base de donnees
|
|-- public/
|   |-- index.php          # Point d'entree de l'application
|   |-- assets/            # CSS, JS, images
|
|-- factures/              # Factures PDF generees
|-- vendor/                # Dependances Composer
|-- composer.json          # Declaration des dependances
```

---

## 4. Base de donnees

**Nom de la base :** `mon_salon`

### Table `Proprietaires`

| Colonne           | Type         | Description              |
|-------------------|--------------|--------------------------|
| id_proprietaire   | INT (PK)     | Identifiant unique       |
| nom               | VARCHAR      | Nom de famille           |
| prenom            | VARCHAR      | Prenom                   |
| telephone         | VARCHAR      | Numero de telephone      |
| email             | VARCHAR      | Adresse email            |
| adresse           | TEXT         | Adresse postale          |

### Table `Animaux`

| Colonne           | Type         | Description                          |
|-------------------|--------------|--------------------------------------|
| id_animal         | INT (PK)     | Identifiant unique                   |
| id_proprietaire   | INT (FK)     | Reference vers Proprietaires         |
| nom_animal        | VARCHAR      | Nom de l'animal                      |
| espece            | VARCHAR      | Espece (Chien, Chat, Lapin...)       |
| race              | VARCHAR      | Race                                 |
| poids             | FLOAT        | Poids en kg                          |
| steril            | BOOLEAN      | Sterilise (0/1)                      |
| sexe              | VARCHAR      | Sexe (M/F)                           |

### Table `RendezVous`

| Colonne     | Type         | Description               |
|-------------|--------------|---------------------------|
| id_rdv      | INT (PK)     | Identifiant unique        |
| id_animal   | INT (FK)     | Reference vers Animaux    |
| titre       | VARCHAR      | Intitule du rendez-vous   |
| date_debut  | DATETIME     | Date et heure de debut    |
| date_fin    | DATETIME     | Date et heure de fin      |

### Table `Prestations`

| Colonne        | Type         | Description                    |
|----------------|--------------|--------------------------------|
| id_prestation  | INT (PK)     | Identifiant unique             |
| id_animal      | INT (FK)     | Reference vers Animaux         |
| date_soin      | DATE         | Date de la prestation          |
| type_soin      | VARCHAR      | Types de soins (separes par ,) |
| prix           | DECIMAL      | Prix facture                   |
| notes          | TEXT         | Observations                   |

### Table `Utilisateurs`

| Colonne         | Type         | Description            |
|-----------------|--------------|------------------------|
| id_utilisateur  | INT (PK)     | Identifiant unique     |
| identifiant     | VARCHAR      | Nom d'utilisateur      |
| mot_de_passe    | VARCHAR      | Hash bcrypt du MDP     |

### Relations

```
Proprietaires (1) ---< (N) Animaux
Animaux       (1) ---< (N) RendezVous
Animaux       (1) ---< (N) Prestations
```

---

## 5. Systeme de routage

Les routes sont definies dans `app/routes.php` et gerees par `app/Core/Router.php`.

### Liste des routes

| Nom                  | Methode  | URL                          | Action                           |
|----------------------|----------|------------------------------|----------------------------------|
| home                 | GET      | /                            | Redirection accueil              |
| login                | GET/POST | /auth/login                  | Connexion                        |
| logout               | GET      | /auth/logout                 | Deconnexion                      |
| clients.index        | GET      | /clients                     | Liste des clients                |
| clients.create       | GET      | /clients/new                 | Formulaire nouveau client        |
| clients.store        | POST     | /clients                     | Enregistrer nouveau client       |
| clients.edit         | GET      | /clients/{id}/edit           | Formulaire edition client        |
| clients.update       | POST     | /clients/{id}                | Mettre a jour un client          |
| clients.delete       | POST     | /clients/{id}/delete         | Supprimer un client              |
| animals.edit         | GET      | /animals/{id}/edit           | Formulaire edition animal        |
| animals.update       | POST     | /animals/{id}                | Mettre a jour un animal          |
| animals.tracking     | GET      | /animals/{id}/tracking       | Suivi toilettage de l'animal     |
| appointments.index   | GET      | /appointments                | Calendrier des RDV               |
| appointments.create  | POST     | /appointments                | Creer un RDV                     |
| appointments.update  | POST     | /appointments/{id}           | Modifier un RDV                  |
| appointments.delete  | POST     | /appointments/{id}/delete    | Supprimer un RDV                 |
| settings.index       | GET/POST | /settings                    | Parametres                       |
| invoices.generate    | GET      | /invoices/{id}/generate      | Generer une facture              |
| invoices.download    | GET      | /invoices/{id}/download      | Telecharger une facture          |
| prestations.store    | POST     | /animals/{id}/prestations    | Enregistrer une prestation       |

### Generation d'URL

```php
// Generer une URL a partir du nom de la route
route('clients.edit', ['id' => 5])   // => /clients/5/edit
route('clients.index')               // => /clients

// Redirection
redirect('clients.index');
redirect('clients.edit', ['id' => 5], ['success' => '1']);
```

---

## 6. Controleurs

Tous les controleurs heritent de `Controller` (app/Core/Controller.php) qui fournit :
- `view($name, $data)` : rendre une vue avec des variables
- `requireLogin()` : verifier l'authentification

### AuthController

| Methode          | Description                                           |
|------------------|-------------------------------------------------------|
| `redirectHome()` | Redirige vers le dashboard ou la page de connexion    |
| `login()`        | GET : affiche le formulaire / POST : authentification |
| `logout()`       | Detruit la session et redirige vers login             |

### ClientController

| Methode        | Description                                                  |
|----------------|--------------------------------------------------------------|
| `index()`      | Affiche la liste des clients avec recherche                  |
| `create()`     | Affiche le formulaire d'ajout (nouveau ou existant)          |
| `store()`      | Enregistre un nouveau proprietaire + animal                  |
| `edit($id)`    | Affiche le formulaire de modification d'un client            |
| `update($id)`  | Met a jour les informations d'un client                      |

### AnimalController

| Methode          | Description                                      |
|------------------|--------------------------------------------------|
| `edit($id)`      | Affiche le formulaire de modification             |
| `update($id)`    | Met a jour les informations de l'animal           |
| `tracking($id)`  | Affiche le suivi toilettage + historique soins     |

### AppointmentController

| Methode        | Description                          |
|----------------|--------------------------------------|
| `index()`      | Affiche le calendrier FullCalendar   |
| `create()`     | Cree un nouveau rendez-vous          |
| `update($id)`  | Modifie un rendez-vous existant      |
| `delete($id)`  | Supprime un rendez-vous              |

### PrestationController

| Methode       | Description                                           |
|---------------|-------------------------------------------------------|
| `store($id)`  | Enregistre une prestation et lance la facturation      |

### InvoiceController

| Methode          | Description                                               |
|------------------|-----------------------------------------------------------|
| `generate($id)`  | Genere le PDF Factur-X et l'envoie a N2F                  |
| `download($id)`  | Telecharge une facture existante                          |

### SettingsController

| Methode    | Description                                    |
|------------|------------------------------------------------|
| `index()`  | Affiche les parametres / change le mot de passe |

---

## 7. Modeles

Tous les modeles utilisent des methodes statiques avec PDO et requetes preparees.

### Client

| Methode                      | Retour   | Description                              |
|------------------------------|----------|------------------------------------------|
| `getAllWithAnimaux($search)`  | array    | Tous les clients + animaux (filtrable)   |
| `getAllProprietaires()`       | array    | Liste de tous les proprietaires          |
| `createProprietaire($data)`  | int      | Creer un proprietaire (retourne l'ID)    |
| `createAnimal($data)`        | int      | Creer un animal (retourne l'ID)          |
| `findProprietaire($id)`      | ?array   | Trouver un proprietaire par ID           |
| `updateProprietaire($id, $data)` | bool | Mettre a jour un proprietaire            |

### Animal

| Methode                      | Retour   | Description                              |
|------------------------------|----------|------------------------------------------|
| `getListForAppointments()`   | array    | Liste des animaux pour le calendrier     |
| `findWithOwner($id)`         | ?array   | Animal + infos proprietaire              |
| `update($id, $data)`         | bool     | Mettre a jour un animal                  |
| `delete($id)`                | bool     | Supprimer un animal                      |

### RendezVous

| Methode                  | Retour   | Description                              |
|--------------------------|----------|------------------------------------------|
| `getToday()`             | array    | RDV du jour                              |
| `getCalendarEvents()`    | array    | Tous les RDV (format FullCalendar)       |
| `create($data)`          | int      | Creer un RDV                             |
| `getById($id)`           | ?array   | Trouver un RDV par ID                    |
| `update($id, $data)`     | bool     | Modifier un RDV                          |
| `delete($id)`            | bool     | Supprimer un RDV                         |

### Prestation

| Methode           | Retour   | Description                        |
|-------------------|----------|------------------------------------|
| `create($data)`   | int      | Enregistrer une prestation         |

### Soin

| Methode                | Retour   | Description                            |
|------------------------|----------|----------------------------------------|
| `findByAnimal($id)`    | array    | Historique des soins d'un animal       |

### User

| Methode                    | Retour   | Description                        |
|----------------------------|----------|------------------------------------|
| `findById($id)`            | ?array   | Trouver un utilisateur par ID      |
| `updatePassword($id, $hash)` | bool  | Mettre a jour le mot de passe      |

---

## 8. Vues

| Fichier                        | Route                     | Description                                   |
|--------------------------------|---------------------------|-----------------------------------------------|
| `login_view.php`               | /auth/login               | Formulaire de connexion admin                 |
| `liste_clients_view.php`       | /clients                  | Dashboard : liste clients + RDV du jour       |
| `ajouter_client_view.php`      | /clients/new              | Ajout client (nouveau ou existant) + animal   |
| `modifier_client_view.php`     | /clients/{id}/edit        | Edition des infos proprietaire                |
| `modifier_animal_view.php`     | /animals/{id}/edit        | Edition des infos animal                      |
| `calendrier_view.php`          | /appointments             | Agenda interactif (FullCalendar)              |
| `suivi_toilettage_view.php`    | /animals/{id}/tracking    | Suivi soins + formulaire nouvelle visite      |
| `facture_view.php`             | (interne)                 | Template HTML pour generation PDF             |
| `parametres_view.php`          | /settings                 | Changement de mot de passe                    |

---

## 9. Fonctions utilitaires (Helpers)

Fichier : `app/helpers.php` - charge automatiquement via Composer.

| Fonction                             | Description                                      |
|--------------------------------------|--------------------------------------------------|
| `route($name, $params)`             | Genere une URL a partir d'un nom de route         |
| `url($path)`                         | Genere une URL absolue pour un asset              |
| `redirect($route, $params, $query)` | Redirige vers une route et arrete le script       |
| `currentUrl()`                       | Retourne l'URL courante                           |
| `isCurrentRoute($name)`             | Verifie si la route courante correspond           |
| `param($key, $default)`             | Recupere un parametre GET/POST en securite        |
| `e($value)`                          | Echappe le HTML (protection XSS)                 |
| `flashMessage($type, $msg)`         | Stocke un message flash en session                |
| `getFlashMessage($type)`            | Recupere et supprime un message flash             |
| `getAllFlashMessages()`              | Recupere et supprime tous les messages flash      |
| `can($permission)`                   | Verifie une permission (stub)                    |

---

## 10. Authentification

Le systeme utilise les **sessions PHP**.

**Processus de connexion :**

1. L'utilisateur saisit identifiant + mot de passe
2. Verification en base avec `password_verify()` (bcrypt)
3. En cas de succes, variables de session definies :
   - `$_SESSION['admin_connecte']` = true
   - `$_SESSION['admin_id']` = ID utilisateur
   - `$_SESSION['admin_nom']` = nom d'utilisateur
4. Redirection vers `/clients`

**Protection des routes :**

Chaque controleur appelle `$this->requireLogin()` en debut de methode. Si l'utilisateur n'est pas connecte, il est redirige vers `/auth/login`.

**Deconnexion :** `session_destroy()` puis redirection vers login.

---

## 11. Systeme de facturation

### Flux complet

```
Suivi toilettage (/animals/{id}/tracking)
    |
    v
Formulaire "Nouvelle visite"
(date, soins, prix, notes)
    |
    v
PrestationController@store
(enregistre en BDD)
    |
    v
InvoiceController@generate
    |
    |-- 1. Recupere les donnees (prestation + animal + proprietaire)
    |-- 2. Genere le HTML depuis facture_view.php
    |-- 3. Convertit en PDF via DomPDF
    |-- 4. Cree le XML Factur-X
    |-- 5. Combine PDF + XML (atgp/factur-x)
    |-- 6. Sauvegarde dans /factures/
    |-- 7. Envoie a N2F via API
    |
    v
Redirection vers suivi avec message de succes
+ telechargement automatique du PDF
```

### Format des factures

- **Nom de fichier :** `Facture_SweetyDog_{annee}-{id_prestation}.pdf`
- **Format :** PDF avec XML Factur-X embarque (norme francaise)
- **Stockage :** dossier `/factures/` a la racine du projet

### Integration N2F

- Cle API stockee dans `code.env` (`N2F_API_KEY`)
- Envoi via CURL en multipart/form-data
- Header : `Authorization: ApiKey {cle}`

---

## 12. Dependances externes

### PHP (Composer)

| Package          | Version | Usage                            |
|------------------|---------|----------------------------------|
| atgp/factur-x    | ^2.5    | Generation factures Factur-X     |
| dompdf/dompdf    | ^3.1    | Conversion HTML vers PDF         |

### JavaScript / CSS (CDN)

| Bibliotheque     | Version | Usage                            |
|------------------|---------|----------------------------------|
| FullCalendar     | 6.1.10  | Calendrier interactif            |
| Font Awesome     | 6.5.0   | Icones                           |

---

## 13. Configuration

### Base de donnees (`config/db.php`)

| Parametre | Valeur     |
|-----------|------------|
| Host      | localhost  |
| Database  | mon_salon  |
| User      | root       |
| Password  | root       |

### Autoloading (`composer.json`)

Les classes sont chargees automatiquement via le classmap Composer :
- `app/Core/`
- `app/Controllers/`
- `app/Models/`
- `app/Services/`
- `app/helpers.php` (fichier auto-inclus)

---

## 14. Securite

| Mesure                     | Implementation                                        |
|----------------------------|-------------------------------------------------------|
| Injection SQL              | Requetes preparees PDO avec parametres nommes         |
| XSS                        | `htmlspecialchars()` via le helper `e()`              |
| Mots de passe              | Hash bcrypt (`password_hash` / `password_verify`)     |
| Sessions                   | `session_destroy()` a la deconnexion                  |
| Telechargement factures    | Verification d'authentification avant acces           |
| Donnees formulaires        | Validation et nettoyage (`trim`, cast, controles)     |
