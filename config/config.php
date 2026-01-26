<?php
/**
 * Configuration de la base de données et constants globaux
 * Fichier de configuration pour la connexion MySQL
 */

// Paramètres de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ecommerce');

// URL de base
define('BASE_URL', 'http://localhost/e-commerce-projet-PHP_B2');

// Chemins des dossiers
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ADMIN_PATH', ROOT_PATH . '/admin');

// Variables de session
define('SESSION_TIMEOUT', 3600); // 1 heure en secondes

/**
 * Fonction de connexion à la base de données
 * Retourne une connexion mysqli
 */
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données: " . $conn->connect_error);
    }
    
    // Définir le charset UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Démarrer la session utilisateur
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
