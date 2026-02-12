<?php
require_once '../config/config.php';
require_once '../src/Product.php';
session_start();

// Sécurité Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../public/index.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $pdo = connectDB();
    $productManager = new Product($pdo);
    $productManager->deleteProduct($id);
}

header('Location: products.php');
exit();
?>