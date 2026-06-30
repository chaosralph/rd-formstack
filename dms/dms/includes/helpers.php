<?php

function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function jsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $code = 400): never
{
    jsonResponse(['success' => false, 'error' => $message], $code);
}

function generateFilename(string $extension = 'pdf'): string
{
    return date('Y-m-d_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
}

function createFilenameSlug(string $value, int $maxLength = 80): string
{
    $value = trim($value);
    $value = str_replace(
        ['Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß'],
        ['Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss'],
        $value
    );
    $value = preg_replace('/[^a-zA-Z0-9]+/', '_', $value) ?? '';
    $value = trim($value, '_');
    $value = substr($value, 0, $maxLength);

    return $value !== '' ? $value : 'Dokument';
}

function generateAccountingPdfFilename(string $title, string $receiptDate): string
{
    $slug = createFilenameSlug($title, 70);
    return $receiptDate . '_Beleg_' . $slug . '_' . bin2hex(random_bytes(3)) . '.pdf';
}

function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

function ensureDirectories(): void
{
    $dirs = [UPLOAD_DIR, ORIGINALS_DIR, PDF_DIR];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

function checkAuth(): void
{
    if (!AUTH_REQUIRED) {
        return;
    }
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // DMS ist in rd-formstack integriert: bestehende Haupt-Session nutzen.
    if (empty($_SESSION['user_id'])) {
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            jsonError('Nicht autorisiert', 401);
        }
        $target = urlencode($_SERVER['REQUEST_URI'] ?? (SITE_URL . '/'));
        header('Location: ' . MAIN_APP_LOGIN_URL . '?redirect=' . $target);
        exit;
    }
}

function createThumbnail(string $sourcePath, string $destPath, int $maxWidth = 300, int $maxHeight = 400): bool
{
    $info = getimagesize($sourcePath);
    if ($info === false) {
        return false;
    }

    [$origWidth, $origHeight, $type] = $info;

    $image = match ($type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
        IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
        IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
        default => false,
    };

    if ($image === false) {
        return false;
    }

    $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
    $newWidth = (int)round($origWidth * $ratio);
    $newHeight = (int)round($origHeight * $ratio);

    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    $white = imagecolorallocate($thumb, 255, 255, 255);
    imagefill($thumb, 0, 0, $white);
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    $result = imagejpeg($thumb, $destPath, 85);

    imagedestroy($image);
    imagedestroy($thumb);

    return $result;
}
