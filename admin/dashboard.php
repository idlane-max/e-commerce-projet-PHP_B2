<?php
require_once '../config/config.php';

// Sécurité Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../includes/header.php';

$pdo = connectDB();

// --- TES STATISTIQUES (Version PDO) ---

// 1. Total Produits
$stmt = $pdo->query("SELECT COUNT(*) FROM items");
$productCount = $stmt->fetchColumn();

// 2. Total Utilisateurs (Clients uniquement, comme tu voulais)
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'");
$userCount = $stmt->fetchColumn();

// 3. Total Commandes (Lignes de commande)
// Note: Si tu veux le nombre de factures payées, utilise la table invoice
$stmt = $pdo->query("SELECT COUNT(*) FROM invoice"); 
$orderCount = $stmt->fetchColumn();

// 4. Revenus Totaux (Ta super idée !)
// COALESCE permet d'afficher 0 si c'est vide au lieu de NULL
$stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM invoice");
$revenue = $stmt->fetchColumn();
?>

<div class="container" style="padding-top: 20px;">
    
    <?php include 'includes/admin_menu.php'; ?>

    <h1 style="margin-bottom: 30px;">Tableau de bord</h1>
    <p class="mb-4">Bonjour, <strong><?php echo htmlspecialchars($_SESSION['user']['nom']); ?></strong>. Voici ce qui se passe sur votre boutique.</p>

    <div class="dashboard-grid">
        
        <div class="stat-card green">
            <span class="icon" style="color: var(--success);"><i class="bi bi-currency-euro"></i></span>
            <h3>Chiffre d'Affaires</h3>
            <div class="number"><?php echo number_format($revenue, 2, ',', ' '); ?> €</div>
        </div>

        <div class="stat-card purple">
            <span class="icon" style="color: var(--secondary);"><i class="bi bi-receipt"></i></span>
            <h3>Commandes validées</h3>
            <div class="number"><?php echo $orderCount; ?></div>
        </div>

        <div class="stat-card blue">
            <span class="icon" style="color: var(--primary);"><i class="bi bi-box-seam"></i></span>
            <h3>Produits en ligne</h3>
            <div class="number"><?php echo $productCount; ?></div>
            <a href="products.php" class="btn btn-primary btn-auto btn-small" style="margin-top: 10px; font-size: 0.8rem;">Gérer</a>
        </div>

        <div class="stat-card orange">
            <span class="icon" style="color: #f59e0b;"><i class="bi bi-people"></i></span>
            <h3>Clients Inscrits</h3>
            <div class="number"><?php echo $userCount; ?></div>
            <a href="users.php" class="btn btn-primary btn-auto btn-small" style="margin-top: 10px; font-size: 0.8rem;">Gérer</a>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>