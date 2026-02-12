<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/config.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/mon_ecommerce'); // Change le nom du dossier ici aussi
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyShop - E-Commerce</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../public/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="<?php echo BASE_URL; ?>../public/index.php">
                <i class="bi bi-bag-fill"></i> MyShop
            </a>
        </div>

        <ul class="nav-menu">
            <li><a href="<?php echo BASE_URL; ?>../public/index.php">Accueil</a></li>
            <li><a href="<?php echo BASE_URL; ?>../public/catalogue.php">Catalogue</a></li>
            
            <li>
                <a href="<?php echo BASE_URL; ?>../public/cart.php">
                    <i class="bi bi-cart"></i> Panier
                    </a>
            </li>

            <li>
                <a href="<?php echo BASE_URL; ?>../public/mes-commandes.php"> Historique</a>
            </li>

            <li>
                <a href="<?php echo BASE_URL; ?>../public/about.php"> About</a>
            </li>

            <?php if(isset($_SESSION['user'])): ?>
                <li><a href="#" style="color: var(--primary); font-weight:bold;">
                    <?php echo htmlspecialchars($_SESSION['user']['nom']); ?>
                </a></li>
                
                <?php if($_SESSION['user']['role'] === 'admin'): ?>
                    <li><a href="<?php echo BASE_URL; ?>../admin/dashboard.php">Admin</a></li>
                <?php endif; ?>
                
                <li><a href="<?php echo BASE_URL; ?>../views/logout.php" style="color: var(--danger);">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>../views/login.php">Connexion</a></li>
                <li><a href="<?php echo BASE_URL; ?>../views/register.php" class="btn btn-primary btn-auto" style="color: white; padding: 8px 15px;">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<main>