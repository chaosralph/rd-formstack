<?php
/**
 * Benutzer-Verwaltung API (nur Admin)
 * 
 * GET    users.php              → Alle Benutzer auflisten
 * POST   users.php              → Neuen Benutzer anlegen
 * PUT    users.php?id=X         → Benutzer bearbeiten
 * DELETE users.php?id=X         → Benutzer löschen
 * POST   users.php?action=reset-password&id=X → Passwort zurücksetzen
 */

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Authorization');

function jsonResponse($data, $httpCode = 200) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    jsonResponse(null, 200);
}

require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$user = $auth->requireAuth();

// Nur Admins dürfen User verwalten
if ($user['role'] !== 'admin') {
    jsonResponse(['error' => 'Keine Berechtigung'], 403);
}

$db = Database::getInstance()->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$userId = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        // === ALLE BENUTZER AUFLISTEN ===
        $stmt = $db->query("SELECT id, email, name, role, is_active, created_at, updated_at FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Boolean casten
        foreach ($users as &$u) {
            $u['is_active'] = (bool) $u['is_active'];
        }
        
        jsonResponse(['users' => $users]);
        break;
        
    case 'POST':
        if ($action === 'reset-password') {
            // === PASSWORT ZURÜCKSETZEN ===
            if (!$userId) {
                jsonResponse(['error' => 'Benutzer-ID erforderlich'], 400);
            }
            
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            if (empty($data)) $data = $_POST;
            
            $newPassword = $data['new_password'] ?? '';
            
            if (empty($newPassword) || strlen($newPassword) < 8) {
                jsonResponse(['error' => 'Neues Passwort muss mindestens 8 Zeichen lang sein'], 400);
            }
            
            // Prüfen ob User existiert
            $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            if (!$stmt->fetch()) {
                jsonResponse(['error' => 'Benutzer nicht gefunden'], 404);
            }
            
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);
            
            jsonResponse(['success' => true, 'message' => 'Passwort wurde zurückgesetzt']);
            
        } else {
            // === NEUEN BENUTZER ANLEGEN ===
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            if (empty($data)) $data = $_POST;
            
            $email = trim($data['email'] ?? '');
            $name = trim($data['name'] ?? '');
            $password = $data['password'] ?? '';
            $role = $data['role'] ?? 'user';
            
            if (empty($email) || empty($name) || empty($password)) {
                jsonResponse(['error' => 'E-Mail, Name und Passwort sind erforderlich'], 400);
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(['error' => 'Ungültige E-Mail-Adresse'], 400);
            }
            
            if (strlen($password) < 8) {
                jsonResponse(['error' => 'Passwort muss mindestens 8 Zeichen lang sein'], 400);
            }
            
            if (!in_array($role, ['admin', 'user'])) {
                $role = 'user';
            }
            
            // Prüfen ob E-Mail schon vergeben
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                jsonResponse(['error' => 'Diese E-Mail-Adresse ist bereits vergeben'], 409);
            }
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $hash, $name, $role]);
            
            $newId = $db->lastInsertId();
            
            jsonResponse([
                'success' => true,
                'message' => 'Benutzer erfolgreich angelegt',
                'user' => [
                    'id' => $newId,
                    'email' => $email,
                    'name' => $name,
                    'role' => $role
                ]
            ]);
        }
        break;
        
    case 'PUT':
        // === BENUTZER BEARBEITEN ===
        if (!$userId) {
            jsonResponse(['error' => 'Benutzer-ID erforderlich'], 400);
        }
        
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        // Prüfen ob User existiert
        $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            jsonResponse(['error' => 'Benutzer nicht gefunden'], 404);
        }
        
        $updates = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updates[] = 'name = ?';
            $params[] = trim($data['name']);
        }
        
        if (isset($data['email'])) {
            $email = trim($data['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(['error' => 'Ungültige E-Mail-Adresse'], 400);
            }
            // Prüfen ob E-Mail schon von anderem User genutzt
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                jsonResponse(['error' => 'Diese E-Mail-Adresse ist bereits vergeben'], 409);
            }
            $updates[] = 'email = ?';
            $params[] = $email;
        }
        
        if (isset($data['role'])) {
            if (in_array($data['role'], ['admin', 'user'])) {
                // Verhindern dass sich Admin selbst herabstuft
                if ($userId == $user['id'] && $data['role'] !== 'admin') {
                    jsonResponse(['error' => 'Sie können sich nicht selbst die Admin-Rechte entziehen'], 400);
                }
                $updates[] = 'role = ?';
                $params[] = $data['role'];
            }
        }
        
        if (isset($data['is_active'])) {
            // Verhindern dass sich Admin selbst deaktiviert
            if ($userId == $user['id'] && !$data['is_active']) {
                jsonResponse(['error' => 'Sie können sich nicht selbst deaktivieren'], 400);
            }
            $updates[] = 'is_active = ?';
            $params[] = $data['is_active'] ? 1 : 0;
        }
        
        if (empty($updates)) {
            jsonResponse(['error' => 'Keine Änderungen angegeben'], 400);
        }
        
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => 'Benutzer erfolgreich aktualisiert']);
        break;
        
    case 'DELETE':
        // === BENUTZER LÖSCHEN ===
        if (!$userId) {
            jsonResponse(['error' => 'Benutzer-ID erforderlich'], 400);
        }
        
        // Verhindern dass sich Admin selbst löscht
        if ($userId == $user['id']) {
            jsonResponse(['error' => 'Sie können sich nicht selbst löschen'], 400);
        }
        
        // Prüfen ob User existiert
        $stmt = $db->prepare("SELECT id, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$targetUser) {
            jsonResponse(['error' => 'Benutzer nicht gefunden'], 404);
        }
        
        // User löschen (CASCADE löscht auch Belege)
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        jsonResponse(['success' => true, 'message' => 'Benutzer "' . $targetUser['email'] . '" wurde gelöscht']);
        break;
        
    default:
        jsonResponse(['error' => 'Methode nicht erlaubt'], 405);
        break;
}
