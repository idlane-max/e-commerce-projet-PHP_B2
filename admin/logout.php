<?php
/**
 * Déconnexion administrateur
 */
require_once '../config/config.php';
require_once '../src/User.php';

$db = connectDB();
$user = new User($db);

$user->logout();

header('Location: login.php');
exit;
?>
