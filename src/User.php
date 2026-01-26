<?php
/**
 * Classe User - Gestion des utilisateurs
 */
class User {
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database;
    }
    
    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register($nom, $email, $password, $confirmPassword) {
        // Validation des données
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
        
        // Vérifier si l'email existe déjà
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }
        
        // Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Insérer l'utilisateur
        $stmt = $this->conn->prepare("INSERT INTO users (nom, email, mot_de_passe, rôle) VALUES (?, ?, ?, 'client')");
        $stmt->bind_param("sss", $nom, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Inscription réussie! Vous pouvez maintenant vous connecter.'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        }
    }
    
    /**
     * Connexion d'un utilisateur
     */
    public function login($email, $password) {
        // Validation des données
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email et mot de passe obligatoires'];
        }
        
        // Récupérer l'utilisateur
        $stmt = $this->conn->prepare("SELECT id, nom, email, mot_de_passe, rôle FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        }
        
        $user = $result->fetch_assoc();
        
        // Vérifier le mot de passe
        if (!password_verify($password, $user['mot_de_passe'])) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        }
        
        // Créer la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['rôle'];
        
        return ['success' => true, 'message' => 'Connexion réussie', 'role' => $user['rôle']];
    }
    
    /**
     * Déconnexion de l'utilisateur
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Déconnexion réussie'];
    }
    
    /**
     * Récupérer tous les utilisateurs
     */
    public function getAllUsers() {
        $stmt = $this->conn->prepare("SELECT id, nom, email, rôle, date_inscription FROM users WHERE rôle = 'client' ORDER BY date_inscription DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function deleteUser($userId) {
        if (!is_numeric($userId)) {
            return ['success' => false, 'message' => 'ID utilisateur invalide'];
        }
        
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ? AND rôle = 'client'");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Utilisateur supprimé avec succès'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression'];
        }
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Vérifier si l'utilisateur est administrateur
     */
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}
?>
