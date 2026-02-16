<?php
// 1. Charger la config et les classes
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Cart.php';

// On démarre la session si ce n'est pas fait (nécessaire pour $_SESSION['cart'])
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 2. Vérification Login
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Veuillez vous connecter pour ajouter au panier.']);
    exit;
}

// 3. Initialisation
try {
    $pdo = connectDB();
    $cart = new Cart($pdo);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
    exit;
}

// 4. Récupération des données (CORRECTION ICI)
// On vérifie si le JS envoie 'id' OU si un formulaire envoie 'product_id'
if (isset($_POST['id'])) {
    $productId = intval($_POST['id']);
} elseif (isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
} else {
    $productId = 0;
}

$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Debugging (voir dans l'onglet Réseau du navigateur)
// error_log("ID reçu : " . $productId . " - Quantité : " . $quantity);

if ($productId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides (ID: ' . $productId . ')']);
    exit;
}

// 5. Action
$result = $cart->addToCart($productId, $quantity);

echo json_encode($result);
?>