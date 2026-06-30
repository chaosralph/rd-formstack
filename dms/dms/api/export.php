<?php
/**
 * API: PDF-Export
 * GET /api/export.php?id=X          – Einzelne PDF herunterladen
 * GET /api/export.php?all=1         – Alle PDFs als ZIP
 * GET /api/export.php?category=X    – Alle PDFs einer Kategorie als ZIP
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';

checkAuth();

$db = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
$exportAll = !empty($_GET['all']);
$categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : null;

if ($id > 0) {
    $stmt = $db->prepare('SELECT * FROM dms_documents WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        http_response_code(404);
        echo 'Dokument nicht gefunden';
        exit;
    }

    $pdfPath = PDF_DIR . $doc['pdf_filename'];
    if (!file_exists($pdfPath)) {
        http_response_code(404);
        echo 'PDF-Datei nicht gefunden';
        exit;
    }

    $downloadTitle = createFilenameSlug($doc['title'] ?? 'Dokument', 70);
    $downloadDate = !empty($doc['receipt_date']) ? $doc['receipt_date'] : date('Y-m-d');
    $downloadName = $downloadDate . '_Beleg_' . $downloadTitle . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($pdfPath));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    readfile($pdfPath);
    exit;
}

if ($exportAll || $categoryId) {
    $where = '';
    $params = [];

    if ($categoryId) {
        $where = 'WHERE d.category_id = :category_id';
        $params[':category_id'] = $categoryId;
    }

    $stmt = $db->prepare(
        "SELECT d.*, c.name as category_name
         FROM dms_documents d
         LEFT JOIN dms_categories c ON d.category_id = c.id
         $where
         ORDER BY d.created_at DESC"
    );
    $stmt->execute($params);
    $documents = $stmt->fetchAll();

    if (empty($documents)) {
        http_response_code(404);
        echo 'Keine Dokumente gefunden';
        exit;
    }

    $zipFilename = 'DMS_Export_' . date('Y-m-d_His') . '.zip';
    $zipPath = sys_get_temp_dir() . '/' . $zipFilename;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        echo 'Fehler beim Erstellen des ZIP-Archivs';
        exit;
    }

    foreach ($documents as $doc) {
        $pdfPath = PDF_DIR . $doc['pdf_filename'];
        if (!file_exists($pdfPath)) {
            continue;
        }

        $folder = $doc['category_name'] ?? 'Ohne Kategorie';
        $folder = preg_replace('/[^a-zA-Z0-9äöüÄÖÜß\s\-_]/', '', $folder);
        $name = createFilenameSlug($doc['title'] ?? 'Dokument', 70);
        $datePrefix = !empty($doc['receipt_date']) ? $doc['receipt_date'] . '_Beleg_' : '';

        $zip->addFile($pdfPath, "$folder/$datePrefix$name.pdf");
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    readfile($zipPath);
    unlink($zipPath);
    exit;
}

jsonError('Ungültige Export-Parameter');
