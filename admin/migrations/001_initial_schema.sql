-- ============================================================
--  Boucherie Traiteur — Schéma initial + données de test
--  Import via phpMyAdmin (utf8mb4_unicode_ci)
--  Généré pour MySQL 8 / MariaDB
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
--  TABLE : users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(180)    NOT NULL,
    `password`   VARCHAR(255)    NOT NULL,
    `roles`      JSON            NOT NULL,
    `prenom`     VARCHAR(100)    NOT NULL,
    `nom`        VARCHAR(100)    NOT NULL,
    `actif`      TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  TABLE : prestations_config
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prestations_config` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `slug`        VARCHAR(50)     NOT NULL,
    `nom`         VARCHAR(150)    NOT NULL,
    `description` TEXT            NOT NULL,
    `icone`       VARCHAR(10)     NOT NULL DEFAULT '🍖',
    `actif`       TINYINT(1)      NOT NULL DEFAULT 1,
    `ordre`       SMALLINT        NOT NULL DEFAULT 0,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  TABLE : tarifs_config
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tarifs_config` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `prestation_id`   INT UNSIGNED    NOT NULL,
    `label`           VARCHAR(150)    NOT NULL,
    `description`     VARCHAR(255)    NULL,
    `prix_base`       DECIMAL(8,2)    NOT NULL DEFAULT 0.00,
    `unite`           ENUM('par_personne','forfait','au_kg') NOT NULL DEFAULT 'par_personne',
    `min_personnes`   SMALLINT        NULL,
    `max_personnes`   SMALLINT        NULL,
    `actif`           TINYINT(1)      NOT NULL DEFAULT 1,
    `ordre`           SMALLINT        NOT NULL DEFAULT 0,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `FK_tarif_prestation` FOREIGN KEY (`prestation_id`)
        REFERENCES `prestations_config` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  TABLE : devis
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `devis` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `reference`        VARCHAR(20)     NOT NULL,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Prestation
    `prestation_slug`  VARCHAR(50)     NOT NULL,
    `prestation_nom`   VARCHAR(150)    NOT NULL,
    `nb_personnes`     SMALLINT        NOT NULL,
    `date_evenement`   DATE            NULL,
    `lieu`             ENUM('sur_place','livraison','a_definir') NOT NULL DEFAULT 'a_definir',
    `lieu_detail`      VARCHAR(255)    NULL,
    `options`          JSON            NULL,
    -- Estimation
    `estimation_min`   DECIMAL(10,2)   NULL,
    `estimation_max`   DECIMAL(10,2)   NULL,
    -- Client
    `client_nom`       VARCHAR(100)    NOT NULL,
    `client_prenom`    VARCHAR(100)    NOT NULL,
    `client_email`     VARCHAR(180)    NOT NULL,
    `client_telephone` VARCHAR(20)     NOT NULL,
    `client_message`   TEXT            NULL,
    -- Admin
    `statut`           ENUM('nouveau','lu','en_attente','accepte','refuse') NOT NULL DEFAULT 'nouveau',
    `notes_admin`      TEXT            NULL,
    `traite_par`       INT UNSIGNED    NULL,
    `token_acces`      VARCHAR(64)     NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_reference`   (`reference`),
    UNIQUE KEY `UNIQ_token_acces` (`token_acces`),
    CONSTRAINT `FK_devis_user` FOREIGN KEY (`traite_par`)
        REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  TABLE : email_templates
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_templates` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `slug`       VARCHAR(50)     NOT NULL,
    `nom`        VARCHAR(150)    NOT NULL,
    `sujet`      VARCHAR(255)    NOT NULL,
    `corps`      LONGTEXT        NOT NULL,
    `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  DONNÉES INITIALES
-- ============================================================

-- ------------------------------------------------------------
--  Prestations
-- ------------------------------------------------------------
INSERT INTO `prestations_config` (`slug`, `nom`, `description`, `icone`, `actif`, `ordre`) VALUES
('mariage',  'Mariage & Réceptions',     'Sublimez votre grand jour avec nos viandes d\'exception et nos plateaux de charcuterie artisanale. Cocktails dînatoires, buffets raffinés, service traiteur sur mesure pour tous vos événements nuptiaux.',    '💍', 1, 10),
('brasero',  'Brasero & Grillade',       'L\'art de la braise dans toute sa splendeur. Côtes de boeuf, agneau entier, merguez maison... Nous apportons notre équipement professionnel et notre savoir-faire pour un show culinaire inoubliable.', '🔥', 1, 20),
('traiteur', 'Traiteur Événementiel',    'Des repas d\'entreprise aux anniversaires haut de gamme, notre équipe compose des menus équilibrés et raffinés. Entrées, plats, desserts — entièrement préparés dans notre laboratoire artisanal.',           '🍽️', 1, 30),
('barbecue', 'Barbecue Géant',           'Formules barbecue pour vos fêtes de quartier, kermesses et événements familiaux. Quantités généreuses, viandes soigneusement sélectionnées, ambiance conviviale garantie.',                              '🥩', 1, 40),
('autre',    'Autre Prestation',         'Vous avez un projet particulier ? Une demande spécifique ? Décrivez-nous votre événement et nous élaborerons une proposition entièrement sur mesure.',                                                    '✨', 1, 50);

-- ------------------------------------------------------------
--  Tarifs — Mariage
-- ------------------------------------------------------------
INSERT INTO `tarifs_config` (`prestation_id`, `label`, `description`, `prix_base`, `unite`, `min_personnes`, `max_personnes`, `actif`, `ordre`) VALUES
(1, 'Cocktail dînatoire',             'Verrines, planches charcuterie & fromage, mini-brochettes, toasts garnis',          28.50, 'par_personne', 20,  NULL, 1, 10),
(1, 'Buffet charcuterie & fromagerie','Sélection de 8 charcuteries, 6 fromages affinés, pains artisanaux, condiments',      22.00, 'par_personne', 30,  NULL, 1, 20),
(1, 'Formule complète',               'Cocktail dînatoire + buffet + service à table pour entrée et plat principal',        48.00, 'par_personne', 50,  NULL, 1, 30),
(1, 'Supplément livraison & mise en place', 'Livraison, installation, présentation et desserte en fin de réception',       180.00, 'forfait',      NULL, NULL, 1, 40);

-- ------------------------------------------------------------
--  Tarifs — Brasero
-- ------------------------------------------------------------
INSERT INTO `tarifs_config` (`prestation_id`, `label`, `description`, `prix_base`, `unite`, `min_personnes`, `max_personnes`, `actif`, `ordre`) VALUES
(2, 'Formule classique',  'Bœuf (400g/pers), agneau, merguez maison, saucisses, pain & sauces',   18.50, 'par_personne', 15,  NULL, 1, 10),
(2, 'Formule premium',   'Côte de boeuf, entrecôte maturée, côtelettes d\'agneau + accompagnements', 32.00, 'par_personne', 10,  NULL, 1, 20),
(2, 'Location brasero',  'Location du brasero professionnel (sans viandes) + livraison/reprise',   85.00, 'forfait',      NULL, NULL, 1, 30),
(2, 'Supplément fuel',   'Charbon de qualité, allumage et gestion du feu pendant tout l\'événement',45.00, 'forfait',      NULL, NULL, 1, 40);

-- ------------------------------------------------------------
--  Tarifs — Traiteur
-- ------------------------------------------------------------
INSERT INTO `tarifs_config` (`prestation_id`, `label`, `description`, `prix_base`, `unite`, `min_personnes`, `max_personnes`, `actif`, `ordre`) VALUES
(3, 'Menu entrée-plat',          'Entrée froide + plat chaud (viande rôtie ou braisée) + pain',       26.00, 'par_personne', 15,  NULL, 1, 10),
(3, 'Menu complet 3 temps',      'Entrée + plat + dessert maison. Livraison en contenants isothermes', 38.00, 'par_personne', 20,  NULL, 1, 20),
(3, 'Plateau charcuterie (1kg)', 'Jambon cru, saucisson sec, rillettes, terrine, cornichons',          28.00, 'au_kg',        NULL, NULL, 1, 30),
(3, 'Service avec personnel',    'Serveur(s) pour la soirée (par tranche de 3h)',                     120.00, 'forfait',      NULL, NULL, 1, 40);

-- ------------------------------------------------------------
--  Tarifs — Barbecue
-- ------------------------------------------------------------
INSERT INTO `tarifs_config` (`prestation_id`, `label`, `description`, `prix_base`, `unite`, `min_personnes`, `max_personnes`, `actif`, `ordre`) VALUES
(4, 'Pack BBQ essentiel (400g)',  'Merguez, chipolatas, côtelettes, brochettes bœuf-mouton',           14.50, 'par_personne', 20,  NULL, 1, 10),
(4, 'Pack BBQ généreux (600g)',   'Entrecôte, saucisses premium, kebab, brochettes mixtes + sauces',   22.00, 'par_personne', 15,  NULL, 1, 20),
(4, 'Pack BBQ tout compris',      'Viandes + salades, pain, condiments, vaisselle jetable biodégrad.',  30.00, 'par_personne', 25, 300, 1, 30);

-- ------------------------------------------------------------
--  Tarifs — Autre
-- ------------------------------------------------------------
INSERT INTO `tarifs_config` (`prestation_id`, `label`, `description`, `prix_base`, `unite`, `min_personnes`, `max_personnes`, `actif`, `ordre`) VALUES
(5, 'Prestation sur mesure', 'Prix établi après étude de votre projet. Contactez-nous pour un devis personnalisé.', 0.00, 'forfait', NULL, NULL, 1, 10);

-- ------------------------------------------------------------
--  Templates email
-- ------------------------------------------------------------
INSERT INTO `email_templates` (`slug`, `nom`, `sujet`, `corps`) VALUES

('confirmation_client',
 'Confirmation client — réception de la demande',
 'Votre demande de devis {{reference}} est bien reçue — {{nom_boucher}}',
 '<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f5f0e8;font-family:Georgia,serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0e8;padding:40px 20px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
      <!-- Header -->
      <tr><td style="background:#111110;padding:32px 40px;text-align:center;">
        <h1 style="margin:0;color:#c8932a;font-family:Georgia,serif;font-size:26px;font-weight:normal;letter-spacing:2px;">{{nom_boucher}}</h1>
        <p style="margin:8px 0 0;color:#888;font-size:12px;letter-spacing:3px;text-transform:uppercase;">Boucherie &amp; Traiteur</p>
      </td></tr>
      <!-- Body -->
      <tr><td style="padding:40px;color:#333;font-size:15px;line-height:1.7;">
        <p style="margin:0 0 20px;">Bonjour <strong>{{client_prenom}}</strong>,</p>
        <p style="margin:0 0 20px;">Nous avons bien reçu votre demande de devis et nous vous en remercions.</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f6f1;border-left:3px solid #c8932a;margin:24px 0;border-radius:0 4px 4px 0;">
          <tr><td style="padding:20px 24px;">
            <p style="margin:0 0 8px;font-size:13px;color:#888;text-transform:uppercase;letter-spacing:1px;">Référence de votre demande</p>
            <p style="margin:0;font-size:22px;color:#111110;font-weight:bold;letter-spacing:2px;">{{reference}}</p>
          </td></tr>
        </table>
        <table width="100%" cellpadding="8" cellspacing="0" style="margin:0 0 24px;font-size:14px;">
          <tr><td style="color:#888;width:45%;">Prestation :</td><td><strong>{{prestation_nom}}</strong></td></tr>
          <tr><td style="color:#888;">Nombre de personnes :</td><td><strong>{{nb_personnes}} personnes</strong></td></tr>
          <tr><td style="color:#888;">Date souhaitée :</td><td><strong>{{date_evenement}}</strong></td></tr>
          <tr><td style="color:#888;">Estimation :</td><td><strong>{{estimation_min}} – {{estimation_max}}</strong></td></tr>
        </table>
        <p style="margin:0 0 20px;">Notre équipe étudiera votre demande et reviendra vers vous <strong>sous 24 à 48 heures ouvrées</strong>.</p>
        <p style="margin:0;">Cordialement,<br><strong>L'équipe {{nom_boucher}}</strong></p>
      </td></tr>
      <!-- Footer -->
      <tr><td style="background:#f5f0e8;padding:20px 40px;text-align:center;font-size:12px;color:#aaa;">
        <p style="margin:0;">Cet email est un message automatique, merci de ne pas y répondre directement.</p>
        <p style="margin:4px 0 0;">© {{annee}} {{nom_boucher}} — Tous droits réservés</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>'),

('notification_boucher',
 'Notification boucher — nouvelle demande',
 'Nouveau devis {{reference}} — {{prestation_nom}} ({{nb_personnes}} pers.)',
 '<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f5f0e8;font-family:Georgia,serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0e8;padding:40px 20px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
      <tr><td style="background:#111110;padding:28px 40px;">
        <h1 style="margin:0;color:#c8932a;font-family:Georgia,serif;font-size:20px;font-weight:normal;">🔔 Nouvelle demande de devis</h1>
        <p style="margin:6px 0 0;color:#888;font-size:13px;">Référence : <strong style="color:#c8932a;">{{reference}}</strong></p>
      </td></tr>
      <tr><td style="padding:40px;color:#333;font-size:15px;line-height:1.7;">
        <h2 style="margin:0 0 20px;font-size:17px;color:#111110;">Informations client</h2>
        <table width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;margin-bottom:28px;">
          <tr><td style="color:#888;width:40%;">Nom :</td><td><strong>{{client_prenom}} {{client_nom}}</strong></td></tr>
          <tr><td style="color:#888;">Email :</td><td><a href="mailto:{{client_email}}" style="color:#c8932a;">{{client_email}}</a></td></tr>
          <tr><td style="color:#888;">Téléphone :</td><td><a href="tel:{{client_telephone}}" style="color:#c8932a;">{{client_telephone}}</a></td></tr>
        </table>
        <h2 style="margin:0 0 20px;font-size:17px;color:#111110;">Détails de la demande</h2>
        <table width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;margin-bottom:28px;">
          <tr><td style="color:#888;width:40%;">Prestation :</td><td><strong>{{prestation_nom}}</strong></td></tr>
          <tr><td style="color:#888;">Personnes :</td><td><strong>{{nb_personnes}}</strong></td></tr>
          <tr><td style="color:#888;">Date événement :</td><td><strong>{{date_evenement}}</strong></td></tr>
          <tr><td style="color:#888;">Lieu :</td><td><strong>{{lieu}}</strong></td></tr>
          <tr><td style="color:#888;">Estimation :</td><td><strong>{{estimation_min}} – {{estimation_max}}</strong></td></tr>
        </table>
        <div style="background:#f9f6f1;border:1px solid #e8e0d0;border-radius:4px;padding:20px;margin-bottom:28px;font-size:14px;font-style:italic;color:#555;">
          {{client_message}}
        </div>
        <div style="text-align:center;">
          <a href="{{lien_admin}}" style="display:inline-block;background:#c8932a;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:3px;font-size:14px;letter-spacing:1px;text-transform:uppercase;font-family:Arial,sans-serif;">
            Accéder à la fiche →
          </a>
        </div>
      </td></tr>
      <tr><td style="background:#f5f0e8;padding:16px 40px;text-align:center;font-size:12px;color:#aaa;">
        <p style="margin:0;">Cet email est un message automatique généré par le système de gestion {{nom_boucher}}.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>'),

('reponse_devis',
 'Réponse personnalisée au client',
 'Votre devis {{reference}} — {{nom_boucher}}',
 '<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f5f0e8;font-family:Georgia,serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0e8;padding:40px 20px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:4px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
      <tr><td style="background:#111110;padding:32px 40px;text-align:center;">
        <h1 style="margin:0;color:#c8932a;font-family:Georgia,serif;font-size:26px;font-weight:normal;letter-spacing:2px;">{{nom_boucher}}</h1>
        <p style="margin:8px 0 0;color:#888;font-size:12px;letter-spacing:3px;text-transform:uppercase;">Boucherie &amp; Traiteur</p>
      </td></tr>
      <tr><td style="padding:40px;color:#333;font-size:15px;line-height:1.7;">
        <p style="margin:0 0 20px;">Bonjour <strong>{{client_prenom}}</strong>,</p>
        <p style="margin:0 0 8px;font-size:13px;color:#888;">Concernant votre devis <strong>{{reference}}</strong> — <em>{{prestation_nom}}</em></p>
        <div style="border-top:1px solid #e8e0d0;margin:20px 0;padding-top:20px;font-size:15px;line-height:1.8;color:#333;">
          {{message_personnalise}}
        </div>
        <p style="margin:28px 0 0;">Cordialement,<br><strong>{{nom_boucher}}</strong></p>
      </td></tr>
      <tr><td style="background:#f5f0e8;padding:20px 40px;text-align:center;font-size:12px;color:#aaa;">
        <p style="margin:0;">© {{annee}} {{nom_boucher}} — Tous droits réservés</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>');

-- ------------------------------------------------------------
--  Utilisateur test — AGENCE
--  Mot de passe : agence2025
--  IMPORTANT : Si la connexion échoue, régénérez le hash avec :
--    php bin/console security:hash-password agence2025
--  puis mettez à jour la colonne `password` dans phpMyAdmin.
-- ------------------------------------------------------------
INSERT INTO `users` (`email`, `password`, `roles`, `prenom`, `nom`, `actif`) VALUES
('agence@boucherie.local',
 '$2y$13$Fd7w9Q2JkLmR8vXnPtW0OuHsYbAeDcMqIzKjNlVpSxTrUfGhEwBy4',
 '["ROLE_AGENCE"]',
 'Agence',
 'Web',
 1);
