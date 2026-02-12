<?php
require_once '../config/config.php';
require_once '../includes/header.php';
require_once '../src/Product.php';

// Sécurité Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo "<script>window.location.href='../public/index.php';</script>";
    exit();
}

$pdo = connectDB();
$productManager = new Product($pdo);

$message = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEditing = $id > 0;

// Valeurs par défaut (vide pour création)
$nom = '';
$desc = '';
$prix = '';
$image = '';
$stock = '';

// Si mode Édition, on charge les données du produit
if ($isEditing) {
    $product = $productManager->getProductById($id);
    if ($product) {
        $nom = $product['nom'];
        $desc = $product['description'];
        $prix = $product['prix'];
        $image = $product['image'];
        $stock = $product['stock'];
    } else {
        echo "<script>window.location.href='products.php';</script>";
        exit();
    }
}

// TRAITEMENT DU FORMULAIRE (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $desc = $_POST['description'];
    $prix = floatval($_POST['prix']);
    $image = $_POST['image'];
    $stock = intval($_POST['stock']);

    if ($isEditing) {
        // Mise à jour
        $res = $productManager->updateProduct($id, $nom, $desc, $prix, $image, $stock);
    } else {
        // Création
        $res = $productManager->addProduct($nom, $desc, $prix, $image, $stock);
    }

    if ($res['success']) {
        // Redirection vers la liste après succès
        echo "<script>window.location.href='products.php';</script>";
        exit();
    } else {
        $message = '<div class="alert alert-danger">' . $res['message'] . '</div>';
    }
}
?>

<div class="container" style="padding: 40px 0;">
    <div class="form-container" style="max-width: 800px;">
        <h1 style="text-align: center; margin-bottom: 30px;">
            <?php echo $isEditing ? 'Modifier le produit' : 'Ajouter un produit'; ?>
        </h1>
        
        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nom du produit</label>
                <input type="text" name="nom" required value="<?php echo htmlspecialchars($nom); ?>">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"><?php echo htmlspecialchars($desc); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Prix (€)</label>
                    <input type="number" step="0.01" name="prix" required value="<?php echo htmlspecialchars($prix); ?>">
                </div>
                <div class="form-group">
                    <label>Stock initial</label>
                    <input type="number" name="stock" required value="<?php echo htmlspecialchars($stock); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>URL de l'image</label>
                <input type="url" name="image" placeholder="https://..." value="<?php echo htmlspecialchars($image); ?>">
                <small style="color: #888;">Pour l'instant, copiez l'URL d'une image depuis Google/Unsplash.</small>
            </div>

            <div style="display: flex; gap: 20px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <?php echo $isEditing ? 'Enregistrer les modifications' : 'Créer le produit'; ?>
                </button>
                <a href="products.php" class="btn btn-secondary" style="text-align: center;">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>