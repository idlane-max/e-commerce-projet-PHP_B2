<?php
/**
 * Page catalogue des produits
 */
require_once '../config/config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../src/Product.php';

// Connexion et récupération
$pdo = connectDB();
$productManager = new Product($pdo);
$products = $productManager->getAllProducts();
?>

<div class="container">
    <div class="catalog-header">
        <h1>Notre Catalogue</h1>
        <p>Découvrez nos meilleures sélections du moment.</p>
    </div>

    <div class="products-grid">
        <?php foreach($products as $product): ?>
            <article class="product-card">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['nom']); ?>">
                </div>
                
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['nom']); ?></h3>
                    
                    <p class="product-description">
                        <?php echo substr(htmlspecialchars($product['description']), 0, 60) . '...'; ?>
                    </p>
                    
                    <span class="price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> €</span>
                    
                    <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $product['stock'] > 0 ? 'En stock' : 'Rupture de stock'; ?>
                    </div>

                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                        Voir le détail
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>