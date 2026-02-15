<?php
require_once '../config/config.php';
require_once '../src/Product.php';

// Sécurité Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../includes/header.php';

$pdo = connectDB();
$productManager = new Product($pdo);
$products = $productManager->getAllProducts();
?>

<div class="container" style="padding-top: 20px;">
    <?php include 'includes/admin_menu.php'; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Produits</h1>
        <a href="product_form.php" class="btn btn-primary btn-auto">+ Nouveau Produit</a>
    </div>

    <div class="cart-items">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Img</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($p['image']); ?>" style="width:40px;height:40px;border-radius:4px;object-fit:cover;"></td>
                        <td><?php echo htmlspecialchars($p['nom']); ?></td>
                        <td><?php echo number_format($p['prix'], 2); ?> €</td>
                        <td style="font-weight:bold; color: <?php echo $p['stock'] > 0 ? 'green' : 'red'; ?>">
                            <?php echo $p['stock']; ?>
                        </td>
                        <td>
                            <a href="product_form.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-auto" style="padding: 5px 10px; font-size: 0.8rem;">Modif</a>
                            <a href="delete_product.php?id=<?php echo $p['id']; ?>" class="btn btn-danger btn-auto" style="padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Supprimer ?')">X</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>