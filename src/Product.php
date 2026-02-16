<?php
/**
 * Classe Product - Gestion des produits
 */
class Product {
    private $pdo; // On utilise PDO
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Récupérer tous les produits (avec leur stock)
     */
    public function getAllProducts() {
        // Jointure pour avoir la quantité en stock avec le produit
        // Attention : On a nommé la colonne 'quantite' dans la table 'stock' à l'étape 1
        $sql = "SELECT i.*, s.quantite_en_stock as stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                ORDER BY i.date_publication DESC";
                
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un produit par ID
     */
    public function getProductById($id) {
        $sql = "SELECT i.*, s.quantite_en_stock as stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                WHERE i.id = :id";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $product = $stmt->fetch();
        return $product ?: null; // Retourne null si pas trouvé
    }
    
    /**
     * Ajouter un nouveau produit (ADMIN)
     */
    public function addProduct($nom, $description, $prix, $image, $stock) {
        // Validation basique
        if (empty($nom) || empty($description) || empty($prix)) {
            return ['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis'];
        }
        
        try {
            // On commence une transaction (pour que tout s'ajoute ou rien du tout)
            $this->pdo->beginTransaction();

            // 1. Insertion dans ITEMS
            $sqlItem = "INSERT INTO items (nom, description, prix, image) VALUES (:nom, :desc, :prix, :img)";
            $stmt = $this->pdo->prepare($sqlItem);
            $stmt->execute([
                'nom' => $nom,
                'desc' => $description,
                'prix' => $prix,
                'img' => $image ?: 'default.jpg'
            ]);
            
            // On récupère l'ID créé
            $productId = $this->pdo->lastInsertId();
            
            // 2. Insertion dans STOCK
            $sqlStock = "INSERT INTO stock (id_item, quantite_en_stock) VALUES (:id, :qty)";
            $stmtStock = $this->pdo->prepare($sqlStock);
            $stmtStock->execute([
                'id' => $productId,
                'qty' => $stock
            ]);
            
            // Si tout est bon, on valide
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Produit ajouté avec succès', 'id' => $productId];

        } catch (Exception $e) {
            // En cas d'erreur, on annule tout
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }
    
    /**
     * Modifier un produit
     */
    public function updateProduct($id, $nom, $description, $prix, $image, $stock) {
        try {
            $this->pdo->beginTransaction();

            // 1. Mise à jour ITEM
            $sql = "UPDATE items SET nom = :nom, description = :desc, prix = :prix, image = :img WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'nom' => $nom,
                'desc' => $description,
                'prix' => $prix,
                'img' => $image,
                'id' => $id
            ]);

            // 2. Mise à jour STOCK
            $sqlStock = "UPDATE stock SET quantite_en_stock = :qty WHERE id_item = :id";
            $stmtStock = $this->pdo->prepare($sqlStock);
            $stmtStock->execute([
                'qty' => $stock,
                'id' => $id
            ]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Produit modifié avec succès'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur de mise à jour'];
        }
    }
    
    /**
     * Supprimer un produit
     */
    public function deleteProduct($id) {
        // Grâce au "ON DELETE CASCADE" dans la BDD, supprimer l'item supprimera aussi le stock
        $stmt = $this->pdo->prepare("DELETE FROM items WHERE id = :id");
        if ($stmt->execute(['id' => $id])) {
            return ['success' => true, 'message' => 'Produit supprimé'];
        }
        return ['success' => false, 'message' => 'Erreur de suppression'];
    }
}
?>