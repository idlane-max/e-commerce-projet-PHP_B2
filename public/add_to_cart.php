<?php
/**
 * API pour ajouter un produit au panier
 */
require_once '../config/config.php';
require_once '../src/Cart.php';
require_once '../src/User.php';

header('Content-Type: application/json');

if (!User::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
    exit;
}

$db = connectDB();
$cart = new Cart($db);

$productId = $_POST['product_id'] ?? '';
$quantity = $_POST['quantity'] ?? 1;

$result = $cart->addToCart($productId, $quantity);

echo json_encode($result);

$db->close();
?>
