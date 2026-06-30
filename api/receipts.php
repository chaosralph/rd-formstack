<?php
/**
 * Beleg-API
 */

// Output Buffering - fängt stray Output (Warnings, BOM etc.) ab
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Authorization');

/**
 * Saubere JSON-Antwort senden
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
require_once __DIR__ . '/../classes/ReceiptProcessor.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$user = $auth->requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();

// Datei-Anzeige
if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $receiptId = intval($_GET['id']);
    $stmt = $db->prepare("SELECT file_path, file_type FROM receipts WHERE id = ? AND user_id = ?");
    $stmt->execute([$receiptId, $user['id']]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($receipt && file_exists($receipt['file_path'])) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: ' . $receipt['file_type']);
        header('Content-Disposition: inline; filename="' . basename($receipt['file_path']) . '"');
        readfile($receipt['file_path']);
        exit;
    } else {
        jsonResponse(['error' => 'Datei nicht gefunden'], 404);
    }
}

switch ($method) {
    case 'POST':
        // Beleg hochladen
        if (!isset($_FILES['file'])) {
            jsonResponse(['error' => 'Keine Datei hochgeladen'], 400);
        }
        
        // Manuelle Kategorie vom Frontend (optional)
        $manualCategory = $_POST['category'] ?? null;
        
        $processor = new ReceiptProcessor();
        $result = $processor->processReceipt($_FILES['file'], $user['id'], $manualCategory);
        
        if ($result['success']) {
            // Vollständige Beleg-Informationen abrufen
            $stmt = $db->prepare("SELECT * FROM receipts WHERE id = ?");
            $stmt->execute([$result['receipt_id']]);
            $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Buchungsvorschläge separat laden
            $stmtB = $db->prepare("SELECT * FROM booking_suggestions WHERE receipt_id = ?");
            $stmtB->execute([$result['receipt_id']]);
            $receipt['booking_suggestions'] = $stmtB->fetchAll(PDO::FETCH_ASSOC);
            
            jsonResponse([
                'success' => true,
                'receipt' => $receipt,
                'category' => $result['category'],
                'ocr_result' => $result['ocr_result'],
                'ocr_method' => $result['ocr_method'] ?? 'unknown'
            ]);
        } else {
            jsonResponse($result, 400);
        }
        break;
        
    case 'GET':
        // Belege auflisten
        $category = $_GET['category'] ?? null;
        $status = $_GET['status'] ?? null;
        $limit = intval($_GET['limit'] ?? 50);
        $offset = intval($_GET['offset'] ?? 0);
        
        $where = ['user_id = ?'];
        $params = [$user['id']];
        
        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }
        
        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        
        $sql = "SELECT * FROM receipts WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buchungsvorschläge für jeden Beleg hinzufügen
        foreach ($receipts as &$receipt) {
            $stmtB = $db->prepare("SELECT * FROM booking_suggestions WHERE receipt_id = ?");
            $stmtB->execute([$receipt['id']]);
            $receipt['booking_suggestions'] = $stmtB->fetchAll(PDO::FETCH_ASSOC);
        }
        
        jsonResponse(['receipts' => $receipts]);
        break;
        
    case 'PUT':
        // Beleg aktualisieren
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        $receiptId = $_GET['id'] ?? $data['id'] ?? null;
        if (!$receiptId) {
            jsonResponse(['error' => 'Beleg-ID erforderlich'], 400);
        }
        
        // Prüfe ob Beleg dem Benutzer gehört
        $stmt = $db->prepare("SELECT id FROM receipts WHERE id = ? AND user_id = ?");
        $stmt->execute([$receiptId, $user['id']]);
        if (!$stmt->fetch()) {
            jsonResponse(['error' => 'Beleg nicht gefunden'], 404);
        }
        
        $updates = [];
        $params = [];
        
        if (isset($data['category'])) {
            $updates[] = 'category = ?';
            $params[] = $data['category'];
        }
        
        if (isset($data['amount'])) {
            $updates[] = 'amount = ?';
            $params[] = $data['amount'];
        }
        
        if (isset($data['status'])) {
            $updates[] = 'status = ?';
            $params[] = $data['status'];
        }
        
        if (empty($updates)) {
            jsonResponse(['error' => 'Keine Änderungen angegeben'], 400);
        }
        
        $params[] = $receiptId;
        $sql = "UPDATE receipts SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => 'Beleg aktualisiert']);
        break;
        
    case 'DELETE':
        // Beleg löschen
        $receiptId = $_GET['id'] ?? null;
        if (!$receiptId) {
            jsonResponse(['error' => 'Beleg-ID erforderlich'], 400);
        }
        
        // Prüfe ob Beleg dem Benutzer gehört
        $stmt = $db->prepare("SELECT file_path FROM receipts WHERE id = ? AND user_id = ?");
        $stmt->execute([$receiptId, $user['id']]);
        $receipt = $stmt->fetch();
        
        if (!$receipt) {
            jsonResponse(['error' => 'Beleg nicht gefunden'], 404);
        }
        
        // Datei löschen
        if (file_exists($receipt['file_path'])) {
            @unlink($receipt['file_path']);
        }
        
        // Aus Datenbank löschen
        $stmt = $db->prepare("DELETE FROM receipts WHERE id = ?");
        $stmt->execute([$receiptId]);
        
        jsonResponse(['success' => true, 'message' => 'Beleg gelöscht']);
        break;
        
    default:
        jsonResponse(['error' => 'Methode nicht erlaubt'], 405);
        break;
}
