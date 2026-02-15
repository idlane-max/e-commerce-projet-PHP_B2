<?php
// 1. Inclusions (Configuration + Design + Classe)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php'; // AFFICHE LE MENU ET LE CSS
require_once __DIR__ . '/../src/User.php';

// 2. Redirection si déjà connecté
// On vérifie directement la session, c'est plus sûr et rapide
if (isset($_SESSION['user'])) {
    header('Location: ../public/index.php');
    exit();
}

$message = '';
$messageType = '';

// 3. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = connectDB(); // Connexion PDO
    $user = new User($pdo);

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Appel de ta méthode login (qui est dans User.php)
    $result = $user->login($email, $password);
    
    if ($result['success']) {
        // Succès : Redirection vers l'accueil
        header('Location: ../public/index.php');
        exit();
    } else {
        // Erreur : On affiche le message
        $message = $result['message'];
        $messageType = 'danger'; // 'danger' pour le rouge CSS
    }
}
?>

<div class="container">
    <div class="form-container">
        <h1 style="text-align:center; margin-bottom:20px;">Connexion</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
        </form>
        
        <p style="text-align:center; margin-top:15px;">
            Pas encore de compte ? <a href="register.php" style="color: var(--primary-color);">Inscrivez-vous ici</a>
        </p>
    </div>
</div>

<?php 
// 5. On ferme avec le footer
require_once __DIR__ . '/../includes/footer.php'; 
?>