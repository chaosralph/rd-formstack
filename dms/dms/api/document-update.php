<?php
/**
 * API: Dokument bearbeiten (Titel, Beschreibung, Kategorie)
 * PUT /api/document-update.php?id=X
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';

checkAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonError('Nur PUT erlaubt', 405);
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonError('Ungültige Dokument-ID');
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonError('Ungültige Eingabedaten');
}

$db = Database::getConnection();

$stmt = $db->prepare('SELECT id FROM dms_documents WHERE id = :id');
$stmt->execute([':id' => $id]);
if (!$stmt->fetch()) {
    jsonError('Dokument nicht gefunden', 404);
}

$fields = [];
$params = [':id' => $id];

if (isset($input['title'])) {
    $title = sanitize($input['title']);
    if (empty($title)) {
        jsonError('Titel darf nicht leer sein');
    }
    $fields[] = 'title = :title';
    $params[':title'] = $title;
}

if (array_key_exists('description', $input)) {
    $fields[] = 'description = :description';
    $params[':description'] = sanitize($input['description'] ?? '');
}

if (array_key_exists('category_id', $input)) {
    $fields[] = 'category_id = :category_id';
    $params[':category_id'] = $input['category_id'] ? (int)$input['category_id'] : null;
}

if (empty($fields)) {
    jsonError('Keine Änderungen angegeben');
}

$sql = 'UPDATE dms_documents SET ' . implode(', ', $fields) . ' WHERE id = :id';
$db->prepare($sql)->execute($params);

jsonResponse(['success' => true, 'message' => 'Dokument aktualisiert']);
