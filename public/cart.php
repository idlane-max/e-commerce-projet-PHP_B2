<?php
/**
 * Page du panier d'achat
 */
require_once '../config/config.php';
require_once '../src/Cart.php';
require_once '../src/User.php';

// Vérifier que l'utilisateur est connecté
if (!User::isLoggedIn()) {
    header('Location: ../views/login.php');
    exit;
}

$db = connectDB();
$cart = new Cart($db);

$message = '';
$messageType = '';

// Traiter les actions du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'remove') {
        $productId = $_POST['product_id'] ?? '';
        $result = $cart->removeFromCart($productId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif ($action === 'update') {
        $productId = $_POST['product_id'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $result = $cart->updateQuantity($productId, $quantity);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif ($action === 'clear') {
        $result = $cart->clearCart();
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif ($action === 'checkout') {
        $adresse = trim($_POST['adresse'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $codePostal = trim($_POST['code_postal'] ?? '');
        
        $result = $cart->checkout($adresse, $ville, $codePostal);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            // Rediriger vers une page de confirmation après 2 secondes
            header("refresh:2;url=order-confirmation.php?invoice_id=" . $result['invoice_id']);
        }
    }
}

$cartItems = $cart->getCartItems();
$total = $cart->getCartTotal();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - E-Commerce</title>
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
                <li><a href="cart.php">Panier <span class="cart-badge" id="cart-count"><?php echo $cart->getCartCount(); ?></span></a></li>
                <li><a href="../views/logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <!-- Cart Page -->
    <div class="container">
        <h1>Panier d'achat</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cartItems)): ?>
            <p>Votre panier est vide.</p>
            <a href="articles.php" class="btn btn-primary">Continuer vos achats</a>
        <?php else: ?>
            <div class="cart-content">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nom']); ?></td>
                                <td><?php echo number_format($item['price'], 2); ?> €</td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 50px;">
                                        <button type="submit" class="btn-small">Mettre à jour</button>
                                    </form>
                                </td>
                                <td><?php echo number_format($item['total'], 2); ?> €</td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <p class="total">Total: <strong><?php echo number_format($total, 2); ?> €</strong></p>
                </div>
                
                <!-- Checkout Form -->
                <form method="POST" class="checkout-form">
                    <input type="hidden" name="action" value="checkout">
                    
                    <h3>Adresse de livraison</h3>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse:</label>
                        <input type="text" id="adresse" name="adresse" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ville">Ville:</label>
                        <input type="text" id="ville" name="ville" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="code_postal">Code postal:</label>
                        <input type="text" id="code_postal" name="code_postal" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Finaliser la commande</button>
                </form>
                
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-secondary">Vider le panier</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2026 E-Commerce. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
<?php $db->close(); ?>
