<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/header.php';

// Sécurité : connexion obligatoire
if (!isset($_SESSION['user'])) {
    echo "<script>window.location.href='../views/login.php';</script>";
    exit();
}

$pdo = connectDB();
$userId = $_SESSION['user']['id'];

// Récupérer les factures (Invoice) triées par date
// Note : La jointure n'est pas nécessaire si tu n'as pas besoin des noms d'items ici
// Mais on va récupérer les commandes groupées
$sql = "SELECT * FROM invoice WHERE id_user = :uid ORDER BY date_transaction DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['uid' => $userId]);
$factures = $stmt->fetchAll();
?>

<div class="container" style="padding: 40px 0;">
    <h1 style="margin-bottom: 30px;">Mes Commandes</h1>

    <?php if (empty($factures)): ?>
        <div class="alert alert-info">Vous n'avez pas encore passé de commande.</div>
    <?php else: ?>
        
        <div class="cart-items"> <table class="cart-table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Adresse</th>
                        <th>Montant Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factures as $fac): ?>
                        <tr>
                            <td><strong>#<?php echo $fac['id']; ?></strong></td>
                            <td><?php echo date('d/m/Y à H:i', strtotime($fac['date_transaction'])); ?></td>
                            <td>
                                <?php echo htmlspecialchars($fac['ville']); ?><br>
                                <small style="color:#888"><?php echo htmlspecialchars($fac['adresse_facturation']); ?></small>
                            </td>
                            <td style="font-weight:bold; color:var(--primary);">
                                <?php echo number_format($fac['montant'], 2); ?> €
                            </td>
                            <td>
                                <a href="order-confirmation.php?invoice_id=<?php echo $fac['id']; ?>" class="btn btn-primary btn-auto" style="padding: 5px 10px; font-size: 0.8rem;">
                                    Voir détail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>