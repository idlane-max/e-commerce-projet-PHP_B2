<?php
// 1. Inclusions
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../src/Product.php';

// 2. Récupération de l'ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>window.location.href='catalogue.php';</script>";
    exit();
}

// 3. Récupération du produit via PDO
$pdo = connectDB();
$productManager = new Product($pdo);
$product = $productManager->getProductById($id);

if (!$product) {
    echo "<div class='container'><div class='alert alert-danger'>Produit introuvable.</div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Gestion des classes CSS pour le stock
$isInStock = $product['stock'] > 0;
$stockClass = $isInStock ? 'in-stock' : 'out-of-stock';
$stockText = $isInStock ? 'En stock (' . $product['stock'] . ' disponibles)' : 'Rupture de stock';
?>

<div class="container">
    <nav style="margin-top: 20px; font-size: 0.9rem; color: var(--text-light);">
        <a href="index.php">Accueil</a> &gt; 
        <a href="catalogue.php">Catalogue</a> &gt; 
        <span style="color: var(--text-dark); font-weight: 600;"><?php echo htmlspecialchars($product['nom']); ?></span>
    </nav>

    <div class="product-detail-container">
        
        <div class="detail-image-wrapper">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['nom']); ?>">
        </div>

        <div class="detail-info-wrapper">
            <h1><?php echo htmlspecialchars($product['nom']); ?></h1>
            
            <div class="stock-badge-large <?php echo $stockClass; ?>">
                <i class="bi <?php echo $isInStock ? 'bi-check-circle' : 'bi-x-circle'; ?>"></i> 
                <?php echo $stockText; ?>
            </div>

            <span class="detail-price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> €</span>

            <div class="detail-description">
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <div class="add-to-cart-box">
                <?php if ($isInStock): ?>
                    <form id="add-to-cart-form" class="cart-form">
                        <input type="hidden" id="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div>
                            <label style="display:block; margin-bottom:5px; font-size:0.9rem;">Quantité</label>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                        </div>

                        <button type="submit" class="btn btn-primary" style="flex-grow: 1;">
                            <i class="bi bi-cart-plus"></i> Ajouter au panier
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-disabled" disabled style="width:100%;">Indisponible</button>
                <?php endif; ?>
            </div>
            
            <div id="ajax-message" style="margin-top: 15px; display: none;"></div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('add-to-cart-form');
    
    if(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // On empêche le rechargement de la page
            
            const productId = document.getElementById('product_id').value;
            const quantity = document.getElementById('quantity').value;
            const messageDiv = document.getElementById('ajax-message');

            // Envoi d'une requête AJAX vers add_to_cart.php pour ajouter un produit au panier.
            // On envoie l'id du produit et la quantité en POST, puis on récupère la réponse JSON.
            // Selon le résultat (success ou erreur), on affiche un message temporaire à l'utilisateur.
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                // Affichage du message
                messageDiv.style.display = 'block';
                if(data.success) {
                    messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    // Mise à jour du compteur dans le header 
                    // updateCartCount(); 
                } else {
                    messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
                
                // Masquer le message après 3 secondes
                setTimeout(() => { messageDiv.style.display = 'none'; }, 3000);
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>