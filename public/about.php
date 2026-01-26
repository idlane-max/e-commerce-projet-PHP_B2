<?php
/**
 * Page À propos
 */
require_once '../config/config.php';
require_once '../src/User.php';

$db = connectDB();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qui sommes-nous - E-Commerce</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-content">
            <div class="logo">
                <h1><a href="index.php">E-Commerce</a></h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="articles.php">Articles</a></li>
                <li><a href="about.php">Qui sommes-nous?</a></li>
                <?php if (User::isLoggedIn()): ?>
                    <li><a href="cart.php">Panier <span class="cart-badge" id="cart-count">0</span></a></li>
                    <li><a href="../views/logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="../views/login.php">Connexion</a></li>
                    <li><a href="../views/register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- About Section -->
    <div class="container about-section">
        <h1>Qui sommes-nous?</h1>
        
        <div class="about-content">
            <h2>Notre histoire</h2>
            <p>
                Bienvenue dans notre boutique en ligne! Nous sommes une entreprise passionnée par 
                la fourniture de produits de haute qualité à nos clients. Fondée en 2026, notre mission 
                est de vous offrir une expérience d'achat exceptionnelle avec un excellent service client.
            </p>
            
            <h2>Nos valeurs</h2>
            <ul>
                <li><strong>Qualité:</strong> Tous nos produits sont sélectionnés avec soin pour garantir la meilleure qualité.</li>
                <li><strong>Intégrité:</strong> Nous opérons avec transparence et honnêteté envers nos clients.</li>
                <li><strong>Engagement:</strong> Nous sommes dédiés à fournir un service client exceptionnel.</li>
                <li><strong>Innovation:</strong> Nous recherchons constamment de nouveaux produits et améliorations.</li>
            </ul>
            
            <h2>Notre équipe</h2>
            <p>
                Notre équipe est composée de professionnels expérimentés et passionnés qui travaillent 
                ensemble pour vous offrir la meilleure expérience possible. Nous sommes toujours heureux 
                de répondre à vos questions et de vous aider dans vos achats.
            </p>
            
            <h2>Nous contacter</h2>
            <p>
                Email: <a href="mailto:info@ecommerce.com">info@ecommerce.com</a><br>
                Téléphone: +33 (0) 1 23 45 67 89<br>
                Adresse: 123 Rue de la Boutique, 75000 Paris, France
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2026 E-Commerce. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        function updateCartCount() {
            fetch('cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const element = document.getElementById('cart-count');
                    if (element) element.textContent = data.count;
                });
        }
        
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</body>
</html>
<?php $db->close(); ?>
