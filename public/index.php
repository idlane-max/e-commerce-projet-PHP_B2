<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero">
    <div class="hero-bg hero-bg-1" style="background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1600&auto=format&fit=crop');"></div>

    <div class="hero-bg hero-bg-2" style="background-image: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1600&auto=format&fit=crop');"></div>

    <div class="hero-overlay"></div>

    <div class="hero-content">
        <div class="container">
            <h1>Le style à portée de clic.</h1>
            <p>Découvrez notre nouvelle collection 2026. Qualité premium, design unique.</p>
            <a href="catalogue.php" class="btn btn-primary btn-auto" style="font-size: 1.2rem; padding: 15px 30px;">Voir le catalogue</a>
        </div>
    </div>
</section>

<div class="container">
    <h2 style="text-align:center; margin-bottom: 40px; font-weight: 600;">Pourquoi nous choisir ?</h2>
    
    <div class="features-grid-home">
        <div style="padding: 20px;">
            <i class="bi bi-truck" style="font-size: 3rem; color: var(--primary);"></i>
            <h3>Livraison Rapide</h3>
            <p>Expédition en 24h partout en France.</p>
        </div>
        <div style="padding: 20px;">
            <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--primary);"></i>
            <h3>Paiement Sécurisé</h3>
            <p>Transactions cryptées et garanties.</p>
        </div>
        <div style="padding: 20px;">
            <i class="bi bi-heart" style="font-size: 3rem; color: var(--primary);"></i>
            <h3>Service Client</h3>
            <p>Une équipe à votre écoute 7j/7.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>