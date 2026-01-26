<?php
/**
 * Page catalogue des produits
 */
require_once '../config/config.php';
require_once '../src/Product.php';
require_once '../src/User.php';

$db = connectDB();
$product = new Product($db);

$products = $product->getAllProducts();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles - E-Commerce</title>
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

    <!-- Main Content -->
    <div class="container">
        <h1>Catalogue des produits</h1>
        
        <div class="products-grid">
            <?php foreach ($products as $prod): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="images/<?php echo htmlspecialchars($prod['image']); ?>" 
                             alt="<?php echo htmlspecialchars($prod['nom']); ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($prod['nom']); ?></h3>
                        <p class="description"><?php echo substr(htmlspecialchars($prod['description']), 0, 100) . '...'; ?></p>
                        <p class="price"><?php echo number_format($prod['prix'], 2); ?> €</p>
                        <p class="stock <?php echo $prod['quantite_en_stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                            <?php echo $prod['quantite_en_stock'] > 0 ? 'En stock' : 'Rupture de stock'; ?>
                        </p>
                        <a href="product-detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-secondary">
                            Voir détails
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
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
                    document.getElementById('cart-count').textContent = data.count;
                });
        }
        
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</body>
</html>
<?php $db->close(); ?>
