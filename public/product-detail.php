<?php
/**
 * Page détail d'un produit
 */
require_once '../config/config.php';
require_once '../src/Product.php';
require_once '../src/User.php';

$db = connectDB();
$product = new Product($db);

$productId = $_GET['id'] ?? null;

if (!$productId || !is_numeric($productId)) {
    header('Location: articles.php');
    exit;
}

$productDetail = $product->getProductById($productId);

if (!$productDetail) {
    header('Location: articles.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productDetail['nom']); ?> - E-Commerce</title>
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

    <!-- Product Detail -->
    <div class="container product-detail">
        <a href="articles.php" class="btn btn-secondary back-link">&larr; Retour aux articles</a>
        
        <div class="product-detail-content">
            <div class="product-image-large">
                <img src="images/<?php echo htmlspecialchars($productDetail['image']); ?>" 
                     alt="<?php echo htmlspecialchars($productDetail['nom']); ?>">
            </div>
            
            <div class="product-detail-info">
                <h1><?php echo htmlspecialchars($productDetail['nom']); ?></h1>
                
                <div class="price-info">
                    <p class="price"><?php echo number_format($productDetail['prix'], 2); ?> €</p>
                </div>
                
                <div class="stock-info">
                    <p class="stock <?php echo $productDetail['quantite_en_stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $productDetail['quantite_en_stock'] > 0 ? 'En stock (' . $productDetail['quantite_en_stock'] . ' disponible(s))' : 'Rupture de stock'; ?>
                    </p>
                </div>
                
                <div class="description-full">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($productDetail['description'])); ?></p>
                </div>
                
                <?php if (User::isLoggedIn() && $productDetail['quantite_en_stock'] > 0): ?>
                    <form class="add-to-cart-form" id="add-to-cart-form">
                        <div class="form-group">
                            <label for="quantity">Quantité:</label>
                            <input type="number" id="quantity" name="quantity" min="1" 
                                   max="<?php echo $productDetail['quantite_en_stock']; ?>" value="1">
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter au panier</button>
                    </form>
                <?php elseif (!User::isLoggedIn()): ?>
                    <p><a href="../views/login.php" class="btn btn-primary">Connectez-vous pour acheter</a></p>
                <?php else: ?>
                    <p class="btn btn-disabled">Produit indisponible</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2026 E-Commerce. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const quantity = document.getElementById('quantity').value;
            const productId = <?php echo $productId; ?>;
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    updateCartCount();
                } else {
                    alert(data.message);
                }
            });
        });
        
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
