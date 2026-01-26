<?php
/**
 * Connexion utilisateur
 */
require_once '../config/config.php';
require_once '../src/User.php';

// Rediriger si déjà connecté
if (User::isLoggedIn()) {
    header('Location: ../public/index.php');
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
    
    if ($result['success']) {
        header('Location: ../public/index.php');
        exit;
    }
}

$db->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - E-Commerce</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>Connexion</h1>
            
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
            
            <p>Pas encore de compte? <a href="register.php">Inscrivez-vous ici</a></p>
        </div>
    </div>
</body>
</html>
