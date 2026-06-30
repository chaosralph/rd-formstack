<?php
/**
 * API: Dokumente auflisten / einzelnes Dokument abrufen / löschen
 * GET    /api/documents.php              – Liste aller Dokumente
 * GET    /api/documents.php?id=X         – Einzelnes Dokument
 * GET    /api/documents.php?category=X   – Nach Kategorie filtern
 * GET    /api/documents.php?search=X     – Suche
 * DELETE /api/documents.php?id=X         – Dokument löschen
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';

checkAuth();

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonError('Ungültige Dokument-ID');
    }

    $stmt = $db->prepare('SELECT * FROM dms_documents WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        jsonError('Dokument nicht gefunden', 404);
    }

    $pagesStmt = $db->prepare('SELECT * FROM dms_document_pages WHERE document_id = :id');
    $pagesStmt->execute([':id' => $id]);
    $pages = $pagesStmt->fetchAll();

    foreach ($pages as $page) {
        $origPath = ORIGINALS_DIR . $page['original_filename'];
        if (file_exists($origPath)) {
            unlink($origPath);
        }
    }

    $pdfPath = PDF_DIR . $doc['pdf_filename'];
    if (file_exists($pdfPath)) {
        unlink($pdfPath);
    }

    if ($doc['thumbnail']) {
        $thumbPath = PDF_DIR . $doc['thumbnail'];
        if (file_exists($thumbPath)) {
            unlink($thumbPath);
        }
    }

    $db->prepare('DELETE FROM dms_documents WHERE id = :id')->execute([':id' => $id]);

    jsonResponse(['success' => true, 'message' => 'Dokument gelöscht']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        $stmt = $db->prepare(
            'SELECT d.*, c.name as category_name, c.color as category_color
             FROM dms_documents d
             LEFT JOIN dms_categories c ON d.category_id = c.id
             WHERE d.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $doc = $stmt->fetch();

        if (!$doc) {
            jsonError('Dokument nicht gefunden', 404);
        }

        $pagesStmt = $db->prepare(
            'SELECT * FROM dms_document_pages WHERE document_id = :id ORDER BY page_number'
        );
        $pagesStmt->execute([':id' => $id]);
        $doc['pages'] = $pagesStmt->fetchAll();
        $doc['pdf_size_formatted'] = formatFileSize((int)$doc['pdf_size']);

        jsonResponse(['success' => true, 'document' => $doc]);
    }

    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    $categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;
    $search = sanitize($_GET['search'] ?? '');

    $where = [];
    $params = [];

    if ($categoryId) {
        $where[] = 'd.category_id = :category_id';
        $params[':category_id'] = $categoryId;
    }

    if ($search) {
        $where[] = '(d.title LIKE :search OR d.description LIKE :search2)';
        $params[':search'] = "%$search%";
        $params[':search2'] = "%$search%";
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $db->prepare("SELECT COUNT(*) FROM dms_documents d $whereClause");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $sql = "SELECT d.*, c.name as category_name, c.color as category_color
            FROM dms_documents d
            LEFT JOIN dms_categories c ON d.category_id = c.id
            $whereClause
            ORDER BY d.created_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $documents = $stmt->fetchAll();

    foreach ($documents as &$doc) {
        $doc['pdf_size_formatted'] = formatFileSize((int)$doc['pdf_size']);
    }

    jsonResponse([
        'success' => true,
        'documents' => $documents,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'per_page' => ITEMS_PER_PAGE,
            'total_pages' => (int)ceil($total / ITEMS_PER_PAGE),
        ],
    ]);
}

jsonError('Methode nicht erlaubt', 405);
