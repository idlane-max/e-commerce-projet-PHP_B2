<?php
/**
 * Gestion des produits (admin)
 */
require_once '../config/config.php';
require_once '../src/User.php';
require_once '../src/Product.php';

// Vérifier que l'utilisateur est admin
if (!User::isAdmin()) {
    header('Location: login.php');
    exit;
}

$db = connectDB();
$product = new Product($db);

$message = '';
$messageType = '';
$action = $_GET['action'] ?? 'list';
$productId = $_GET['id'] ?? null;

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? 'list';
    
    if ($action === 'add') {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix = $_POST['prix'] ?? '';
        $stock = $_POST['stock'] ?? '';
        $image = $_POST['image'] ?? 'default.jpg';
        
        $result = $product->addProduct($nom, $description, $prix, $image, $stock);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $action = 'list';
        }
    } elseif ($action === 'edit') {
        $productId = $_POST['product_id'] ?? '';
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix = $_POST['prix'] ?? '';
        $stock = $_POST['stock'] ?? '';
        $image = $_POST['image'] ?? '';
        
        $result = $product->updateProduct($productId, $nom, $description, $prix, $image, $stock);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $action = 'list';
        }
    } elseif ($action === 'delete') {
        $productId = $_POST['product_id'] ?? '';
        
        $result = $product->deleteProduct($productId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        $action = 'list';
    }
}

// Récupérer les produits
$products = $product->getAllProducts();
$editProduct = null;

if ($action === 'edit' && $productId) {
    $editProduct = $product->getProductById($productId);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des produits - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .admin-nav {
            background-color: #333;
            padding: 0;
        }
        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .admin-nav li {
            margin: 0;
        }
        .admin-nav a {
            color: white;
            padding: 15px 20px;
            display: block;
            text-decoration: none;
        }
        .admin-nav a:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <ul>
            <li><a href="dashboard.php">Tableau de bord</a></li>
            <li><a href="products.php">Produits</a></li>
            <li><a href="users.php">Utilisateurs</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </nav>

    <!-- Products Management -->
    <div class="container">
        <h1>Gestion des produits</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
            <div>
                <a href="?action=add" class="btn btn-primary">+ Ajouter un produit</a>
                
                <table class="admin-table" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f5f5f5;">
                            <th style="padding: 10px; border: 1px solid #ddd;">ID</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Nom</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Description</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Prix</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Stock</th>
                            <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $prod['id']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($prod['nom']); ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo substr(htmlspecialchars($prod['description']), 0, 50); ?>...</td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo number_format($prod['prix'], 2); ?> €</td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $prod['quantite_en_stock']; ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    <a href="?action=edit&id=<?php echo $prod['id']; ?>" class="btn btn-small">Modifier</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="form_action" value="delete">
                                        <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <div class="form-section" style="max-width: 600px;">
                <h2><?php echo $action === 'add' ? 'Ajouter un produit' : 'Modifier un produit'; ?></h2>
                
                <form method="POST">
                    <input type="hidden" name="form_action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit' && $editProduct): ?>
                        <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="nom">Nom du produit:</label>
                        <input type="text" id="nom" name="nom" required 
                               value="<?php echo $editProduct ? htmlspecialchars($editProduct['nom']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" required rows="5"><?php echo $editProduct ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="prix">Prix:</label>
                        <input type="number" id="prix" name="prix" step="0.01" required 
                               value="<?php echo $editProduct ? $editProduct['prix'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock:</label>
                        <input type="number" id="stock" name="stock" required 
                               value="<?php echo $editProduct ? $editProduct['quantite_en_stock'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image:</label>
                        <input type="text" id="image" name="image" placeholder="default.jpg" 
                               value="<?php echo $editProduct ? htmlspecialchars($editProduct['image']) : ''; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Ajouter' : 'Modifier'; ?>
                    </button>
                    <a href="products.php" class="btn btn-secondary">Annuler</a>
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
