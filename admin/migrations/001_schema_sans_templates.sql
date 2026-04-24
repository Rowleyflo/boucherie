-- ============================================================
--  Boucherie Chez Guillaume — Schéma + données (sans templates email)
--  Les templates email seront configurés via l'interface admin.
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
    `prestation_slug`  VARCHAR(50)     NOT NULL,
    `prestation_nom`   VARCHAR(150)    NOT NULL,
    `nb_personnes`     SMALLINT        NOT NULL,
    `date_evenement`   DATE            NULL,
    `lieu`             ENUM('sur_place','livraison','a_definir') NOT NULL DEFAULT 'a_definir',
    `lieu_detail`      VARCHAR(255)    NULL,
    `options`          JSON            NULL,
    `estimation_min`   DECIMAL(10,2)   NULL,
    `estimation_max`   DECIMAL(10,2)   NULL,
    `client_nom`       VARCHAR(100)    NOT NULL,
    `client_prenom`    VARCHAR(100)    NOT NULL,
    `client_email`     VARCHAR(180)    NOT NULL,
    `client_telephone` VARCHAR(20)     NOT NULL,
    `client_message`   TEXT            NULL,
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

INSERT INTO `prestations_config` (`slug`, `nom`, `description`, `icone`, `actif`, `ordre`) VALUES
('mariage',  'Mariage & Réceptions',     'Sublimez votre grand jour avec nos viandes d\'exception et nos plateaux de charcuterie artisanale.',    '💍', 1, 10),
('brasero',  'Brasero & Grillade',       'L\'art de la braise dans toute sa splendeur. Côtes de boeuf, agneau entier, merguez maison...', '🔥', 1, 20),
('traiteur', 'Traiteur Événementiel',    'Des repas d\'entreprise aux anniversaires haut de gamme, notre équipe compose des menus équilibrés.',   '🍽️', 1, 30),
('barbecue', 'Barbecue Géant',           'Formules barbecue pour vos fêtes de quartier, kermesses et événements familiaux.',                      '🥩', 1, 40),
('autre',    'Autre Prestation',         'Vous avez un projet particulier ? Décrivez-nous votre événement pour une proposition sur mesure.',        '✨', 1, 50);

INSERT INTO `tarifs_config` (`prestation_id`, `label`, `description`, `prix_base`, `unite`, `min_personnes`, `max_personnes`, `actif`, `ordre`) VALUES
(1, 'Cocktail dînatoire',              'Verrines, planches charcuterie & fromage, mini-brochettes, toasts garnis',          28.50, 'par_personne', 20,  NULL, 1, 10),
(1, 'Buffet charcuterie & fromagerie', 'Sélection de 8 charcuteries, 6 fromages affinés, pains artisanaux, condiments',     22.00, 'par_personne', 30,  NULL, 1, 20),
(1, 'Formule complète',                'Cocktail dînatoire + buffet + service à table',                                     48.00, 'par_personne', 50,  NULL, 1, 30),
(1, 'Supplément livraison',            'Livraison, installation, présentation et desserte en fin de réception',             180.00, 'forfait',      NULL, NULL, 1, 40),
(2, 'Formule classique',               'Boeuf (400g/pers), agneau, merguez maison, saucisses, pain & sauces',               18.50, 'par_personne', 15,  NULL, 1, 10),
(2, 'Formule premium',                 'Côte de boeuf, entrecôte maturée, côtelettes d\'agneau + accompagnements',          32.00, 'par_personne', 10,  NULL, 1, 20),
(2, 'Location brasero',                'Location du brasero professionnel (sans viandes) + livraison/reprise',              85.00, 'forfait',      NULL, NULL, 1, 30),
(2, 'Supplément fuel',                 'Charbon de qualité, allumage et gestion du feu pendant tout l\'événement',          45.00, 'forfait',      NULL, NULL, 1, 40),
(3, 'Menu entrée-plat',                'Entrée froide + plat chaud (viande rôtie ou braisée) + pain',                      26.00, 'par_personne', 15,  NULL, 1, 10),
(3, 'Menu complet 3 temps',            'Entrée + plat + dessert maison. Livraison en contenants isothermes',                38.00, 'par_personne', 20,  NULL, 1, 20),
(3, 'Plateau charcuterie (1kg)',        'Jambon cru, saucisson sec, rillettes, terrine, cornichons',                        28.00, 'au_kg',        NULL, NULL, 1, 30),
(3, 'Service avec personnel',          'Serveur(s) pour la soirée (par tranche de 3h)',                                   120.00, 'forfait',      NULL, NULL, 1, 40),
(4, 'Pack BBQ essentiel (400g)',        'Merguez, chipolatas, côtelettes, brochettes boeuf-mouton',                        14.50, 'par_personne', 20,  NULL, 1, 10),
(4, 'Pack BBQ généreux (600g)',         'Entrecôte, saucisses premium, kebab, brochettes mixtes + sauces',                 22.00, 'par_personne', 15,  NULL, 1, 20),
(4, 'Pack BBQ tout compris',            'Viandes + salades, pain, condiments, vaisselle jetable biodégradable',            30.00, 'par_personne', 25, 300, 1, 30),
(5, 'Prestation sur mesure',            'Prix établi après étude de votre projet.',                                         0.00, 'forfait',      NULL, NULL, 1, 10);

-- Utilisateur test — mot de passe : agence2025
INSERT INTO `users` (`email`, `password`, `roles`, `prenom`, `nom`, `actif`) VALUES
('agence@boucherie.local',
 '$2y$13$Fd7w9Q2JkLmR8vXnPtW0OuHsYbAeDcMqIzKjNlVpSxTrUfGhEwBy4',
 '["ROLE_AGENCE"]',
 'Agence', 'Web', 1);
