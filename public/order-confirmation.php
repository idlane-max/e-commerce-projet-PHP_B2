<?php
// 1. Inclusions & Config
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

// 2. Vérification Connexion
if (!isset($_SESSION['user'])) {
    echo "<script>window.location.href='/src/login.php';</script>";
    exit();
}

$pdo = connectDB();
$userId = $_SESSION['user']['id'];
$invoiceId = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;

if ($invoiceId <= 0) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

// 3. Récupérer la facture (Invoice)
// On vérifie que la facture appartient bien à l'utilisateur connecté (Sécurité)
$sqlInvoice = "SELECT * FROM invoice WHERE id = :id AND id_user = :user_id";
$stmt = $pdo->prepare($sqlInvoice);
$stmt->execute(['id' => $invoiceId, 'user_id' => $userId]);
$invoice = $stmt->fetch();

if (!$invoice) {
    echo "<div class='container alert alert-danger'>Facture introuvable.</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

// 4. Récupérer les articles de la commande (Orders + Items)
// Note : Ta requête SQL initiale utilisait DATE_SUB(NOW(), INTERVAL 1 MINUTE).
// C'est risqué si le client met du temps à charger la page.
// Mieux vaut récupérer les commandes liées à cet utilisateur créées "récemment"
// ou idéalement lier invoice et orders (mais ta structure ne le fait pas explicitement).
// Je vais adapter ta requête pour prendre les dernières commandes.

$sqlItems = "SELECT o.quantite, o.prix_unitaire, i.nom 
             FROM orders o
             JOIN items i ON o.id_item = i.id
             WHERE o.id_user = :user_id 
             ORDER BY o.date_commande DESC 
             LIMIT 20"; 
             // LIMIT 20 est une sécurité. L'idéal serait d'avoir un 'invoice_id' dans la table 'orders'.

$stmt = $pdo->prepare($sqlItems);
$stmt->execute(['user_id' => $userId]);
$orderItems = $stmt->fetchAll();
?>

<div class="container" style="padding: 40px 0;">
    
    <div style="text-align: center; margin-bottom: 40px; background: #d1fae5; padding: 30px; border-radius: var(--radius); color: #065f46;">
        <i class="bi bi-check-circle-fill" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
        <h1 style="margin: 0;">Commande confirmée !</h1>
        <p style="margin-top: 10px; font-size: 1.2rem;">Merci pour votre achat, <strong><?php echo htmlspecialchars($_SESSION['user']['nom']); ?></strong>.</p>
    </div>

    <div style="background: white; padding: 30px; border-radius: var(--radius); box-shadow: var(--shadow-md); max-width: 800px; margin: 0 auto;">
        
        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #f4f4f4; padding-bottom: 20px; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0; font-size: 1.5rem;">Facture #<?php echo $invoice['id']; ?></h2>
                <p style="color: var(--text-light); margin-top: 5px;">Date : <?php echo date('d/m/Y H:i', strtotime($invoice['date_transaction'])); ?></p>
            </div>
            <div style="text-align: right;">
                <h3 style="font-size: 1.1rem; margin-bottom: 5px;">Livraison à :</h3>
                <p style="margin: 0; color: var(--text-dark);">
                    <?php echo htmlspecialchars($invoice['adresse_facturation']); ?><br>
                    <?php echo htmlspecialchars($invoice['code_postal']); ?> <?php echo htmlspecialchars($invoice['ville']); ?>
                </p>
            </div>
        </div>

        <h3 style="margin-bottom: 15px;">Articles commandés</h3>
        <table class="cart-table" style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix Unit.</th>
                    <th>Qté</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                    <?php $totalItem = $item['quantite'] * $item['prix_unitaire']; ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['nom']); ?></strong></td>
                        <td><?php echo number_format($item['prix_unitaire'], 2); ?> €</td>
                        <td><?php echo $item['quantite']; ?></td>
                        <td><?php echo number_format($totalItem, 2); ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="text-align: right; font-size: 1.5rem; font-weight: bold; border-top: 2px solid #f4f4f4; padding-top: 20px;">
            Total payé : <span style="color: var(--primary);"><?php echo number_format($invoice['montant'], 2); ?> €</span>
        </div>

    </div>

    <div style="text-align: center; margin-top: 40px; display: flex; gap: 20px; justify-content: center;">
        <a href="index.php" class="btn btn-primary btn-auto">Retour à l'accueil</a>
        <a href="catalogue.php" class="btn btn-primary btn-auto" style="background: var(--secondary);">Continuer vos achats</a>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>