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
    rôle ENUM('client', 'admin') DEFAULT 'client',
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
    quantité_en_stock INT DEFAULT 0,
    FOREIGN KEY (id_item) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table des commandes
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_item INT NOT NULL,
    quantité INT NOT NULL,
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
    adresse_facturation VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(10) NOT NULL,
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Insertion de données de test
-- =====================================================

-- Admin user (password: admin123)
INSERT INTO users (nom, email, mot_de_passe, rôle) VALUES 
('Admin Boutique', 'admin@ecommerce.com', '$2y$10$1234567890123456789012345678901234567890123456789012', 'admin');

-- Produits de test
INSERT INTO items (nom, description, prix, image) VALUES 
('Téléphone Smartphone', 'Un téléphone moderne et performant', 599.99, 'phone.jpg'),
('Ordinateur Portable', 'Laptop haute performance pour le travail', 1299.99, 'laptop.jpg'),
('Écouteurs Bluetooth', 'Écouteurs sans fil de qualité premium', 149.99, 'headphones.jpg'),
('Chargeur USB-C', 'Chargeur rapide compatible avec tous les appareils USB-C', 49.99, 'charger.jpg'),
('Coque Protection', 'Coque robuste pour smartphone', 29.99, 'case.jpg');

-- Stock pour chaque produit
INSERT INTO stock (id_item, quantité_en_stock) VALUES 
(1, 50),
(2, 30),
(3, 100),
(4, 150),
(5, 200);
