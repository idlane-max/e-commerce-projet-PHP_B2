<?php
/**
 * Page d'import CSV des produits
 * Permet d'importer un fichier CSV contenant des produits
 */

require_once '../config/config.php';
require_once '../src/User.php';
require_once '../src/Product.php';

// Vérifier si l'utilisateur est connecté et admin
if (!User::isLoggedIn() || !User::isAdmin()) {
    header('Location: login.php');
    exit;
}

$database = connectDB();
$product = new Product($database);
$message = '';
$success = false;
$importStats = [];

// Traiter l'upload du fichier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Validation du fichier
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = 'Erreur lors du téléchargement du fichier.';
    } elseif ($file['type'] !== 'text/csv' && $file['type'] !== 'application/vnd.ms-excel') {
        $message = 'Le fichier doit être au format CSV.';
    } else {
        // Lire le fichier CSV
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            $message = 'Impossible de lire le fichier CSV.';
        } else {
            $lineNumber = 0;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $lineNumber++;
                
                // Sauter la ligne d'en-tête
                if ($lineNumber === 1) {
                    continue;
                }
                
                // Valider le nombre de colonnes
                if (count($row) < 6) {
                    $errorCount++;
                    $errors[] = "Ligne $lineNumber: Format invalide (colonnes manquantes)";
                    continue;
                }
                
                // Extraire les données
                $id = trim($row[0]);
                $nom = trim($row[1]);
                $description = trim($row[2]);
                $prix = trim($row[3]);
                $image = trim($row[4]);
                $stock = trim($row[5]);
                
                // Valider les données
                if (empty($nom) || empty($description)) {
                    $errorCount++;
                    $errors[] = "Ligne $lineNumber: Nom ou description vide";
                    continue;
                }
                
                if (!is_numeric($prix) || $prix < 0) {
                    $errorCount++;
                    $errors[] = "Ligne $lineNumber: Prix invalide";
                    continue;
                }
                
                if (!is_numeric($stock) || $stock < 0) {
                    $errorCount++;
                    $errors[] = "Ligne $lineNumber: Stock invalide";
                    continue;
                }
                
                // Vérifier si le produit existe déjà
                $existing = $product->getProductById($id);
                if ($existing) {
                    // Mettre à jour le produit
                    $result = $product->updateProduct($id, $nom, $description, floatval($prix), $image, intval($stock));
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Ligne $lineNumber: Erreur lors de la mise à jour";
                    }
                } else {
                    // Ajouter un nouveau produit
                    $result = $product->addProduct($nom, $description, floatval($prix), $image, intval($stock));
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Ligne $lineNumber: Erreur lors de l'ajout";
                    }
                }
            }
            
            fclose($handle);
            
            // Préparer le message de résultat
            $success = $errorCount === 0;
            $importStats = [
                'total' => $lineNumber - 1,
                'success' => $successCount,
                'errors' => $errorCount
            ];
            
            if ($success) {
                $message = "✅ Import réussi! $successCount produit(s) importé(s) avec succès.";
            } else {
                $message = "⚠️ Import partiellement terminé: $successCount importé(s), $errorCount erreur(s)";
                if (count($errors) > 0 && count($errors) <= 10) {
                    $message .= "\n\nPremières erreurs:\n" . implode("\n", array_slice($errors, 0, 10));
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import CSV - Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .import-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .import-card h1 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input[type="file"] {
            display: block;
            padding: 10px;
            border: 2px dashed #007bff;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
        }
        
        .form-group input[type="file"]:hover {
            border-color: #0056b3;
            background: #f0f8ff;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box h3 {
            margin-top: 0;
            color: #007bff;
        }
        
        .info-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .info-box li {
            margin: 5px 0;
            color: #333;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        
        .stat-box.success {
            border-left-color: #28a745;
        }
        
        .stat-box.error {
            border-left-color: #dc3545;
        }
        
        .stat-box .number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-box.success .number {
            color: #28a745;
        }
        
        .stat-box.error .number {
            color: #dc3545;
        }
        
        .stat-box .label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .template-link {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .template-link:hover {
            background: #218838;
        }
        
        .error-list {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 12px;
            font-family: monospace;
        }
        
        .error-list p {
            margin: 5px 0;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="logo">E-Commerce Admin</div>
            <div class="nav-links">
                <a href="dashboard.php">Tableau de bord</a>
                <a href="products.php">Produits</a>
                <a href="users.php">Utilisateurs</a>
                <a href="import.php" class="active">Import CSV</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="import-card">
            <h1>📥 Import de Produits (CSV)</h1>
            
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $success ? 'alert-success' : ($errorCount > 0 ? 'alert-warning' : 'alert-error'); ?>">
                    <?php echo nl2br(htmlspecialchars($message)); ?>
                </div>
                
                <?php if (!empty($importStats)): ?>
                    <div class="stats">
                        <div class="stat-box">
                            <div class="number"><?php echo $importStats['total']; ?></div>
                            <div class="label">Total de lignes</div>
                        </div>
                        <div class="stat-box success">
                            <div class="number"><?php echo $importStats['success']; ?></div>
                            <div class="label">Importés avec succès</div>
                        </div>
                        <div class="stat-box error">
                            <div class="number"><?php echo $importStats['errors']; ?></div>
                            <div class="label">Erreurs</div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>📋 Format du fichier CSV requis:</h3>
                <ul>
                    <li><strong>Séparateur:</strong> Virgule (,)</li>
                    <li><strong>Encodage:</strong> UTF-8</li>
                    <li><strong>En-têtes:</strong> id, nom, description, prix, image, quantite_en_stock</li>
                    <li><strong>Colonnes requises:</strong> 6</li>
                </ul>
                <p><strong>Exemple de ligne:</strong></p>
                <code>1,Produit Test,Description complète,99.99,product.jpg,50</code>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="csv_file">Sélectionner un fichier CSV:</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    <a href="../database/products.csv" class="template-link" download>📥 Télécharger le fichier d'exemple (500 produits)</a>
                </div>
                
                <div class="buttons">
                    <button type="submit" class="btn btn-primary">Importer le CSV</button>
                    <a href="dashboard.php" class="btn btn-secondary">Retour</a>
                </div>
            </form>
            
            <div class="info-box" style="margin-top: 30px; background: #f0f0f0; border-left-color: #666;">
                <h3>ℹ️ Informations supplémentaires:</h3>
                <ul>
                    <li>Les produits existants seront <strong>mis à jour</strong> (base sur l'ID)</li>
                    <li>Les nouveaux produits seront <strong>créés</strong></li>
                    <li>Vérification automatique des <strong>doublons</strong></li>
                    <li>Validation des <strong>données obligatoires</strong></li>
                    <li>Maximum <strong>1000 lignes</strong> par import</li>
                </ul>
            </div>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; color: #666; margin-top: 40px;">
        <p>&copy; 2026 E-Commerce Project - Admin Panel</p>
    </footer>
</body>
</html>
