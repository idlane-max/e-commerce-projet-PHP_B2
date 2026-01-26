<?php
/**
 * Gestion des utilisateurs (admin)
 */
require_once '../config/config.php';
require_once '../src/User.php';

// Vérifier que l'utilisateur est admin
if (!User::isAdmin()) {
    header('Location: login.php');
    exit;
}

$db = connectDB();
$user = new User($db);

$message = '';
$messageType = '';

// Supprimer un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $userId = $_POST['user_id'] ?? '';
    
    $result = $user->deleteUser($userId);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
}

// Récupérer tous les utilisateurs
$users = $user->getAllUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs - Admin</title>
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

    <!-- Users Management -->
    <div class="container">
        <h1>Gestion des utilisateurs</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <table class="admin-table" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f5f5f5;">
                    <th style="padding: 10px; border: 1px solid #ddd;">ID</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Nom</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Email</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Date d'inscription</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $u['id']; ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($u['nom']); ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo date('d/m/Y', strtotime($u['date_inscription'])); ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
