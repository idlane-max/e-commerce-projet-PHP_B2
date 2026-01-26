<?php
/**
 * Tableau de bord administrateur
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
$user = new User($db);
$product = new Product($db);

// Obtenir les statistiques
$stmt = $db->prepare("SELECT COUNT(*) as total FROM items");
$stmt->execute();
$productCount = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE rôle = 'client'");
$stmt->execute();
$userCount = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$orderCount = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $db->prepare("SELECT SUM(montant) as total FROM invoice");
$stmt->execute();
$revenueResult = $stmt->get_result()->fetch_assoc();
$revenue = $revenueResult['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Commerce</title>
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
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            color: #666;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
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

    <!-- Dashboard Content -->
    <div class="container">
        <h1>Tableau de bord administrateur</h1>
        <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_nom']); ?></p>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>Produits</h3>
                <div class="number"><?php echo $productCount; ?></div>
                <a href="products.php" class="btn btn-small">Gérer</a>
            </div>
            
            <div class="stat-card">
                <h3>Utilisateurs</h3>
                <div class="number"><?php echo $userCount; ?></div>
                <a href="users.php" class="btn btn-small">Gérer</a>
            </div>
            
            <div class="stat-card">
                <h3>Commandes</h3>
                <div class="number"><?php echo $orderCount; ?></div>
                <a href="orders.php" class="btn btn-small">Voir</a>
            </div>
            
            <div class="stat-card">
                <h3>Revenus</h3>
                <div class="number"><?php echo number_format($revenue, 0); ?> €</div>
            </div>
        </div>
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
