<?php
/**
 * Page de connexion administrateur
 */
require_once '../config/config.php';
require_once '../src/User.php';

// Rediriger si déjà connecté en tant qu'admin
if (User::isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$db = connectDB();
$user = new User($db);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = $user->login($email, $password);
    
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
    if ($result['success'] && $result['role'] === 'admin') {
        header('Location: dashboard.php');
        exit;
    } elseif ($result['success']) {
        $message = 'Accès refusé: vous n\'êtes pas administrateur';
        $messageType = 'error';
        // Déconnecter l'utilisateur
        $user->logout();
    }
}

$db->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Commerce</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-form admin-login">
            <h1>Administration</h1>
            <p>Connexion des administrateurs</p>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>
            
            <p><a href="../public/index.php">Retour à la boutique</a></p>
        </div>
    </div>
</body>
</html>
