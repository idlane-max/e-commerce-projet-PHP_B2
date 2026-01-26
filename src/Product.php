<?php
/**
 * Classe Product - Gestion des produits
 */
class Product {
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database;
    }
    
    /**
     * Récupérer tous les produits
     */
    public function getAllProducts() {
        $stmt = $this->conn->prepare("
            SELECT i.id, i.nom, i.description, i.prix, i.image, s.quantite_en_stock
            FROM items i
            LEFT JOIN stock s ON i.id = s.id_item
            ORDER BY i.date_publication DESC
        ");
        if (!$stmt) {
            die('Erreur prepare: ' . $this->conn->error);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Récupérer un produit par ID
     */
    public function getProductById($productId) {
        $stmt = $this->conn->prepare("
            SELECT i.id, i.nom, i.description, i.prix, i.image, s.quantite_en_stock, i.date_publication
            FROM items i
            LEFT JOIN stock s ON i.id = s.id_item
            WHERE i.id = ?
        ");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Ajouter un nouveau produit
     */
    public function addProduct($nom, $description, $prix, $image, $stock) {
        // Validation des données
        if (empty($nom) || empty($description) || empty($prix) || empty($stock)) {
            return ['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis'];
        }
        
        if (!is_numeric($prix) || $prix <= 0) {
            return ['success' => false, 'message' => 'Le prix doit être un nombre positif'];
        }
        
        if (!is_numeric($stock) || $stock < 0) {
            return ['success' => false, 'message' => 'Le stock doit être un nombre positif'];
        }
        
        // Générer le nom du fichier image
        $imageName = $image ? $image : 'default.jpg';
        
        // Insérer le produit
        $stmt = $this->conn->prepare("INSERT INTO items (nom, description, prix, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $nom, $description, $prix, $imageName);
        
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du produit'];
        }
        
        $productId = $this->conn->insert_id;
        
        // Ajouter le stock
        $stmtStock = $this->conn->prepare("INSERT INTO stock (id_item, quantite_en_stock) VALUES (?, ?)");
        $stmtStock->bind_param("ii", $productId, $stock);
        
        if ($stmtStock->execute()) {
            return ['success' => true, 'message' => 'Produit ajouté avec succès', 'id' => $productId];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du stock'];
        }
    }
    
    /**
     * Modifier un produit
     */
    public function updateProduct($productId, $nom, $description, $prix, $image, $stock) {
        // Validation des données
        if (!is_numeric($productId) || $productId <= 0) {
            return ['success' => false, 'message' => 'ID produit invalide'];
        }
        
        if (empty($nom) || empty($description) || empty($prix)) {
            return ['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis'];
        }
        
        if (!is_numeric($prix) || $prix <= 0) {
            return ['success' => false, 'message' => 'Le prix doit être un nombre positif'];
        }
        
        // Obtenir l'image existante si aucune nouvelle n'est fournie
        if (empty($image)) {
            $product = $this->getProductById($productId);
            if (!$product) {
                return ['success' => false, 'message' => 'Produit non trouvé'];
            }
            $image = $product['image'];
        }
        
        // Mettre à jour le produit
        $stmt = $this->conn->prepare("UPDATE items SET nom = ?, description = ?, prix = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $nom, $description, $prix, $image, $productId);
        
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Erreur lors de la modification du produit'];
        }
        
        // Mettre à jour le stock si fourni
        if (!empty($stock) && is_numeric($stock) && $stock >= 0) {
            $stmtStock = $this->conn->prepare("UPDATE stock SET quantite_en_stock = ? WHERE id_item = ?");
            $stmtStock->bind_param("ii", $stock, $productId);
            $stmtStock->execute();
        }
        
        return ['success' => true, 'message' => 'Produit modifié avec succès'];
    }
    
    /**
     * Supprimer un produit
     */
    public function deleteProduct($productId) {
        if (!is_numeric($productId) || $productId <= 0) {
            return ['success' => false, 'message' => 'ID produit invalide'];
        }
        
        $stmt = $this->conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bind_param("i", $productId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Produit supprimé avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du produit'];
        }
    }
    
    /**
     * Vérifier si un produit a suffisamment de stock
     */
    public function hasEnoughStock($productId, $quantity) {
        $product = $this->getProductById($productId);
        
        if (!$product) {
            return false;
        }
        
        return $product['quantite_en_stock'] >= $quantity;
    }
}
?>
