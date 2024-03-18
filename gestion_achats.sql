CREATE DATABASE IF NOT EXISTS gestion_achats;

CREATE TABLE IF NOT EXISTS gestion_achats.fournisseurs 
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nom VARCHAR(75) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    siret DECIMAL(14,0) NOT NULL UNIQUE,
    mail VARCHAR(200) NOT NULL,
    numero_telephone VARCHAR(15),
    IBAN VARCHAR(34) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS gestion_achats.produits
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nom VARCHAR(255),
    description TEXT,
    prix_unitaire DECIMAL(8,2),
    id_fournisseur INT NOT NULL,
    CONSTRAINT FK_fournisseurs_produits FOREIGN KEY (id_fournisseur) REFERENCES fournisseurs(id)
);

CREATE TABLE IF NOT EXISTS gestion_achats.commandes
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    quantite INT NOT NULL,
    statut VARCHAR(20) NOT NULL,
    date_commande DATETIME NOT NULL,
    prix DECIMAL(8,2) NOT NULL,
    id_fournisseur INT NOT NULL,
    CONSTRAINT FK_fournisseurs_commandes FOREIGN KEY (id_fournisseur) REFERENCES fournisseurs(id), 
    id_produit INT NOT NULL,
    CONSTRAINT FK_produits_commandes FOREIGN KEY (id_produit) REFERENCES produits(id) 
);

CREATE TABLE IF NOT EXISTS gestion_achats.alertes
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    type_alerte VARCHAR(50) NOT NULL,
    date_alerte DATETIME NOT NULL,
    description_alerte TEXT NOT NULL,
    id_commande INT NOT NULL,
    CONSTRAINT FK_commandes_alertes FOREIGN KEY (id_commande) REFERENCES commandes(id) 
);

USE gestion_achats;
CREATE TRIGGER commande_superieur_10000
AFTER INSERT ON gestion_achats.commandes
FOR EACH ROW
BEGIN
    IF NEW.prix > 10000 THEN
        INSERT INTO gestion_achats.alertes (`type_alerte`, `date_alerte`, `description_alerte`, `id_commande`) VALUES
        (
            'Achat commande supérieure à 10000', 
            NOW(), 
            CONCAT('La commande ', NEW.id, ' est supérieure à 10.000€. Sa valeur est de ', NEW.prix, '€.'), 
            NEW.id
        );
    END IF;
END;

USE gestion_achats;
CREATE TRIGGER before_insert_commande
BEFORE INSERT ON gestion_achats.commandes
FOR EACH ROW
BEGIN
	DECLARE prix_unit DECIMAL(10,2);
    DECLARE fournisseur INT;
    SELECT prix_unitaire INTO prix_unit FROM gestion_achats.produits WHERE id = NEW.id_produit;
    SELECT id_fournisseur INTO fournisseur FROM gestion_achats.produits WHERE id = NEW.id_produit;
    SET NEW.prix = prix_unit * NEW.quantite;
    SET NEW.date_commande = NOW();
    SET NEW.statut = "En attente";
    SET NEW.id_fournisseur = fournisseur;
END;

USE gestion_achats;
CREATE TRIGGER before_update_commande
BEFORE UPDATE ON gestion_achats.commandes
FOR EACH ROW
BEGIN
	DECLARE prix_unit DECIMAL(10,2);
    DECLARE fournisseur INT;
    SELECT prix_unitaire INTO prix_unit FROM gestion_achats.produits WHERE id = NEW.id_produit;
    SELECT id_fournisseur INTO fournisseur FROM gestion_achats.produits WHERE id = NEW.id_produit;
    SET NEW.prix = prix_unit * NEW.quantite;
    SET NEW.date_commande = NOW();
    SET NEW.statut = "En attente";
    SET NEW.id_fournisseur = fournisseur;
END;

INSERT INTO gestion_achats.fournisseurs (`nom`, `adresse`, `siret`, `mail`, `numero_telephone`, `IBAN`) VALUES
('La Maison du Meuble', '24 Rue de la République, 75001 Paris', 12345678901234, 'contact@maisonmeuble.com', '06 23 45 67 89', 'FR7612345678901234567890123'),
('Société Culinaire Française', '15 Avenue des Champs-Élysées, 75008 Paris', 98765432109876, 'info@culinairefrancaise.com', '01 23 45 67 89', 'FR7612345678901234567890124'),
('Amazon', '48 rue du Beuvagras, 54720 Saint-Lorem', 12245316902534, 'contact@amazon.com', '06 13 25 47 63', 'FR7612345678900234587730123'),
('Société Agriculture', '12 Avenue de Jean, 59000 Lille', 21115432109876, 'info@agriculture.com', '01 21 25 57 29', 'FR7612342238231242567890124'),
('Rue du Commerce', '1 Rue des Rue, 59190 Hazebrouck', 21115432129891, 'info@rueducommerce.com', '01 11 99 55 22', 'FR4445555238231242567890124'),
('Auchan', '12 Avenue de Paul, 62500 Saint-Omer', 21115499987526, 'info@auchan.com', '01 21 22 99 88', 'FR7612342238231242789998112'),
('Magasin ÉlectroTech', '8 Rue des Électrons, 69001 Lyon', 98765432101232, 'contact@electrotech.fr', '04 56 78 90 12', 'FR7612345678901234567890125');

INSERT INTO gestion_achats.produits (`nom`, `description`, `prix_unitaire`, `id_fournisseur`) VALUES
('Smartwatch VitessePro', 'La Smartwatch VitessePro est conçue pour les athlètes sérieux qui veulent suivre leurs performances avec précision. Dotée de capteurs avancés, elle mesure non seulement votre fréquence cardiaque et votre activité physique, mais elle offre également des fonctionnalités telles que le suivi GPS, les notifications intelligentes et une autonomie de batterie longue durée.', 140.00, 1),
('Ensemble de couteaux de cuisine en acier Damas', "Cet ensemble de couteaux de cuisine en acier Damas est l'ultime compagnon pour tout chef amateur ou professionnel. Fabriqués à partir d'acier japonais de haute qualité, ces couteaux offrent une netteté exceptionnelle et une durabilité accrue. L'ensemble comprend un couteau de chef, un couteau Santoku, un couteau à découper, un couteau à pain et un couteau utilitaire, tous présentés dans un élégant bloc en bois.", 15.25, 2),
('Enceinte Bluetooth Atmosphère 360', "Plongez dans un son immersif avec l'enceinte Bluetooth Atmosphère 360. Cette enceinte élégante offre un son à 360 degrés qui remplit n'importe quelle pièce avec une clarté cristalline. Dotée de la technologie Bluetooth 5.0, elle se connecte facilement à votre appareil mobile pour diffuser votre musique préférée. De plus, sa batterie longue durée vous permet de profiter de votre musique pendant des heures, où que vous soyez.", 35.00, 3),
('Sac à dos anti-vol SecureTech', "Le sac à dos anti-vol SecureTech est conçu pour protéger vos effets personnels lorsque vous êtes en déplacement. Doté de fermetures éclair cachées et de poches dissimulées, il offre une sécurité supplémentaire contre les vols. De plus, son tissu résistant à l'eau et ses compartiments bien organisés en font le choix idéal pour les voyageurs soucieux de la sécurité.", 10.00, 4),
('Machine à espresso BaristaPro', "Transformez votre cuisine en café-bar avec la machine à espresso BaristaPro. Dotée d'une pompe haute pression et d'un système de chauffage rapide, cette machine vous permet de préparer des espressos riches et crémeux en un instant. Avec ses fonctions avancées telles que la mousse de lait automatique et les réglages de température personnalisables, vous pouvez créer des boissons caféinées dignes d'un barista professionnel chez vous.", 241.13, 5),
('Jeu de construction en blocs magnétiques MagiWorld', "Laissez libre cours à votre imagination avec le jeu de construction en blocs magnétiques MagiWorld. Ces blocs colorés se connectent facilement grâce à des aimants intégrés, permettant aux enfants de créer une variété d'objets et de structures en 3D. Le jeu favorise la créativité, la coordination main-œil et la pensée spatiale tout en offrant des heures de plaisir sans fin pour les jeunes esprits créatifs.", 24.72, 6),
("Lampe de bureau LED EcoLite", "La lampe de bureau LED EcoLite allie style et fonctionnalité pour éclairer votre espace de travail. Dotée de bras ajustables et de plusieurs modes d'éclairage, cette lampe vous permet de personnaliser votre expérience d'éclairage pour répondre à vos besoins spécifiques. De plus, sa technologie LED économe en énergie vous permet de réduire votre empreinte carbone tout en bénéficiant d'un éclairage brillant et uniforme.", 4.05, 7);

INSERT INTO gestion_achats.commandes (`quantite`, `id_produit`) VALUES
(10, 1),
(15, 2),
(8, 3),
(10, 4),
(15, 5),
(15, 6),
(8, 7),
(10, 4),
(15, 5),
(15, 6),
(8, 2);

INSERT INTO gestion_achats.alertes (`type_alerte`, `date_alerte`, `description_alerte`, `id_commande`) VALUES
    ('Retard de livraison', '2024-03-14', 'La livraison de la commande est en retard.', 1),
    ('Stock faible', '2024-03-13', 'Le stock du produit est faible.', 2),
    ('Problème de paiement', '2024-03-12', 'Le paiement de la commande a échoué.', 3);    

USE gestion_achats;
CREATE PROCEDURE `creer_fournisseur`(
    IN p_nom VARCHAR(75),
    IN p_adresse VARCHAR(255),
    IN p_siret DECIMAL(14,0),
    IN p_mail VARCHAR(200),
    IN p_numero_telephone VARCHAR(15),
    IN p_IBAN VARCHAR(34)
)
BEGIN 
    INSERT INTO gestion_achats.fournisseurs (`nom`, `adresse`, `siret`, `mail`, `numero_telephone`, `IBAN`) VALUES
    (p_nom, p_adresse, p_siret, p_mail, p_numero_telephone, p_IBAN);
END;


USE gestion_achats;
CREATE PROCEDURE `supprimer_fournisseur`(
    IN p_id int
)
BEGIN 
    DELETE FROM gestion_achats.fournisseurs WHERE `id` = p_id;
END;


USE gestion_achats;
CREATE PROCEDURE `modifier_fournisseur`(
    IN p_id int,
    IN p_nom VARCHAR(75),
    IN p_adresse VARCHAR(255),
    IN p_siret DECIMAL(14,0),
    IN p_mail VARCHAR(200),
    IN p_numero_telephone VARCHAR(15),
    IN p_IBAN VARCHAR(34)
)
BEGIN 
    UPDATE gestion_achats.fournisseurs 
    SET `nom` = p_nom, `adresse` = p_adresse, `siret` = p_siret, `mail` = p_mail, `numero_telephone` = p_numero_telephone, `IBAN` = p_IBAN
    WHERE `id` = p_id;
END;


USE gestion_achats;
CREATE PROCEDURE `lire_fournisseurs`()
BEGIN 
    SELECT * FROM gestion_achats.fournisseurs; 
END;


USE gestion_achats;
CREATE PROCEDURE `creer_commande`(
    IN p_quantite INT,
    IN p_id_produit INT
)
BEGIN 
    INSERT INTO gestion_achats.commandes (`quantite`, `id_produit`) VALUES
    (p_quantite, p_id_produit);
END;


USE gestion_achats;
CREATE PROCEDURE `supprimer_commande`(
    IN p_id int
)
BEGIN 
    DELETE FROM gestion_achats.commandes WHERE `id` = p_id;
END;


USE gestion_achats;
CREATE PROCEDURE `modifier_commande`(
    IN p_id int,
    IN p_quantite INT,
    IN p_id_produit INT
)
BEGIN 
    UPDATE gestion_achats.commandes 
    SET `quantite` = p_quantite, `id_produit` = p_id_produit
    WHERE `id` = p_id;
END;


USE gestion_achats;
CREATE PROCEDURE `lire_commandes`()
BEGIN 
    SELECT * FROM gestion_achats.commandes; 
END;

USE gestion_achats;
CREATE PROCEDURE select_commandes(
    IN p_offset int,
    IN p_limit int
)
BEGIN
    SELECT `id`, `quantite`, `statut`, DATE_FORMAT(`date_commande`, "%d/%m/%Y - %H:%i") as date_commande , `prix`, `id_fournisseur`, `id_produit` FROM `commandes` ORDER BY UNIX_TIMESTAMP(date_commande) DESC LIMIT p_offset,p_limit;
END;
    
USE gestion_achats;
CREATE PROCEDURE `lister_alertes`()
BEGIN
    SELECT * FROM gestion_achats.alertes;
END;
