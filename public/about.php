<?php
// 1. Inclusions (Configuration + Design)
require_once '../config/config.php';
require_once '../includes/header.php'; // Inclut le menu et le CSS automatiquement
?>

<div class="container">

    <div class="about-header">
        <h1>Qui sommes-nous ?</h1>
        <p style="color: var(--text-light); font-size: 1.2rem;">
            Votre destination privilégiée pour le shopping en ligne depuis 2026.
        </p>
    </div>

    <section class="story-section">
        <div class="story-content">
            <h2>Notre histoire</h2>
            <p>
                Bienvenue dans notre boutique en ligne ! Nous sommes une entreprise passionnée par 
                la fourniture de produits de haute qualité à nos clients.
            </p>
            <p>
                Fondée en 2026, notre mission est de vous offrir une expérience d'achat exceptionnelle 
                avec un excellent service client, tout en proposant les dernières tendances du marché.
            </p>
            <div style="margin-top: 20px;">
                <a href="catalogue.php" class="btn btn-primary btn-auto">Voir nos produits</a>
            </div>
        </div>
        <div class="story-img">
            <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=800&auto=format&fit=crop" alt="Notre équipe au travail">
        </div>
    </section>

    <section class="values-section">
        <h2 style="margin-bottom: 10px;">Nos Valeurs</h2>
        <p style="color: var(--text-light); margin-bottom: 40px;">Ce qui nous guide au quotidien.</p>
        
        <div class="values-grid">
            <div class="value-card">
                <i class="bi bi-star"></i> <h3>Qualité</h3>
                <p>Tous nos produits sont sélectionnés avec soin pour garantir la meilleure qualité possible à nos clients.</p>
            </div>
            <div class="value-card">
                <i class="bi bi-shield-check"></i> <h3>Intégrité</h3>
                <p>Nous opérons avec transparence et honnêteté. Pas de frais cachés, pas de mauvaises surprises.</p>
            </div>
            <div class="value-card">
                <i class="bi bi-heart"></i> <h3>Engagement</h3>
                <p>Nous sommes dédiés à fournir un service client exceptionnel, avant, pendant et après votre achat.</p>
            </div>
            <div class="value-card">
                <i class="bi bi-lightbulb"></i> <h3>Innovation</h3>
                <p>Nous recherchons constamment de nouveaux produits et améliorations pour faciliter votre vie.</p>
            </div>
        </div>
    </section>

    <section style="background: white; padding: 40px; border-radius: var(--radius); box-shadow: var(--shadow-sm); text-align: center; margin-bottom: 60px;">
        <h2 style="margin-bottom: 30px;">Nous contacter</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
            <div>
                <i class="bi bi-envelope" style="font-size: 2rem; color: var(--primary); margin-bottom: 10px; display: block;"></i>
                <strong>Email</strong><br>
                <a href="mailto:info@ecommerce.com" style="color: var(--primary);">info@ecommerce.com</a>
            </div>
            <div>
                <i class="bi bi-telephone" style="font-size: 2rem; color: var(--primary); margin-bottom: 10px; display: block;"></i>
                <strong>Téléphone</strong><br>
                +33 (0) 1 23 45 67 89
            </div>
            <div>
                <i class="bi bi-geo-alt" style="font-size: 2rem; color: var(--primary); margin-bottom: 10px; display: block;"></i>
                <strong>Adresse</strong><br>
                123 Rue de la Boutique<br>75000 Paris, France
            </div>
        </div>
    </section>

</div>

<?php 
// script JS pour le panier
?>
<script>
    function updateCartCount() {
        // On vérifie si le fichier existe pour éviter une erreur 404 dans la console
        fetch('cart_count.php')
            .then(response => {
                if(response.ok) return response.json();
                throw new Error('Pas de réponse');
            })
            .then(data => {
                const element = document.getElementById('cart-count');
                if (element) element.textContent = data.count;
            })
            .catch(e => console.log('Info panier non chargée'));
    }
    
    document.addEventListener('DOMContentLoaded', updateCartCount);
</script>

<?php require_once '../includes/footer.php'; ?>