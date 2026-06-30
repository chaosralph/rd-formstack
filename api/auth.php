<?php
/**
 * Authentifizierung API
 * 
 * POST auth.php                       → Login
 * POST auth.php?action=logout         → Logout
 * POST auth.php?action=change-password → Passwort ändern
 * POST auth.php?action=update-profile  → Profil aktualisieren
 * GET  auth.php?action=me             → Aktueller Benutzer
 */

// Output Buffering - fängt stray Output (Warnings, BOM etc.) ab
ob_start();

// CORS & Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Authorization');

/**
 * Saubere JSON-Antwort senden
 * Leert den Output-Buffer und gibt reines JSON aus
 */
function jsonResponse($data, $httpCode = 200) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    jsonResponse(null, 200);
}

require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/JWT.php';

$auth = new Auth();
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

switch ($method) {
    case 'POST':
        if ($action === '' || $action === 'login') {
            // === LOGIN ===
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            
            if (empty($data) && !empty($_POST)) {
                $data = $_POST;
            }
            
            if (empty($data) || (!isset($data['email']) && !isset($data['username']))) {
                jsonResponse(['error' => 'E-Mail und Passwort erforderlich'], 400);
            }
            
            $email = $data['email'] ?? $data['username'] ?? '';
            $password = $data['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                jsonResponse(['error' => 'E-Mail und Passwort erforderlich'], 400);
            }
            
            $user = $auth->authenticate($email, $password);
            
            if (!$user) {
                jsonResponse(['error' => 'Falsche E-Mail oder Passwort'], 401);
            }
            
            // JWT Token erstellen
            $token = JWT::encode([
                'sub' => $user['email'],
                'user_id' => $user['id'],
                'role' => $user['role']
            ]);
            
            // Session starten
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['auth_token'] = $token;
            
            jsonResponse([
                'success' => true,
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'] ?? '',
                    'role' => $user['role']
                ]
            ]);
            
        } elseif ($action === 'logout') {
            // === LOGOUT ===
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            jsonResponse(['success' => true, 'message' => 'Erfolgreich abgemeldet']);
            
        } elseif ($action === 'change-password') {
            // === PASSWORT ÄNDERN ===
            $user = $auth->getCurrentUser();
            if (!$user) {
                jsonResponse(['error' => 'Nicht authentifiziert'], 401);
            }
            
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            if (empty($data)) $data = $_POST;
            
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword)) {
                jsonResponse(['error' => 'Aktuelles und neues Passwort erforderlich'], 400);
            }
            
            if ($newPassword !== $confirmPassword) {
                jsonResponse(['error' => 'Neue Passwörter stimmen nicht überein'], 400);
            }
            
            if (strlen($newPassword) < 8) {
                jsonResponse(['error' => 'Neues Passwort muss mindestens 8 Zeichen lang sein'], 400);
            }
            
            // Aktuelles Passwort prüfen
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData || !password_verify($currentPassword, $userData['password_hash'])) {
                jsonResponse(['error' => 'Aktuelles Passwort ist falsch'], 403);
            }
            
            // Neues Passwort setzen
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newHash, $user['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Passwort erfolgreich geändert']);
            
        } elseif ($action === 'update-profile') {
            // === PROFIL AKTUALISIEREN ===
            $user = $auth->getCurrentUser();
            if (!$user) {
                jsonResponse(['error' => 'Nicht authentifiziert'], 401);
            }
            
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            if (empty($data)) $data = $_POST;
            
            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');
            
            if (empty($name) || empty($email)) {
                jsonResponse(['error' => 'Name und E-Mail erforderlich'], 400);
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(['error' => 'Ungültige E-Mail-Adresse'], 400);
            }
            
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance()->getConnection();
            
            // Prüfen ob E-Mail schon vergeben (von anderem User)
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                jsonResponse(['error' => 'Diese E-Mail-Adresse ist bereits vergeben'], 409);
            }
            
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user['id']]);
            
            // Session aktualisieren
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['user_email'] = $email;
            
            jsonResponse(['success' => true, 'message' => 'Profil erfolgreich aktualisiert']);
            
        } else {
            jsonResponse(['error' => 'Endpoint nicht gefunden: ' . $action], 404);
        }
        break;
        
    case 'GET':
        if ($action === 'me' || $action === '') {
            // === AKTUELLER BENUTZER ===
            $user = $auth->getCurrentUser();
            
            if (!$user) {
                jsonResponse(['error' => 'Nicht authentifiziert'], 401);
            }
            
            jsonResponse([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'] ?? '',
                    'role' => $user['role'],
                    'created_at' => $user['created_at'] ?? null
                ]
            ]);
        } else {
            jsonResponse(['error' => 'Endpoint nicht gefunden'], 404);
        }
        break;
        
    default:
        jsonResponse(['error' => 'Methode nicht erlaubt'], 405);
        break;
}
