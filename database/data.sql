-- ======================================
-- DONNÉES DE TEST
-- ======================================

-- Admin par défaut
-- Email: admin@kayak-loire.fr
-- Mot de passe: Admin123! (hashé ci-dessous)
INSERT INTO `users` (`email`, `password`, `nom`, `prenom`, `role`, `email_verified`) VALUES
('admin@kayak-loire.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Système', 'admin', 1);

-- Client de test
-- Email: client@test.fr
-- Mot de passe: Client123!
INSERT INTO `users` (`email`, `password`, `nom`, `prenom`, `telephone`, `role`, `email_verified`) VALUES
('client@test.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dupont', 'Jean', '0612345678', 'client', 1);

-- ======================================
-- ÉTAPES (Points d'arrêt sur la Loire)
-- ======================================
INSERT INTO `etapes` (`nom`, `description`, `ville`, `distance_precedente`, `ordre`, `actif`) VALUES
('Départ de Nevers', 'Point de départ de l\'aventure, au cœur de la Bourgogne. Ville riche en patrimoine avec sa cathédrale et son palais ducal.', 'Nevers', 0, 1, 1),
('La Charité-sur-Loire', 'Magnifique cité médiévale classée au patrimoine mondial de l\'UNESCO. Admirez l\'église prieurale Notre-Dame.', 'La Charité-sur-Loire', 28, 2, 1),
('Cosne-Cours-sur-Loire', 'Petite ville dynamique avec son port de plaisance. Idéal pour une pause déjeuner.', 'Cosne-Cours-sur-Loire', 30, 3, 1),
('Gien', 'Célèbre pour son château-musée et sa faïencerie. Magnifique pont sur la Loire.', 'Gien', 45, 4, 1),
('Sully-sur-Loire', 'Son château médiéval se reflète majestueusement dans la Loire. Étape incontournable !', 'Sully-sur-Loire', 35, 5, 1),
('Orléans', 'Grande ville historique, cité de Jeanne d\'Arc. Nombreux points d\'intérêt culturels.', 'Orléans', 40, 6, 1),
('Meung-sur-Loire', 'Charmante commune avec son château et ses ruelles pittoresques.', 'Meung-sur-Loire', 18, 7, 1),
('Beaugency', 'Ville médiévale remarquable avec son pont du XIIe siècle et son donjon.', 'Beaugency', 8, 8, 1),
('Blois', 'Ville royale dominée par son château Renaissance. Patrimoine exceptionnel.', 'Blois', 30, 9, 1),
('Amboise', 'Château royal et Clos Lucé (dernière demeure de Léonard de Vinci). Étape magique !', 'Amboise', 35, 10, 1);

-- ======================================
-- HÉBERGEMENTS
-- ======================================
-- Nevers
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(1, 'Hôtel de Loire', 'hotel', 'Hôtel 3 étoiles en centre-ville avec vue sur la Loire. Chambres confortables et petit-déjeuner buffet.', '12 Rue de la Loire, 58000 Nevers', '0386571234', 4, 85.00, '["wifi", "parking", "petit_dejeuner", "climatisation"]', 1),
(1, 'Camping Les Bords de Loire', 'camping', 'Camping familial avec emplacements ombragés au bord de l\'eau. Sanitaires modernes.', 'Route de la Loire, 58000 Nevers', '0386572345', 6, 25.00, '["wifi", "sanitaires", "espace_bbq"]', 1),
(1, 'Gîte du Vieux Nevers', 'gite', 'Charmant gîte en pierre dans une ruelle médiévale. Cuisine équipée, terrasse privée.', '8 Rue Saint-Martin, 58000 Nevers', '0686123456', 5, 120.00, '["wifi", "cuisine", "terrasse", "lave_linge"]', 1);

-- La Charité-sur-Loire
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(2, 'Chambres d\'Hôtes Le Prieuré', 'chambre_hote', 'Maison d\'hôtes de charme dans une ancienne demeure bourgeoise. Accueil chaleureux.', '15 Rue du Prieuré, 58400 La Charité-sur-Loire', '0386702345', 3, 75.00, '["wifi", "petit_dejeuner", "jardin"]', 1),
(2, 'Hôtel de la Loire', 'hotel', 'Hôtel 2 étoiles avec restaurant gastronomique. Vue panoramique sur le fleuve.', '3 Quai de la Loire, 58400 La Charité-sur-Loire', '0386703456', 4, 95.00, '["wifi", "parking", "restaurant", "petit_dejeuner"]', 1);

-- Cosne-Cours-sur-Loire
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(3, 'Camping du Port', 'camping', 'Camping au bord de l\'eau avec accès direct au port de plaisance. Ambiance conviviale.', 'Port de Plaisance, 58200 Cosne-Cours-sur-Loire', '0386281234', 6, 22.00, '["wifi", "sanitaires", "piscine"]', 1),
(3, 'Hôtel Le Vieux Relais', 'hotel', 'Ancien relais de poste rénové. Chambres spacieuses et parking sécurisé.', '11 Route de la Loire, 58200 Cosne-Cours-sur-Loire', '0386282345', 4, 78.00, '["wifi", "parking", "petit_dejeuner"]', 1);

-- Gien
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(4, 'Hôtel du Château', 'hotel', 'Hôtel face au château avec vue imprenable. Décoration raffinée.', '2 Place du Château, 45500 Gien', '0238671234', 4, 110.00, '["wifi", "parking", "petit_dejeuner", "climatisation"]', 1),
(4, 'Gîte Les Faïenciers', 'gite', 'Gîte moderne près du musée de la faïence. Idéal pour familles.', '7 Rue de la Faïencerie, 45500 Gien', '0638123456', 6, 140.00, '["wifi", "cuisine", "jardin", "parking"]', 1);

-- Sully-sur-Loire
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(5, 'Hostellerie du Château', 'hotel', 'Hôtel de charme à 100m du château. Ambiance médiévale et service impeccable.', '5 Rue du Château, 45600 Sully-sur-Loire', '0238361234', 4, 125.00, '["wifi", "parking", "restaurant", "petit_dejeuner"]', 1),
(5, 'Camping de Sully', 'camping', 'Grand camping 4 étoiles avec piscine couverte et animations en saison.', 'Route de Saint-Père, 45600 Sully-sur-Loire', '0238362345', 8, 35.00, '["wifi", "sanitaires", "piscine", "restaurant"]', 1);

-- Orléans
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(6, 'Hôtel Jeanne d\'Arc', 'hotel', 'Hôtel 3 étoiles en plein centre historique. Proche de la cathédrale.', '18 Rue Jeanne d\'Arc, 45000 Orléans', '0238531234', 4, 95.00, '["wifi", "parking", "petit_dejeuner", "climatisation"]', 1),
(6, 'Appartement Loire View', 'gite', 'Appartement moderne avec terrasse donnant sur la Loire. Tout équipé.', '25 Quai du Châtelet, 45000 Orléans', '0638234567', 4, 130.00, '["wifi", "cuisine", "terrasse", "lave_linge"]', 1);

-- Meung-sur-Loire
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(7, 'Chambres d\'Hôtes Les Mauges', 'chambre_hote', 'Belle demeure avec jardin fleuri. Petit-déjeuner fait maison délicieux.', '12 Rue des Mauges, 45130 Meung-sur-Loire', '0238441234', 3, 70.00, '["wifi", "petit_dejeuner", "jardin", "parking"]', 1);

-- Beaugency
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(8, 'Hôtel de l\'Abbaye', 'hotel', 'Hôtel dans une abbaye rénovée. Cadre exceptionnel et spa.', '2 Quai de l\'Abbaye, 45190 Beaugency', '0238441234', 4, 140.00, '["wifi", "parking", "spa", "petit_dejeuner", "restaurant"]', 1),
(8, 'Camping Val de Loire', 'camping', 'Camping calme en bordure de forêt. Idéal pour se ressourcer.', 'Route de Tavers, 45190 Beaugency', '0238442345', 6, 28.00, '["wifi", "sanitaires", "piscine"]', 1);

-- Blois
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(9, 'Hôtel Royal', 'hotel', 'Hôtel 4 étoiles face au château. Luxe et raffinement.', '1 Place du Château, 41000 Blois', '0254781234', 4, 160.00, '["wifi", "parking", "restaurant", "petit_dejeuner", "spa", "climatisation"]', 1),
(9, 'Gîte du Vieux Blois', 'gite', 'Maison de ville typique dans le quartier historique. Charme authentique.', '8 Rue Pierre de Blois, 41000 Blois', '0638345678', 5, 150.00, '["wifi", "cuisine", "terrasse", "lave_linge"]', 1);

-- Amboise
INSERT INTO `hebergements` (`etape_id`, `nom`, `type`, `description`, `adresse`, `telephone`, `capacite`, `prix_par_nuit`, `equipements`, `actif`) VALUES
(10, 'Hôtel Le Choiseul', 'hotel', 'Hôtel de luxe 4 étoiles avec piscine et restaurant gastronomique. Vue sur le château.', '36 Quai Charles Guinot, 37400 Amboise', '0247301234', 4, 180.00, '["wifi", "parking", "restaurant", "petit_dejeuner", "piscine", "spa", "climatisation"]', 1),
(10, 'Chambres d\'Hôtes Clos Lucé', 'chambre_hote', 'Maison bourgeoise proche du Clos Lucé. Jardin magnifique.', '12 Rue du Clos Lucé, 37400 Amboise', '0247302345', 3, 85.00, '["wifi", "petit_dejeuner", "jardin", "parking"]', 1),
(10, 'Camping L\'Île d\'Or', 'camping', 'Camping 5 étoiles sur une île de la Loire ! Animations et services haut de gamme.', 'Île d\'Or, 37400 Amboise', '0247303456', 8, 45.00, '["wifi", "sanitaires", "piscine", "restaurant", "animations"]', 1);

-- ======================================
-- SERVICES COMPLÉMENTAIRES
-- ======================================
INSERT INTO `services_complementaires` (`nom`, `description`, `prix`, `type`, `actif`) VALUES
('Transport des bagages', 'Vos bagages livrés à chaque étape. Plus besoin de les porter en kayak !', 15.00, 'transport_bagages', 1),
('Panier pique-nique', 'Panier garni avec produits locaux pour votre déjeuner (sandwich, fruit, boisson, dessert).', 12.00, 'panier_garni', 1),
('Location kayak 1 place', 'Kayak monoplace avec pagaie, gilet de sauvetage et bidon étanche.', 25.00, 'location_materiel', 1),
('Location kayak 2 places', 'Kayak biplace avec pagaies, gilets de sauvetage et bidons étanches.', 35.00, 'location_materiel', 1),
('Location VTT', 'VTT tout terrain pour explorer les alentours. Casque et antivol inclus.', 20.00, 'location_materiel', 1),
('Panier gastronomique', 'Panier premium avec spécialités régionales et bouteille de vin local.', 35.00, 'panier_garni', 1),
('Assurance annulation', 'Annulation sans frais jusqu\'à 48h avant le départ.', 25.00, 'autre', 1);

-- ======================================
-- PACKS PRÉDÉFINIS
-- ======================================
INSERT INTO `packs` (`nom`, `description`, `duree_jours`, `prix_base`, `difficulte`, `actif`) VALUES
('Découverte - 3 jours', 'Parfait pour un premier contact avec la Loire. De Nevers à Cosne-Cours-sur-Loire. Paysages variés et étapes tranquilles.', 3, 380.00, 'facile', 1),
('Châteaux de la Loire - 5 jours', 'Les plus beaux châteaux du Val de Loire. De Sully-sur-Loire à Amboise. Patrimoine exceptionnel !', 5, 720.00, 'moyen', 1),
('Grande traversée - 7 jours', 'L\'aventure complète de Nevers à Amboise. Pour les amoureux de la Loire et du kayak.', 7, 980.00, 'moyen', 1);

-- Composition du pack "Découverte - 3 jours"
INSERT INTO `pack_etapes` (`pack_id`, `etape_id`, `jour`, `hebergement_id`) VALUES
(1, 1, 1, 1),  -- Jour 1: Nevers - Hôtel de Loire
(1, 2, 2, 4),  -- Jour 2: La Charité - Chambres d'Hôtes Le Prieuré
(1, 3, 3, 7);  -- Jour 3: Cosne - Hôtel Le Vieux Relais

-- Composition du pack "Châteaux de la Loire - 5 jours"
INSERT INTO `pack_etapes` (`pack_id`, `etape_id`, `jour`, `hebergement_id`) VALUES
(2, 5, 1, 10), -- Jour 1: Sully - Hostellerie du Château
(2, 6, 2, 12), -- Jour 2: Orléans - Hôtel Jeanne d'Arc
(2, 8, 3, 15), -- Jour 3: Beaugency - Hôtel de l'Abbaye
(2, 9, 4, 17), -- Jour 4: Blois - Hôtel Royal
(2, 10, 5, 19); -- Jour 5: Amboise - Hôtel Le Choiseul

-- Composition du pack "Grande traversée - 7 jours"
INSERT INTO `pack_etapes` (`pack_id`, `etape_id`, `jour`, `hebergement_id`) VALUES
(3, 1, 1, 1),  -- Jour 1: Nevers
(3, 2, 2, 5),  -- Jour 2: La Charité
(3, 4, 3, 8),  -- Jour 3: Gien
(3, 5, 4, 10), -- Jour 4: Sully
(3, 6, 5, 12), -- Jour 5: Orléans
(3, 9, 6, 17), -- Jour 6: Blois
(3, 10, 7, 19); -- Jour 7: Amboise

-- ======================================
-- PROMOTIONS
-- ======================================
INSERT INTO `promotions` (`code`, `description`, `type`, `valeur`, `date_debut`, `date_fin`, `premiere_reservation_seulement`, `montant_min`, `actif`) VALUES
('BIENVENUE10', 'Code de bienvenue : -10% sur votre première réservation', 'pourcentage', 10.00, '2024-01-01', '2025-12-31', 1, 100.00, 1),
('ETE2024', 'Promotion été : -15% sur les réservations de juin à août', 'pourcentage', 15.00, '2024-06-01', '2024-08-31', 0, 200.00, 1),
('PRINTEMPS20', 'Offre printemps : -20€ sur les réservations de 5 jours minimum', 'montant_fixe', 20.00, '2024-03-01', '2024-05-31', 0, 400.00, 1);

-- ======================================
-- PLAGES TARIFAIRES
-- ======================================
INSERT INTO `plages_tarifaires` (`nom`, `date_debut`, `date_fin`, `coefficient`) VALUES
('Haute saison été', '2024-07-01', '2024-08-31', 1.30),
('Pont de l\'Ascension', '2024-05-09', '2024-05-12', 1.20),
('Vacances de la Toussaint', '2024-10-19', '2024-11-03', 1.15),
('Basse saison hiver', '2024-11-15', '2025-02-28', 0.85);