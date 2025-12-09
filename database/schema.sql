-- ======================================
-- BASE DE DONNÉES : kayak_loire
-- ======================================

-- ======================================
-- TABLE : users (Utilisateurs)
-- ======================================
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `telephone` VARCHAR(20) DEFAULT NULL,
  `adresse` TEXT DEFAULT NULL,
  `code_postal` VARCHAR(10) DEFAULT NULL,
  `ville` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('client', 'admin') DEFAULT 'client',
  `email_verified` TINYINT(1) DEFAULT 0,
  `verification_token` VARCHAR(255) DEFAULT NULL,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expiry` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : etapes (Points d'arrêt)
-- ======================================
CREATE TABLE `etapes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `ville` VARCHAR(100) NOT NULL,
  `latitude` DECIMAL(10, 8) DEFAULT NULL,
  `longitude` DECIMAL(11, 8) DEFAULT NULL,
  `distance_precedente` INT(11) DEFAULT NULL,
  `ordre` INT(11) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : hebergements (Hébergements)
-- ======================================
CREATE TABLE `hebergements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `etape_id` INT(11) NOT NULL,
  `nom` VARCHAR(200) NOT NULL,
  `type` ENUM('hotel', 'camping', 'gite', 'chambre_hote') NOT NULL,
  `description` TEXT DEFAULT NULL,
  `adresse` TEXT NOT NULL,
  `telephone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `capacite` INT(11) NOT NULL COMMENT 'Nombre de personnes max',
  `prix_par_nuit` DECIMAL(10, 2) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `equipements` TEXT DEFAULT NULL COMMENT 'JSON: ["wifi", "parking", "petit_dejeuner"]',
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `etape_id` (`etape_id`),
  CONSTRAINT `hebergements_ibfk_1` FOREIGN KEY (`etape_id`) REFERENCES `etapes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : fermetures_hebergements (Périodes de fermeture)
-- ======================================
CREATE TABLE `fermetures_hebergements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `hebergement_id` INT(11) NOT NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `motif` VARCHAR(255) DEFAULT NULL COMMENT 'Ex: Travaux, Congés',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `hebergement_id` (`hebergement_id`),
  CONSTRAINT `fermetures_hebergements_ibfk_1` FOREIGN KEY (`hebergement_id`) REFERENCES `hebergements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : services_complementaires (Services additionnels)
-- ======================================
CREATE TABLE `services_complementaires` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `prix` DECIMAL(10, 2) NOT NULL,
  `type` ENUM('transport_bagages', 'panier_garni', 'location_materiel', 'autre') NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : packs (Parcours prédéfinis)
-- ======================================
CREATE TABLE `packs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `duree_jours` INT(11) NOT NULL,
  `prix_base` DECIMAL(10, 2) NOT NULL,
  `difficulte` ENUM('facile', 'moyen', 'difficile') DEFAULT 'moyen',
  `image` VARCHAR(255) DEFAULT NULL,
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : pack_etapes (Liaison Pack <-> Étapes)
-- ======================================
CREATE TABLE `pack_etapes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pack_id` INT(11) NOT NULL,
  `etape_id` INT(11) NOT NULL,
  `jour` INT(11) NOT NULL COMMENT 'Jour du pack (1, 2, 3...)',
  `hebergement_id` INT(11) DEFAULT NULL COMMENT 'Hébergement proposé pour cette étape',
  PRIMARY KEY (`id`),
  KEY `pack_id` (`pack_id`),
  KEY `etape_id` (`etape_id`),
  KEY `hebergement_id` (`hebergement_id`),
  CONSTRAINT `pack_etapes_ibfk_1` FOREIGN KEY (`pack_id`) REFERENCES `packs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pack_etapes_ibfk_2` FOREIGN KEY (`etape_id`) REFERENCES `etapes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pack_etapes_ibfk_3` FOREIGN KEY (`hebergement_id`) REFERENCES `hebergements` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : promotions (Codes promo)
-- ======================================
CREATE TABLE `promotions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `type` ENUM('pourcentage', 'montant_fixe') NOT NULL,
  `valeur` DECIMAL(10, 2) NOT NULL COMMENT 'Pourcentage (ex: 10) ou montant (ex: 20.00)',
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `utilisation_max` INT(11) DEFAULT NULL COMMENT 'NULL = illimité',
  `utilisation_count` INT(11) DEFAULT 0,
  `premiere_reservation_seulement` TINYINT(1) DEFAULT 0,
  `montant_min` DECIMAL(10, 2) DEFAULT NULL COMMENT 'Montant minimum de commande',
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : plages_tarifaires (Tarifs spécifiques par période)
-- ======================================
CREATE TABLE `plages_tarifaires` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(200) NOT NULL COMMENT 'Ex: Haute saison été, Semaine de Noël',
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `coefficient` DECIMAL(5, 2) NOT NULL COMMENT 'Multiplicateur du prix (1.5 = +50%, 0.8 = -20%)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : reservations (Réservations clients)
-- ======================================
CREATE TABLE `reservations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `numero_reservation` VARCHAR(50) NOT NULL COMMENT 'Ex: RES20240315001',
  `type` ENUM('personnalise', 'pack') NOT NULL,
  `pack_id` INT(11) DEFAULT NULL COMMENT 'Si type = pack',
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `nb_personnes` INT(11) NOT NULL,
  `prix_hebergements` DECIMAL(10, 2) DEFAULT 0,
  `prix_services` DECIMAL(10, 2) DEFAULT 0,
  `prix_total_ht` DECIMAL(10, 2) NOT NULL,
  `promotion_id` INT(11) DEFAULT NULL,
  `montant_reduction` DECIMAL(10, 2) DEFAULT 0,
  `prix_total_ttc` DECIMAL(10, 2) NOT NULL,
  `statut` ENUM('en_attente', 'confirmee', 'annulee', 'terminee') DEFAULT 'en_attente',
  `commentaire` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_reservation` (`numero_reservation`),
  KEY `user_id` (`user_id`),
  KEY `pack_id` (`pack_id`),
  KEY `promotion_id` (`promotion_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`pack_id`) REFERENCES `packs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : reservation_hebergements (Hébergements d'une réservation)
-- ======================================
CREATE TABLE `reservation_hebergements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` INT(11) NOT NULL,
  `hebergement_id` INT(11) NOT NULL,
  `date_arrivee` DATE NOT NULL,
  `date_depart` DATE NOT NULL,
  `nb_nuits` INT(11) NOT NULL,
  `prix_unitaire` DECIMAL(10, 2) NOT NULL COMMENT 'Prix au moment de la réservation',
  `prix_total` DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `hebergement_id` (`hebergement_id`),
  CONSTRAINT `reservation_hebergements_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_hebergements_ibfk_2` FOREIGN KEY (`hebergement_id`) REFERENCES `hebergements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : reservation_services (Services d'une réservation)
-- ======================================
CREATE TABLE `reservation_services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` INT(11) NOT NULL,
  `service_id` INT(11) NOT NULL,
  `quantite` INT(11) NOT NULL DEFAULT 1,
  `prix_unitaire` DECIMAL(10, 2) NOT NULL,
  `prix_total` DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `reservation_services_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services_complementaires` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : messages_chat (Messagerie temps réel)
-- ======================================
CREATE TABLE `messages_chat` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `admin_id` INT(11) DEFAULT NULL COMMENT 'Admin qui répond (NULL si message du client)',
  `message` TEXT NOT NULL,
  `expediteur` ENUM('client', 'admin') NOT NULL,
  `lu` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `messages_chat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_chat_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================
-- TABLE : newsletter (Abonnés newsletter)
-- ======================================
CREATE TABLE `newsletter` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `prenom` VARCHAR(100) DEFAULT NULL,
  `actif` TINYINT(1) DEFAULT 1,
  `token_desinscription` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;