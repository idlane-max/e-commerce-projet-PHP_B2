<?php
/**
 * Configuration de la base de données et constantes globales
 */

// 1. Paramètres de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // 'root' par défaut
define('DB_PASSWORD', '');     // '' pour XAMPP, 'root' pour MAMP (à adapter)
define('DB_NAME', 'ecommerce');

// 2. URL de base (Adapte le dossier selon ton installation)
// Exemple : http://localhost/mon_ecommerce
define('BASE_URL', 'http://localhost/e-commerce-projet-PHP_B2');

// 3. Chemins absolus des dossiers 
define('ROOT_PATH', dirname(__DIR__)); // Remonte d'un cran depuis /config
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ADMIN_PATH', ROOT_PATH . '/admin');
// 4. Fonction de connexion à la base de données
function connectDB() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        
        // Configuration des options PDO
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Lève une exception en cas d'erreur
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Renvoie les données en tableau associatif
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Sécurité renforcée pour les requêtes préparées
        
        return $pdo;

    } catch (PDOException $e) {
        die("Erreur de connexion BDD : " . $e->getMessage());
    }
}

/**
 * Démarrer la session utilisateur
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>
