<nav class="admin-nav">
    <ul>
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Tableau de bord</a></li>
        <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Produits</a></li>
        <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">Utilisateurs</a></li>
        <li><a href="../public/index.php" target="_blank">Voir le site</a></li>
        <li><a href="../views/logout.php" style="color: #ef476f;">Déconnexion</a></li>
    </ul>
</nav>