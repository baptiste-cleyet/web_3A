-- -- -- -- -- -- CRÉATION DE LA BASE-- -- -- -- -- -- -- -- -- -- -- -- -
CREATE DATABASE IF NOT EXISTS `projet_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `projet_db`;

-- -- -- -- -- -- -DROP (au cas ou)-- -- -- -- -- -- -- -- -- -- -- -- -- -- -
SET
    FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Commentaires`;

DROP TABLE IF EXISTS `Article_Tag`;

DROP TABLE IF EXISTS `Tags`;

DROP TABLE IF EXISTS `Articles`;

DROP TABLE IF EXISTS `Role_Permission`;

DROP TABLE IF EXISTS `Role_User`;

DROP TABLE IF EXISTS `Permissions`;

DROP TABLE IF EXISTS `Roles`;

DROP TABLE IF EXISTS `Utilisateurs`;

SET
    FOREIGN_KEY_CHECKS = 1;

-- -- -- -- -- -CRÉATION DES TABLES-- -- -- -- -- -- -- -- -- -- -- -- -- -
-- -- -Utilisateur
CREATE TABLE `Utilisateurs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom_utilisateur` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `mot_de_passe` CHAR(60) NOT NULL,
    --  Stocke le hash bcrypt/argon2 (60 chars pour bcrypt)
    `est_actif` BOOLEAN DEFAULT TRUE,
    `date_inscription` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -Roles
CREATE TABLE `Roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom_role` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -Permissions
CREATE TABLE `Permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom_permission` VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -Table de pivot (Association) role-user
CREATE TABLE `Role_User` (
    `role_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `user_id`),
    FOREIGN KEY (`role_id`) REFERENCES `Roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `Utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -Table de pivot (Association) role-permission
CREATE TABLE `Role_Permission` (
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `Roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `Permissions`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -Articles
CREATE TABLE `Articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `utilisateur_id` INT UNSIGNED NOT NULL,
    `titre` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `contenu` TEXT NOT NULL,
    `image_une` VARCHAR(255) NULL,
    `statut` ENUM('Brouillon', 'Publié', 'Archivé') DEFAULT 'Brouillon',
    `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `date_mise_a_jour` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`utilisateur_id`) REFERENCES `Utilisateurs`(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -Tags
CREATE TABLE `Tags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom_tag` VARCHAR(50) NOT NULL UNIQUE,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -Table de pivot (Association) article-tag
CREATE TABLE `Article_Tag` (
    `article_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`article_id`, `tag_id`),
    FOREIGN KEY (`article_id`) REFERENCES `Articles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `Tags`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -Commentaires
CREATE TABLE `Commentaires` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT UNSIGNED NOT NULL,
    `nom_auteur` VARCHAR(100) NOT NULL,
    `email_auteur` VARCHAR(100) NULL,
    `contenu` TEXT NOT NULL,
    `statut` ENUM('En attente', 'Approuvé', 'Rejeté') DEFAULT 'En attente',
    `date_commentaire` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`article_id`) REFERENCES `Articles`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- -- -- -- -- -- -- -- -- -- INSERTION DES DONNÉES DE TEST-- -- -- -- -- -- -- -- -- -- 
-- -- -Utilisateurs (mot de passe hashé)
SET
    @HASHED_PASSWORD = '$2y$10$Q7iR7/h7Gq6yRzW2gP0pT.0.1oQ5t4T8W0y5fG8E7C8zM7/V2C9a';   --  Hash pour le mot de passe 'vttadmin'

INSERT INTO
    `Utilisateurs` (`id`, `nom_utilisateur`, `email`, `mot_de_passe`)
VALUES
    (1, 'AdminVTT', 'admin@vtt.com', @HASHED_PASSWORD),
    (
        2,
        'EditeurTrail',
        'editeur@vtt.com',
        @HASHED_PASSWORD
    ),
    (
        3,
        'ContributeurRando',
        'contributeur@vtt.com',
        @HASHED_PASSWORD
    );

-- -- -Roles
INSERT INTO
    `Roles` (`id`, `nom_role`, `description`)
VALUES
    (
        1,
        'Administrateur',
        'Accès complet au tableau de bord et à la gestion des utilisateurs.'
    ),
    (
        2,
        'Éditeur',
        'Peut créer, modifier et publier ses propres articles et ceux des contributeurs.'
    ),
    (
        3,
        'Contributeur',
        'Peut créer et modifier ses propres articles (statut Brouillon uniquement).'
    );

-- -- -Permissions
INSERT INTO
    `Permissions` (`id`, `nom_permission`)
VALUES
    (1, 'admin_access'),
    (2, 'article_creer'),
    (3, 'article_editer_tous'),
    (4, 'article_publier'),
    (5, 'article_supprimer'),
    (6, 'commentaire_gerer'),
    (7, 'utilisateur_gerer'),
    (8, 'tag_gerer');

-- -- -Association role-permission
-- Administrateur(Role 1)
INSERT INTO
    `Role_Permission` (`role_id`, `permission_id`)
VALUES
    (1, 1),
    (1, 2),
    (1, 3),
    (1, 4),
    (1, 5),
    (1, 6),
    (1, 7),
    (1, 8);

-- Éditeur(Role 2)
INSERT INTO
    `Role_Permission` (`role_id`, `permission_id`)
VALUES
    (2, 1),
    (2, 2),
    (2, 3),
    (2, 4),
    (2, 6),
    (2, 8);

-- Contributeur
INSERT INTO
    `Role_Permission` (`role_id`, `permission_id`)
VALUES
    (3, 2);

-- -- -Association utilisateur-role
-- AdminVTT est Admin
INSERT INTO
    `Role_User` (`user_id`, `role_id`)
VALUES
    (1, 1);

--  EditeurTrail est Éditeur ET Contributeur (Un utilisateur peut avoir plusieurs rôles)
INSERT INTO
    `Role_User` (`user_id`, `role_id`)
VALUES
    (2, 2),
    (2, 3);

--  ContributeurRando est Contributeur
INSERT INTO
    `Role_User` (`user_id`, `role_id`)
VALUES
    (3, 3);

-- -- -Tags (VTT)
INSERT INTO
    `Tags` (`id`, `nom_tag`, `slug`)
VALUES
    (1, 'Traces GPS', 'traces-gps'),
    (2, 'Enduro', 'enduro'),
    (3, 'XC', 'xc'),
    (4, 'Suspension', 'suspension'),
    (5, 'Nutrition', 'nutrition'),
    (6, 'Entraînement', 'entrainement'),
    (7, 'Hydratation', 'hydratation');

-- -- - Articles (VTT)
INSERT INTO
    `Articles` (
        `id`,
        `utilisateur_id`,
        `titre`,
        `slug`,
        `contenu`,
        `statut`
    )
VALUES
    (
        1,
        1,
        'Top 5 des Traces VTT Enduro en Rhône-Alpes',
        'top-5-traces-vtt-enduro-rhone-alpes',
        '#
Introduction
Découvrez les descentes les plus mythiques pour les amateurs d\'**Enduro** en France. Freins
puissants et protections obligatoires !
## La piste de l\'Écureuil
Une trace rapide avec de gros dénivelés négatifs. Idéale pour tester votre **suspension**.',
        'Publié'
    ),
    (
        2,
        2,
        'Réglage de la suspension : le SAG parfait pour le XC',
        'reglage-suspension-sag-xc',
        'Le **SAG**
(Sinking At Ground) est crucial en **XC** pour optimiser le rendement et le confort. Nous détaillons
ici le processus étape par étape. Un mauvais réglage impacte directement la performance.',
        'Publié'
    ),
    (
        3,
        3,
        "Gérer l'Hydratation sur une longue sortie VTT",
        'gerer-hydratation-longue-sortie',
        'Au-delà de
3h, l\'eau seule ne suffit plus. Il faut intégrer des électrolytes et des glucides. Notre guide complet sur
la **Nutrition** et l\'**Hydratation**.',
        'Brouillon'
    );

-- -- -Association Article - Tag
INSERT INTO
    `Article_Tag` (`article_id`, `tag_id`)
VALUES
    (1, 1),
    (1, 2),
    (1, 4),
    (2, 3),
    (2, 4),
    (3, 5),
    (3, 7);

-- -- - Commentaires
INSERT INTO
    `Commentaires` (
        `article_id`,
        `nom_auteur`,
        `email_auteur`,
        `contenu`,
        `statut`
    )
VALUES
    (
        1,
        'Nicolas Rider',
        'nic@trail.fr',
        "Super article, je connaissais pas la piste de l'Écureuil ! J'y vais ce
weekend.",
        'Approuvé'
    ),
    (
        1,
        'Anonyme',
        NULL,
        'Trop de monde sur ces pistes, dommage...',
        'En attente'
    ),
    (
        2,
        'ProXC',
        'pro@xc.com',
        "J'utilise le même SAG, c'est le meilleur compromis !",
        'Approuvé'
    );