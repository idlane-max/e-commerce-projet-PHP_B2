<?php
// Parnier
// 1. Inclusions & Config
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../src/Cart.php'; 

// 2. Vérification Connexion
if (!isset($_SESSION['user'])) {
    echo "<script>window.location.href='../views/login.php';</script>";
    exit();
}

$pdo = connectDB();
$cart = new Cart($pdo);

$message = '';
$messageType = '';

// 3. Traitement des actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // A. Supprimer un article
    if ($action === 'remove') {
        $productId = intval($_POST['product_id'] ?? 0);
        $result = $cart->removeFromCart($productId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    } 
    // B. Mettre à jour la quantité
    elseif ($action === 'update') {
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        $result = $cart->updateQuantity($productId, $quantity);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    } 
    // C. Vider le panier
    elseif ($action === 'clear') {
        $cart->clearCart();
        $message = "Panier vidé.";
        $messageType = 'success';
    } 
    // D. Passer la commande (Checkout)
    elseif ($action === 'checkout') {
        $adresse = trim($_POST['adresse'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $codePostal = trim($_POST['code_postal'] ?? '');
        
        $userId = $_SESSION['user']['id'];
        
        $result = $cart->checkout($userId, $adresse, $ville, $codePostal);
        
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
        
        if ($result['success']) {
            // On redirige vers la page de confirmation avec l'ID de la facture
            $invoiceId = $result['invoice_id'] ?? 0;
            echo "<script>setTimeout(function(){ window.location.href='order-confirmation.php?invoice_id=" . $invoiceId . "'; }, 1000);</script>";
        }
    }
}

// 4. Récupération des données
$cartItems = $cart->getCartItems();
$total = $cart->getCartTotal();
$cartCount = $cart->getCartCount();
?>

<div class="container">
    <h1 style="margin-top: 30px; margin-bottom: 20px;">Mon Panier</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div style="text-align: center; padding: 60px 0; background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: #ddd;"></i>
            <h3 style="margin: 20px 0;">Votre panier est vide</h3>
            <a href="catalogue.php" class="btn btn-primary btn-auto">Retourner au catalogue</a>
        </div>
    <?php else: ?>

        <div class="cart-container">
            
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Quantité</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="cart-product-info">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Img">
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['nom']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price'], 2); ?> €</td>
                                <td>
                                    <form method="POST" style="display:flex; gap:5px; align-items:center;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" 
                                               style="width: 50px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; text-align: center;">
                                        
                                        <button type="submit" class="btn btn-primary btn-auto" style="padding: 5px 10px; font-size: 0.8rem;" title="Mettre à jour">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </form>
                                </td>
                                <td style="font-weight: bold; color: var(--primary);">
                                    <?php echo number_format($item['total'], 2); ?> €
                                </td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="cart-action-btn" style="background:none; border:none;" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="padding: 20px;">
                    <form method="POST">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-danger btn-auto" onclick="return confirm('Tout supprimer ?');">
                            <i class="bi bi-trash"></i> Vider le panier
                        </button>
                    </form>
                </div>
            </div>

            <div class="cart-summary">
                <h2>Résumé</h2>
                <div class="summary-row">
                    <span>Articles (<?php echo $cartCount; ?>)</span>
                    <span><?php echo number_format($total, 2); ?> €</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total à payer</span>
                    <span><?php echo number_format($total, 2); ?> €</span>
                </div>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

                <h3 style="font-size: 1.2rem; margin-bottom: 15px;">Livraison</h3>
                
                <form method="POST" action="cart.php">
                    <input type="hidden" name="action" value="checkout">
                    
                    <div class="form-group">
                        <label for="adresse">Adresse complète</label>
                        <input type="text" id="adresse" name="adresse" required placeholder="12 rue de la Paix">
                    </div>
                    
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" required placeholder="Paris">
                    </div>
                    
                    <div class="form-group">
                        <label for="code_postal">Code Postal</label>
                        <input type="text" id="code_postal" name="code_postal" required placeholder="75000">
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                        <i class="bi bi-credit-card"></i> Valider et Payer
                    </button>
                </form>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>