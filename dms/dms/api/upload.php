<?php
/**
 * API: Bilder hochladen und als PDF speichern
 * POST /api/upload.php
 * 
 * Erwartet:
 *   - images[]     : Bilddateien (multipart/form-data)
 *   - title        : Dokumententitel
 *   - description  : Beschreibung (optional)
 *   - category_id  : Kategorie-ID (optional)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/PdfGenerator.php';

checkAuth();
ensureDirectories();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Nur POST erlaubt', 405);
}

function ensureReceiptDateColumn(PDO $db): void
{
    $stmt = $db->query("SHOW COLUMNS FROM dms_documents LIKE 'receipt_date'");
    $exists = $stmt !== false && $stmt->fetch(PDO::FETCH_ASSOC);
    if ($exists) {
        return;
    }

    $db->exec("ALTER TABLE dms_documents ADD COLUMN receipt_date DATE NULL AFTER title");
}

$title = sanitize($_POST['title'] ?? '');
$receiptDate = trim($_POST['receipt_date'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

if (empty($title)) {
    jsonError('Titel ist erforderlich');
}

if ($receiptDate === '') {
    jsonError('Belegdatum ist erforderlich');
}

$dateObj = DateTime::createFromFormat('Y-m-d', $receiptDate);
$dateErrors = DateTime::getLastErrors();
if (
    $dateObj === false ||
    ($dateErrors !== false && (($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0)) ||
    $dateObj->format('Y-m-d') !== $receiptDate
) {
    jsonError('Ungültiges Belegdatum');
}

if (empty($_FILES['images']) || empty($_FILES['images']['name'][0])) {
    jsonError('Mindestens ein Bild ist erforderlich');
}

$files = $_FILES['images'];
$fileCount = count($files['name']);
$imagePaths = [];
$originalFilenames = [];

try {
    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Fehler beim Upload von Datei " . ($i + 1));
        }

        if ($files['size'][$i] > MAX_FILE_SIZE) {
            throw new RuntimeException("Datei " . ($i + 1) . " ist zu groß (max. " . formatFileSize(MAX_FILE_SIZE) . ")");
        }

        $mimeType = mime_content_type($files['tmp_name'][$i]);
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
            throw new RuntimeException("Datei " . ($i + 1) . ": Ungültiger Dateityp ($mimeType)");
        }

        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/heic', 'image/heif' => 'heic',
            default => 'jpg',
        };

        $origFilename = generateFilename($ext);
        $origPath = ORIGINALS_DIR . $origFilename;

        if (!move_uploaded_file($files['tmp_name'][$i], $origPath)) {
            throw new RuntimeException("Fehler beim Speichern von Datei " . ($i + 1));
        }

        $imagePaths[] = $origPath;
        $originalFilenames[] = $origFilename;
    }

    $pdfFilename = generateAccountingPdfFilename($title, $receiptDate);
    $pdfPath = PDF_DIR . $pdfFilename;

    if (!PdfGenerator::createFromImages($imagePaths, $pdfPath, $title)) {
        throw new RuntimeException('Fehler bei der PDF-Erzeugung');
    }

    $thumbnailFilename = 'thumb_' . generateFilename('jpg');
    $thumbnailPath = PDF_DIR . $thumbnailFilename;
    if (!createThumbnail($imagePaths[0], $thumbnailPath)) {
        $thumbnailFilename = null;
    }

    $pdfSize = filesize($pdfPath);

    $db = Database::getConnection();
    ensureReceiptDateColumn($db);
    $db->beginTransaction();

    $stmt = $db->prepare(
        'INSERT INTO dms_documents (title, receipt_date, description, category_id, pdf_filename, pdf_size, page_count, thumbnail)
         VALUES (:title, :receipt_date, :description, :category_id, :pdf_filename, :pdf_size, :page_count, :thumbnail)'
    );
    $stmt->execute([
        ':title' => $title,
        ':receipt_date' => $receiptDate,
        ':description' => $description ?: null,
        ':category_id' => $categoryId,
        ':pdf_filename' => $pdfFilename,
        ':pdf_size' => $pdfSize,
        ':page_count' => $fileCount,
        ':thumbnail' => $thumbnailFilename,
    ]);

    $documentId = (int)$db->lastInsertId();

    $pageStmt = $db->prepare(
        'INSERT INTO dms_document_pages (document_id, page_number, original_filename)
         VALUES (:document_id, :page_number, :original_filename)'
    );

    foreach ($originalFilenames as $index => $filename) {
        $pageStmt->execute([
            ':document_id' => $documentId,
            ':page_number' => $index + 1,
            ':original_filename' => $filename,
        ]);
    }

    $db->commit();

    jsonResponse([
        'success' => true,
        'document' => [
            'id' => $documentId,
            'title' => $title,
            'receipt_date' => $receiptDate,
            'page_count' => $fileCount,
            'pdf_size' => formatFileSize($pdfSize),
        ],
    ], 201);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    foreach ($imagePaths as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }
    if (isset($pdfPath) && file_exists($pdfPath)) {
        unlink($pdfPath);
    }
    if (isset($thumbnailPath) && file_exists($thumbnailPath)) {
        unlink($thumbnailPath);
    }

    jsonError($e->getMessage(), 500);
}
