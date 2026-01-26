<?php
/**
 * Classe Cart - Gestion du panier d'achat
 */
class Cart {
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database;
    }
    
    /**
     * Ajouter un article au panier
     */
    public function addToCart($productId, $quantity = 1) {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Vous devez être connecté'];
        }
        
        if (!is_numeric($productId) || $productId <= 0) {
            return ['success' => false, 'message' => 'Produit invalide'];
        }
        
        if (!is_numeric($quantity) || $quantity <= 0) {
            return ['success' => false, 'message' => 'Quantité invalide'];
        }
        
        // Vérifier si le produit existe
        $stmt = $this->conn->prepare("SELECT id, prix FROM items WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Produit non trouvé'];
        }
        
        $product = $result->fetch_assoc();
        
        // Initialiser le panier en session si vide
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Ajouter ou mettre à jour la quantité
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'quantity' => $quantity,
                'price' => $product['prix']
            ];
        }
        
        return ['success' => true, 'message' => 'Produit ajouté au panier'];
    }
    
    /**
     * Obtenir le contenu du panier
     */
    public function getCartItems() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return [];
        }
        
        $items = [];
        
        foreach ($_SESSION['cart'] as $productId => $cartItem) {
            // Récupérer les détails du produit
            $stmt = $this->conn->prepare("SELECT id, nom, prix, image FROM items WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
                $items[] = [
                    'id' => $productId,
                    'nom' => $product['nom'],
                    'price' => $product['prix'],
                    'image' => $product['image'],
                    'quantity' => $cartItem['quantity'],
                    'total' => $product['prix'] * $cartItem['quantity']
                ];
            }
        }
        
        return $items;
    }
    
    /**
     * Supprimer un article du panier
     */
    public function removeFromCart($productId) {
        if (!isset($_SESSION['cart'][$productId])) {
            return ['success' => false, 'message' => 'Article non trouvé dans le panier'];
        }
        
        unset($_SESSION['cart'][$productId]);
        
        return ['success' => true, 'message' => 'Article supprimé du panier'];
    }
    
    /**
     * Mettre à jour la quantité d'un article
     */
    public function updateQuantity($productId, $quantity) {
        if (!isset($_SESSION['cart'][$productId])) {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }
        
        if (!is_numeric($quantity) || $quantity <= 0) {
            return ['success' => false, 'message' => 'Quantité invalide'];
        }
        
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        
        return ['success' => true, 'message' => 'Quantité mise à jour'];
    }
    
    /**
     * Vider le panier
     */
    public function clearCart() {
        $_SESSION['cart'] = [];
        return ['success' => true, 'message' => 'Panier vidé'];
    }
    
    /**
     * Obtenir le total du panier
     */
    public function getCartTotal() {
        $total = 0;
        
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $cartItem) {
                $total += $cartItem['price'] * $cartItem['quantity'];
            }
        }
        
        return round($total, 2);
    }
    
    /**
     * Obtenir le nombre d'articles dans le panier
     */
    public function getCartCount() {
        $count = 0;
        
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $cartItem) {
                $count += $cartItem['quantity'];
            }
        }
        
        return $count;
    }
    
    /**
     * Financer l'achat (créer une facture)
     */
    public function checkout($adresse, $ville, $codePostal) {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Vous devez être connecté'];
        }
        
        if (empty($_SESSION['cart'])) {
            return ['success' => false, 'message' => 'Le panier est vide'];
        }
        
        if (empty($adresse) || empty($ville) || empty($codePostal)) {
            return ['success' => false, 'message' => 'Tous les champs sont obligatoires'];
        }
        
        $userId = $_SESSION['user_id'];
        $montant = $this->getCartTotal();
        
        // Créer la facture
        $stmt = $this->conn->prepare("
            INSERT INTO invoice (id_user, montant, adresse_facturation, ville, code_postal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("idsss", $userId, $montant, $adresse, $ville, $codePostal);
        
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Erreur lors de la création de la facture'];
        }
        
        $invoiceId = $this->conn->insert_id;
        
        // Créer les commandes pour chaque article
        foreach ($_SESSION['cart'] as $productId => $cartItem) {
            $stmt = $this->conn->prepare("
                INSERT INTO orders (id_user, id_item, quantité, prix_unitaire) 
                VALUES (?, ?, ?, ?)
            ");
            $quantity = $cartItem['quantity'];
            $price = $cartItem['price'];
            $stmt->bind_param("iiii", $userId, $productId, $quantity, $price);
            $stmt->execute();
            
            // Réduire le stock
            $stmt = $this->conn->prepare("UPDATE stock SET quantite_en_stock = quantite_en_stock - ? WHERE id_item = ?");
            $stmt->bind_param("ii", $quantity, $productId);
            $stmt->execute();
        }
        
        // Vider le panier
        $_SESSION['cart'] = [];
        
        return [
            'success' => true, 
            'message' => 'Commande finalisée avec succès',
            'invoice_id' => $invoiceId,
            'montant' => $montant
        ];
    }
}
?>
