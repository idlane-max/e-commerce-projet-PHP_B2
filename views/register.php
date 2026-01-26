<?php
/**
 * Inscription utilisateur
 */
require_once '../config/config.php';
require_once '../src/User.php';

$db = connectDB();
$user = new User($db);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $result = $user->register($nom, $email, $password, $confirmPassword);
    
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
        // Rediriger vers la connexion après 2 secondes
        header("refresh:2;url=login.php");
    }
}

$db->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - E-Commerce</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>Inscription</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="nom">Nom complet:</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </form>
            
            <p>Vous avez déjà un compte? <a href="login.php">Connectez-vous ici</a></p>
        </div>
    </div>
</body>
</html>
