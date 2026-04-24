# Boucherie Traiteur — Back-office & API

Back-office complet de gestion de devis pour une boucherie-traiteur.  
Symfony 7 · Doctrine ORM · Twig · Tailwind CSS CDN · Alpine.js · SMTP O2switch.

---

## Fonctionnalités

- **API publique REST** : `GET /api/config` et `POST /api/devis` — appelables depuis un site vitrine externe (CORS ouvert)
- **Back-office sécurisé** : gestion des devis, prestations, tarifs, templates email
- **Emails automatiques** : confirmation client + notification boucher à chaque nouveau devis
- **Système de rôles** : `ROLE_BOUCHER` (opérationnel) / `ROLE_AGENCE` (administration complète)
- **Interactions AJAX** : changement de statut, notes internes, envoi d'email, édition de tarifs — sans rechargement
- **Design premium** : dark theme charbon/or, typographie Cormorant Garamond + DM Sans

---

## Prérequis

- **PHP 8.2+** avec extensions : `pdo_mysql`, `json`, `mbstring`, `openssl`, `intl`, `ctype`, `iconv`
- **Composer 2.x**
- **MySQL 8** ou **MariaDB** — accessible via phpMyAdmin
- Hébergement **O2switch** ou tout serveur Apache avec `mod_rewrite`

---

## Installation

### 1. Télécharger les dépendances

```bash
cd boucher-admin/
composer install --no-dev --optimize-autoloader
```

> Pour le développement local, omettez `--no-dev` pour avoir le MakerBundle.

### 2. Configurer l'environnement

```bash
cp .env.example .env.local
```

Éditez `.env.local` avec vos paramètres :

```dotenv
APP_ENV=prod
APP_SECRET=votre_secret_aleatoire_32_caracteres

DATABASE_URL="mysql://user:password@localhost:3306/nom_base?serverVersion=8.0&charset=utf8mb4"

MAILER_DSN=smtp://votre_email%40domaine.fr:motdepasse@mail.o2switch.net:587?encryption=tls&auth_mode=login

EMAIL_BOUCHER=boucher@son-domaine.fr
EMAIL_FROM=noreply@son-domaine.fr
NOM_BOUCHER="Boucherie Dupont"
```

> **Note SMTP O2switch** : l'email dans `MAILER_DSN` doit être encodé URL (`@` → `%40`).

### 3. Base de données

1. Connectez-vous à **phpMyAdmin** (O2switch → cPanel → phpMyAdmin)
2. Créez une nouvelle base de données en **utf8mb4 / utf8mb4_unicode_ci**
3. Importez le fichier `migrations/001_initial_schema.sql`

Ce fichier crée toutes les tables et insère :
- 5 prestations (mariage, brasero, traiteur, barbecue, autre) avec leurs tarifs
- 3 templates email (confirmation client, notification boucher, réponse personnalisée)
- 1 compte agence de test (`agence@boucherie.local`)

### 4. Créer le compte administrateur

**Option A — Commande interactive (recommandée)**

```bash
php bin/console app:create-user
```

Suivez les invites pour saisir email, prénom, nom, mot de passe et rôle.

**Option B — Générer un hash manuellement**

```bash
php bin/console security:hash-password votre_mot_de_passe
```

Puis insérez dans phpMyAdmin :

```sql
INSERT INTO users (email, password, roles, prenom, nom)
VALUES (
    'boucher@exemple.fr',
    '$2y$13$VOTRE_HASH_ICI',
    '["ROLE_BOUCHER"]',
    'Jean',
    'Dupont'
);
```

> **Compte de test fourni** : `agence@boucherie.local` — si la connexion échoue avec ce compte,
> régénérez son hash : `php bin/console security:hash-password agence2025`
> puis mettez à jour la colonne `password` dans phpMyAdmin.

### 5. Vider le cache

```bash
php bin/console cache:clear --env=prod
```

---

## Déploiement sur O2switch (hébergement mutualisé)

### Structure des fichiers dans cPanel

O2switch utilise Apache. Le dossier `public/` de Symfony doit être la **racine web** de votre domaine ou sous-domaine.

**Solution recommandée — Sous-domaine dédié** (ex: `admin.votre-site.fr`) :

```
/home/votre_compte/
├── boucher-admin/          ← code source (hors du public_html)
│   ├── src/
│   ├── config/
│   ├── vendor/
│   ├── ...
│   └── public/             ← cette dossier doit être la racine web
│
└── public_html/
    └── (site vitrine existant)
```

Dans cPanel → **Sous-domaines**, créez `admin.votre-site.fr` et pointez la racine vers `/home/votre_compte/boucher-admin/public`.

**Solution alternative — Sous-dossier** (ex: `votre-site.fr/admin`) :

Uploadez tout dans `/public_html/admin-app/` et créez un sous-domaine ou alias.

### Fichier `.htaccess`

Le fichier `public/.htaccess` est inclus et pré-configuré. Il gère :
- Réécriture vers `index.php` (Symfony front-controller)
- En-têtes de sécurité
- Compression gzip
- Cache navigateur pour les assets

### Upload FTP/SFTP

```
Uploadez TOUT le dossier sauf :
  - var/cache/  (sera régénéré)
  - var/log/    (optionnel)
  - .git/       (inutile en prod)

N'oubliez pas d'uploader :
  - vendor/     (après composer install local)
  - .env.local  (créé sur le serveur avec les vrais identifiants)
```

### Commandes post-déploiement (via SSH O2switch)

O2switch fournit un accès SSH. Connectez-vous et exécutez :

```bash
cd ~/boucher-admin
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

## Architecture

```
src/
├── Command/CreateUserCommand.php       # php bin/console app:create-user
├── Controller/
│   ├── Api/DevisApiController.php      # GET /api/config, POST /api/devis
│   ├── Admin/
│   │   ├── DashboardController.php
│   │   ├── DevisController.php
│   │   ├── PrestationController.php
│   │   └── EmailTemplateController.php
│   └── SecurityController.php
├── Entity/
│   ├── Devis.php
│   ├── PrestationConfig.php
│   ├── TarifConfig.php
│   ├── EmailTemplate.php
│   └── User.php
├── Repository/...
├── Service/
│   ├── DevisMailer.php                 # Envoi des emails
│   └── TarifCalculator.php            # Calcul des estimations
└── Twig/AppExtension.php              # Compteur devis "nouveaux" global
```

## API publique

| Endpoint              | Méthode | Description |
|-----------------------|---------|-------------|
| `/api/config`         | GET     | Toutes les prestations actives + tarifs |
| `/api/devis`          | POST    | Créer un nouveau devis |
| `/api/devis`          | OPTIONS | Preflight CORS |

Headers CORS : `Access-Control-Allow-Origin: *` — API ouverte pour le site vitrine.

## Rôles

| Action                        | ROLE_BOUCHER | ROLE_AGENCE |
|-------------------------------|:---:|:---:|
| Voir / traiter les devis      | ✅ | ✅ |
| Envoyer des emails aux clients| ✅ | ✅ |
| Modifier prestations & tarifs | ✅ | ✅ |
| Créer/supprimer une prestation| ❌ | ✅ |
| Réordonner les prestations    | ❌ | ✅ |
| Modifier templates email      | ❌ | ✅ |

---

## Développement local

```bash
# Lancer le serveur Symfony
symfony server:start
# ou
php -S localhost:8000 -t public/

# Créer un utilisateur
php bin/console app:create-user

# Vider le cache
php bin/console cache:clear
```

---

## Variables d'environnement — référence complète

| Variable        | Description | Exemple |
|-----------------|-------------|---------|
| `APP_ENV`       | Environnement | `prod` ou `dev` |
| `APP_SECRET`    | Clé secrète (min 32 chars, aléatoire) | `abc123...` |
| `DATABASE_URL`  | DSN MySQL Doctrine | `mysql://user:pass@localhost/db` |
| `MAILER_DSN`    | DSN SMTP | `smtp://email%40domain.fr:pass@mail.o2switch.net:587` |
| `EMAIL_BOUCHER` | Destinataire des notifications | `boucher@boutique.fr` |
| `EMAIL_FROM`    | Expéditeur des emails | `noreply@boutique.fr` |
| `NOM_BOUCHER`   | Nom affiché dans les emails | `"Boucherie Dupont"` |
