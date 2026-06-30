<?php
/**
 * Authentifizierung
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/JWT.php';

class Auth {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/config.php';
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Benutzer authentifizieren
     */
    public function authenticate($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return null;
        }
        
        if (isset($user['is_active']) && !$user['is_active']) {
            return null;
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }
        
        return $user;
    }
    
    /**
     * Aktuellen Benutzer aus Token oder Session holen
     */
    public function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!empty($_SESSION['user_id'])) {
            $stmt = $this->db->prepare("SELECT id, email, name, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                return $user;
            }
        }
        
        $token = JWT::getTokenFromHeader();
        
        if (!$token) {
            return null;
        }
        
        $payload = JWT::decode($token);
        
        if (!$payload || !isset($payload['sub'])) {
            return null;
        }
        
        $stmt = $this->db->prepare("SELECT id, email, name, role, created_at FROM users WHERE email = ?");
        $stmt->execute([$payload['sub']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['auth_token'] = $token;
        }
        
        return $user;
    }
    
    /**
     * Prüft ob Benutzer eingeloggt ist
     */
    public function requireAuth() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            // Output Buffer leeren für saubere JSON-Antwort
            while (ob_get_level()) {
                ob_end_clean();
            }
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        return $user;
    }
}
