<?php
/**
 * Classe Cart - Gestion du panier d'achat (Version PDO)
 */
class Cart {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Initialiser le panier en session si vide
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    /**
     * Ajouter un article au panier
     */
    public function addToCart($productId, $quantity = 1) {
        // Validation basique
        if ($productId <= 0 || $quantity <= 0) {
            return ['success' => false, 'message' => 'Produit ou quantité invalide'];
        }
        
        // 1. Vérifier si le produit existe et récupérer son prix et stock
        // On joint la table stock pour vérifier la disponibilité
        $sql = "SELECT i.id, i.prix, s.quantite_en_stock as stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                WHERE i.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            return ['success' => false, 'message' => 'Produit non trouvé'];
        }
        
        // Calcul de la quantité future
        $currentQty = isset($_SESSION['cart'][$productId]['quantity']) ? $_SESSION['cart'][$productId]['quantity'] : 0;
        $futureQty = $currentQty + $quantity;

        // Vérification du stock
        if ($futureQty > $product['stock']) {
            return ['success' => false, 'message' => 'Stock insuffisant (Max: ' . $product['stock'] . ')'];
        }
        
        // 2. Ajouter ou mettre à jour dans la session
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'quantity' => $quantity,
                'price' => $product['prix'] // On stocke le prix pour éviter de refaire une requête
            ];
        }
        
        return ['success' => true, 'message' => 'Produit ajouté au panier'];
    }
    
    /**
     * Obtenir le contenu du panier (avec les détails produits)
     */
    public function getCartItems() {
        if (empty($_SESSION['cart'])) {
            return [];
        }
        
        $items = [];
        
        // On récupère les IDs des produits dans le panier
        $ids = array_keys($_SESSION['cart']);
        
        if (empty($ids)) return [];

        // Astuce PDO pour faire un "WHERE id IN (...)"
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id, nom, prix, image FROM items WHERE id IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        $products = $stmt->fetchAll();

        // On fusionne les infos BDD avec les quantités en Session
        foreach ($products as $product) {
            $id = $product['id'];
            $qty = $_SESSION['cart'][$id]['quantity'];
            
            $items[] = [
                'id' => $id,
                'nom' => $product['nom'],
                'price' => $product['prix'],
                'image' => $product['image'],
                'quantity' => $qty,
                'total' => $product['prix'] * $qty
            ];
        }
        
        return $items;
    }
    
    /**
     * Supprimer un article du panier
     */
    public function removeFromCart($productId) {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            return ['success' => true, 'message' => 'Article supprimé'];
        }
        return ['success' => false, 'message' => 'Article non trouvé'];
    }
    
    /**
     * Mettre à jour la quantité
     */
    public function updateQuantity($productId, $quantity) {
        if (!isset($_SESSION['cart'][$productId])) {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }
        
        if ($quantity <= 0) {
            return $this->removeFromCart($productId); // Si 0, on supprime
        }

        // Vérification du stock avant update
        $stmt = $this->pdo->prepare("SELECT quantite_en_stock FROM stock WHERE id_item = :id");
        $stmt->execute(['id' => $productId]);
        $stock = $stmt->fetchColumn();

        if ($quantity > $stock) {
             return ['success' => false, 'message' => 'Stock insuffisant'];
        }
        
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        return ['success' => true, 'message' => 'Quantité mise à jour'];
    }
    
    /**
     * Obtenir le total du panier
     */
    public function getCartTotal() {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return round($total, 2);
    }
    
    /**
     * Obtenir le nombre d'articles (badge)
     */
    public function getCartCount() {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Vider le panier
     */
    public function clearCart() {
        $_SESSION['cart'] = [];
    }

    /**
     * Financer l'achat (Checkout)
     */
    public function checkout($userId, $adresse, $ville, $codePostal) {
        if (empty($_SESSION['cart'])) {
            return ['success' => false, 'message' => 'Panier vide'];
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Créer la commande (Order) globale (Optionnel selon ta structure, 
            // mais ta table orders semble lier user et item directement.
            // On va suivre ta logique : une ligne dans orders par item)
            
            // NOTE : Ta structure BDD "orders" lie directement item et user.
            // C'est un peu inhabituel (d'habitude on a Orders -> OrderDetails),
            // mais je respecte ta structure.
            
            // Création de la facture (Invoice)
            $montant = $this->getCartTotal();
            $sqlInvoice = "INSERT INTO invoice (id_user, montant, adresse_facturation, ville, code_postal) VALUES (?, ?, ?, ?, ?)";
            $stmtInv = $this->pdo->prepare($sqlInvoice);
            $stmtInv->execute([$userId, $montant, $adresse, $ville, $codePostal]);
            $invoiceId = $this->pdo->lastInsertId();

            // Pour chaque article
            foreach ($_SESSION['cart'] as $id => $item) {
                $qty = $item['quantity'];
                $price = $item['price'];

                // A. Insérer dans Orders
                // Rappel : Ta table orders a (id_user, id_item, quantite, prix_unitaire)
                $sqlOrder = "INSERT INTO orders (id_user, id_item, quantite, prix_unitaire) VALUES (?, ?, ?, ?)";
                $stmtOrd = $this->pdo->prepare($sqlOrder);
                $stmtOrd->execute([$userId, $id, $qty, $price]);

                // B. Décrémenter le Stock
                $sqlStock = "UPDATE stock SET quantite_en_stock = quantite_en_stock - ? WHERE id_item = ?";
                $stmtStock = $this->pdo->prepare($sqlStock);
                $stmtStock->execute([$qty, $id]);
            }

            $this->pdo->commit();
            $this->clearCart(); // On vide le panier après succès

            return ['success' => true, 'message' => 'Commande validée !'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }
}
?>