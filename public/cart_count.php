<?php
/**
 * API pour obtenir le nombre d'articles dans le panier
 */
require_once '../config/config.php';
require_once '../src/Cart.php';

header('Content-Type: application/json');

$cart = new Cart(connectDB());
$count = $cart->getCartCount();

echo json_encode(['count' => $count]);
?>
