<?php
// 1. Inclusions essentielles
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php'; // Charge le HTML, CSS et le Menu
require_once __DIR__ . '/../src/User.php';   // Charge la classe

$message = '';
$messageType = '';

// 2. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = connectDB(); // On récupère la connexion PDO
    $user = new User($pdo); // On passe PDO au constructeur

    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $result = $user->register($nom, $email, $password, $confirmPassword);
    
    $message = $result['message'];
    // On adapte la classe CSS (success ou danger)
    $messageType = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        // Redirection JS après 2 secondes
        echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 2000);</script>';
    }
}
?>

<div class="container">
    <div class="form-container">
        <h1 style="text-align:center; margin-bottom:20px;">Inscription</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="nom">Nom complet :</label>
                <input type="text" id="nom" name="nom" required value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            
            <button type="submit" class="btn">S'inscrire</button>
        </form>
        
        <p style="text-align:center; margin-top:15px;">
            Vous avez déjà un compte ? <a href="login.php" style="color: var(--primary-color);">Connectez-vous ici</a>
        </p>
    </div>
</div>

<?php 
// 4. On ferme la page avec le footer
require_once __DIR__ . '/../includes/footer.php'; 
?>