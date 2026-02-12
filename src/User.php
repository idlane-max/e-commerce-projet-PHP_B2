<?php
/**
 * Classe User - Gestion des utilisateurs (Version PDO adaptée à tes colonnes)
 */
class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register($nom, $email, $password, $confirmPassword) {
        // Validation basique
        if (empty($nom) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Tous les champs sont obligatoires'];
        }
        
        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Les mots de passe ne correspondent pas'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'L\'adresse email n\'est pas valide'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'];
        }
        
        // 1. Vérifier si l'email existe déjà
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }
        
        // 2. Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // 3. Insérer l'utilisateur
        // CORRECTION ICI : utilisation de 'mot_de_passe' au lieu de 'password'
        // NOTE : J'utilise 'role' (sans accent). Si ta colonne est 'rôle', modifie la ligne ci-dessous.
        $sql = "INSERT INTO users (nom, email, mot_de_passe, role) VALUES (:nom, :email, :mdp, 'client')";
        
        $stmt = $this->pdo->prepare($sql);
        
        // On lie les paramètres
        $params = [
            'nom' => $nom, 
            'email' => $email, 
            'mdp' => $hashedPassword // On envoie le hash dans la colonne mot_de_passe
        ];

        if ($stmt->execute($params)) {
            return ['success' => true, 'message' => 'Inscription réussie ! Vous allez être redirigé.'];
        } else {
            return ['success' => false, 'message' => 'Erreur technique lors de l\'inscription'];
        }
    }
    
    /**
     * Connexion d'un utilisateur
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email et mot de passe obligatoires'];
        }
        
        // Récupération de l'utilisateur
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        }
        
        // Vérification du hash
        // CORRECTION ICI : on compare avec $user['mot_de_passe']
        if (!password_verify($password, $user['mot_de_passe'])) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        }
        
        // Création de la session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'email' => $user['email'],
            'role' => $user['role'] ?? $user['role'] ?? 'client' // Sécurité : tente les deux ou met par défaut
        ];
        
        return ['success' => true, 'message' => 'Connexion réussie', 'role' => $_SESSION['user']['role']];
    }

    public static function isAdmin() {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
        
    }

    // --- AJOUTS POUR L'ADMIN (PDO) ---

    /**
     * Récupérer tous les clients (Performance : on ne prend que l'utile)
     */
    public function getAllClients() {
        // On exclut les admins de la liste pour éviter les accidents
        $sql = "SELECT id, nom, email, date_inscription 
                FROM users 
                WHERE role != 'admin' 
                ORDER BY date_inscription DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser($id) {
        // On vérifie que l'ID est valide
        if ($id <= 0) return ['success' => false, 'message' => 'ID invalide'];

        try {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Utilisateur supprimé'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

}
?>