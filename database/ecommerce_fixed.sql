-- Création de la base de données e-commerce
CREATE DATABASE IF NOT EXISTS ecommerce;
USE ecommerce;

-- =====================================================
-- Table des utilisateurs
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('client', 'admin') DEFAULT 'client',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table des produits (items)
-- =====================================================
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) DEFAULT 'default.jpg',
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table du stock
-- =====================================================
CREATE TABLE IF NOT EXISTS stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_item INT NOT NULL,
    quantite_en_stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_item) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table des commandes
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_item INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table des factures
-- =====================================================
CREATE TABLE IF NOT EXISTS invoice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    adresse_facturation VARCHAR(255),
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Données de test
-- =====================================================

-- Insertion d'un administrateur (mot de passe: admin123)
INSERT INTO users (nom, email, mot_de_passe, role) VALUES 
('Admin User', 'admin@ecommerce.com', '$2y$10$u1.Q5.QZL0YGGEz1qVu0iuKr.mDYkGfJTF6f6gGdXJp/LVIhG7G5i', 'admin');

-- Insertion d'un client test (mot de passe: client123)
INSERT INTO users (nom, email, mot_de_passe, role) VALUES 
('Client Test', 'client@ecommerce.com', '$2y$10$t3.M1.QZL0YGGEz1qVu0iuKr.mDYkGfJTF6f6gGdXJp/LVIhG7G5i', 'client');

-- Insertion de produits
INSERT INTO items (nom, description, prix, image) VALUES 
('Laptop Pro', 'Ordinateur portable haute performance avec processeur dernière génération', 1299.99, 'laptop.jpg'),
('Smartphone X', 'Téléphone intelligent avec écran OLED et appareil photo 108MP', 899.99, 'phone.jpg'),
('Tablet Elite', 'Tablette 12 pouces parfaite pour le travail et le divertissement', 599.99, 'tablet.jpg'),
('Headphones Pro', 'Écouteurs sans fil avec réduction de bruit active', 249.99, 'headphones.jpg'),
('Smart Watch', 'Montre connectée avec suivi de santé et paiement NFC', 349.99, 'watch.jpg'),
('External SSD', 'Disque dur externe 1TB ultra-rapide avec interface USB-C', 129.99, 'ssd.jpg');

-- Insertion du stock pour chaque produit
INSERT INTO stock (id_item, quantite_en_stock) VALUES 
(1, 15),
(2, 25),
(3, 20),
(4, 40),
(5, 30),
(6, 50);
