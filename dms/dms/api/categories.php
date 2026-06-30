<?php
/**
 * API: Kategorien verwalten
 * GET    /api/categories.php             – Alle Kategorien
 * POST   /api/categories.php             – Neue Kategorie
 * PUT    /api/categories.php?id=X        – Kategorie bearbeiten
 * DELETE /api/categories.php?id=X        – Kategorie löschen
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';

checkAuth();

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->query(
        'SELECT c.*, COUNT(d.id) as document_count
         FROM dms_categories c
         LEFT JOIN dms_documents d ON c.id = d.category_id
         GROUP BY c.id
         ORDER BY c.sort_order, c.name'
    );
    jsonResponse(['success' => true, 'categories' => $stmt->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = sanitize($input['name'] ?? '');
    $color = sanitize($input['color'] ?? '#4f46e5');

    if (empty($name)) {
        jsonError('Name ist erforderlich');
    }

    $stmt = $db->prepare('INSERT INTO dms_categories (name, color) VALUES (:name, :color)');
    $stmt->execute([':name' => $name, ':color' => $color]);

    jsonResponse([
        'success' => true,
        'category' => ['id' => (int)$db->lastInsertId(), 'name' => $name, 'color' => $color],
    ], 201);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonError('Ungültige Kategorie-ID');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name = sanitize($input['name'] ?? '');
    $color = sanitize($input['color'] ?? '#4f46e5');

    if (empty($name)) {
        jsonError('Name ist erforderlich');
    }

    $stmt = $db->prepare('UPDATE dms_categories SET name = :name, color = :color WHERE id = :id');
    $stmt->execute([':name' => $name, ':color' => $color, ':id' => $id]);

    jsonResponse(['success' => true]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonError('Ungültige Kategorie-ID');
    }

    $db->prepare('UPDATE dms_documents SET category_id = NULL WHERE category_id = :id')
       ->execute([':id' => $id]);

    $db->prepare('DELETE FROM dms_categories WHERE id = :id')->execute([':id' => $id]);

    jsonResponse(['success' => true, 'message' => 'Kategorie gelöscht']);
}

jsonError('Methode nicht erlaubt', 405);
