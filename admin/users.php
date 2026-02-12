<?php
// 1. Inclusions
require_once '../config/config.php';
require_once '../includes/header.php';
require_once '../src/User.php';

// 2. Sécurité Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo "<script>window.location.href='../public/index.php';</script>";
    exit;
}

$pdo = connectDB();
$userManager = new User($pdo); // On utilise ta classe User

$message = '';
$messageType = '';

// 3. Traitement (Suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $userId = intval($_POST['user_id']);
    
    // Sécurité supplémentaire : On empêche de se supprimer soi-même
    if ($userId === $_SESSION['user']['id']) {
        $message = "Vous ne pouvez pas supprimer votre propre compte admin.";
        $messageType = "danger";
    } else {
        $result = $userManager->deleteUser($userId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

// 4. Récupération (Méthode optimisée)
$users = $userManager->getAllClients();
?>

<div class="container" style="padding-top: 20px;">
    
    <?php include 'includes/admin_menu.php'; ?>

    <h1 style="margin-bottom: 20px;">Gestion des utilisateurs</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="cart-items">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Inscrit le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px;">Aucun client inscrit pour le moment.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?php echo $u['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($u['nom']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo date('d/m/Y à H:i', strtotime($u['date_inscription'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-auto" 
                                            style="padding: 5px 10px; font-size: 0.8rem;"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?\nCette action est irréversible et supprimera son historique.')">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>