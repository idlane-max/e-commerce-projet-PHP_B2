<?php
// On démarre la session (nécessaire pour pouvoir la détruire)
session_start();

// On détruit toutes les variables de session
$_SESSION = [];

// On détruit la session côté serveur
session_destroy();

// Redirection vers l'accueil
header("Location: ../public/index.php");
exit();
?>