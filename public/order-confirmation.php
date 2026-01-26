<?php
/**
 * Page de confirmation de commande
 */
require_once '../config/config.php';
require_once '../src/User.php';

if (!User::isLoggedIn()) {
    header('Location: ../views/login.php');
    exit;
}

$db = connectDB();

$invoiceId = $_GET['invoice_id'] ?? null;

if (!$invoiceId) {
    header('Location: index.php');
    exit;
}

// Récupérer les détails de la facture
$stmt = $db->prepare("
    SELECT id, montant, adresse_facturation, ville, code_postal, date_transaction 
    FROM invoice 
    WHERE id = ? AND id_user = ?
");
$stmt->bind_param("ii", $invoiceId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$invoice = $result->fetch_assoc();

// Récupérer les articles de la commande
$stmt = $db->prepare("
    SELECT o.id_item, i.nom, o.quantité, o.prix_unitaire, (o.quantité * o.prix_unitaire) as total
    FROM orders o
    JOIN items i ON o.id_item = i.id
    WHERE o.id_user = ? AND o.date_commande >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ORDER BY o.date_commande DESC
    LIMIT 50
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orderItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande - E-Commerce</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-content">
            <div class="logo">
                <h1><a href="index.php">E-Commerce</a></h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="articles.php">Articles</a></li>
                <li><a href="cart.php">Panier</a></li>
                <li><a href="../views/logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <!-- Confirmation -->
    <div class="container order-confirmation">
        <div class="confirmation-message">
            <h1>✓ Commande confirmée!</h1>
            <p>Merci pour votre achat, <?php echo htmlspecialchars($_SESSION['user_nom']); ?>!</p>
        </div>
        
        <div class="invoice-details">
            <h2>Détails de votre facture</h2>
            
            <div class="invoice-info">
                <p><strong>Numéro de facture:</strong> #<?php echo $invoice['id']; ?></p>
                <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($invoice['date_transaction'])); ?></p>
            </div>
            
            <h3>Adresse de livraison</h3>
            <p>
                <?php echo htmlspecialchars($invoice['adresse_facturation']); ?><br>
                <?php echo htmlspecialchars($invoice['code_postal']); ?> <?php echo htmlspecialchars($invoice['ville']); ?>
            </p>
            
            <h3>Articles commandés</h3>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nom']); ?></td>
                            <td><?php echo $item['quantité']; ?></td>
                            <td><?php echo number_format($item['prix_unitaire'], 2); ?> €</td>
                            <td><?php echo number_format($item['total'], 2); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="invoice-total">
                <p class="total">Total: <strong><?php echo number_format($invoice['montant'], 2); ?> €</strong></p>
            </div>
        </div>
        
        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
        <a href="articles.php" class="btn btn-secondary">Continuer vos achats</a>
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
