 -- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE, 
    password_hash VARCHAR(255) NOT NULL, 
    phone_gsm VARCHAR(20) NOT NULL, 
    address_postale TEXT NOT NULL, 
    city VARCHAR(100) NOT NULL,
    role ENUM('client', 'employee', 'admin') DEFAULT 'client', 
    is_active TINYINT(1) DEFAULT 1, -- Permet la suspension d'un compte
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Gestion des mots de passe oubliés 
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES users(email) ON DELETE CASCADE
) ENGINE=InnoDB;



-- GESTION DU CATALOGUE 

CREATE TABLE IF NOT EXISTS themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS diets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL 
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS allergens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL 
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type ENUM('entree', 'plat', 'dessert') NOT NULL,
    description TEXT
) ENGINE=InnoDB;

-- Table de liaison (Un plat peut avoir plusieurs allergènes, et inversement)
CREATE TABLE IF NOT EXISTS dish_allergens (
    dish_id INT,
    allergen_id INT,
    PRIMARY KEY (dish_id, allergen_id),
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    FOREIGN KEY (allergen_id) REFERENCES allergens(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des Menus avec gestion des stocks en temps réel
CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) DEFAULT 'default.jpg',
    price_per_person DECIMAL(10, 2) NOT NULL, 
    min_people INT NOT NULL DEFAULT 1, 
    stock INT NOT NULL DEFAULT 100, 
    theme_id INT,
    usage_conditions TEXT,
    is_active TINYINT(1) DEFAULT 1, -- Archivage possible pour préserver l'historique comptable
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tables de liaison pour rattacher les plats et régimes aux menus
CREATE TABLE IF NOT EXISTS menu_dishes (
    menu_id INT,
    dish_id INT,
    PRIMARY KEY (menu_id, dish_id),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS menu_diets (
    menu_id INT,
    diet_id INT,
    PRIMARY KEY (menu_id, diet_id),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (diet_id) REFERENCES diets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS menu_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_thumbnail TINYINT(1) DEFAULT 0,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
) ENGINE=InnoDB;



-- COMMANDES ET TRAÇABILITÉ  

-- Table des commandes avec suivi des statuts logistiques
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    delivery_date DATETIME NOT NULL,    
    delivery_time TIME NOT NULL,    
    shipping_city VARCHAR(100) NOT NULL,    
    shipping_address TEXT NOT NULL,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,  
    total_amount DECIMAL(10, 2) NOT NULL,   
    status ENUM('pending', 'accepted', 'preparation', 'delivery', 'delivered', 'wait_material', 'completed', 'cancelled') DEFAULT 'pending',
    cancellation_reason TEXT, -- Justification obligatoire si annulé par un employé
    contact_method ENUM('gsm', 'email'), 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Historisation comptable : on fige le prix du menu au moment de l'achat 
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT NOT NULL, 
    price_at_purchase DECIMAL(10, 2) NOT NULL, 
    applied_discount DECIMAL(5,2) DEFAULT 0.00, -- Remise de 10%
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Historisation des changements de statuts en temps réel
CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AVIS CLIENTS ET MODÉRATION

-- Gestion des avis avec système de modération par les employés
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    is_validated TINYINT(1) DEFAULT 0, -- Invisible par défaut, nécessite une validation
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- FIXTURES : DONNÉES DE DÉPART (Initialisation)

INSERT INTO themes (name) VALUES 
('Automne'), ('Hiver'), ('Signature'), ('Noël'), ('Pâques'), ('Classique'), ('Évènement');

INSERT INTO diets (name) VALUES 
('Classique'), ('Végétarien'), ('Vegan');

INSERT INTO menus (title, description, price_per_person, stock, theme_id, image) VALUES 
('Le Velouté d\'Automne', 'Potiron et éclats de châtaigne.', 15.50, 50, 1, 'menu1.jpg'),
('Plateau Océan', 'Sélection de fruits de mer frais.', 45.00, 20, 3, 'menu2.jpg'),
('Festin d\'Hiver', 'Rôti de bœuf et légumes oubliés.', 28.00, 30, 2, 'menu3.jpg');

-- Administrateur de test (Mot de passe : password)
INSERT INTO users (first_name, last_name, email, password_hash, phone_gsm, address_postale, city, role) 
VALUES ('José', 'Admin', 'admin@viteetgourmand.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0600000000', '1 Rue de la Mairie', 'Roubaix', 'admin');

-- Employé de test (Mot de passe : password)
INSERT INTO users (first_name, last_name, email, password_hash, phone_gsm, address_postale, city, role) 
VALUES ('Jeanne', 'Employée', 'employe@viteetgourmand.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0611223344', '2 Rue du Commerce', 'Bordeaux', 'employee');