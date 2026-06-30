<?php
/**
 * DMS Configuration (Vorlage)
 * Kopieren nach config.php und Werte anpassen.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/dms/dms'));
$scriptDir = rtrim($scriptDir, '/');
if ($scriptDir === '' || $scriptDir === '.') {
    $scriptDir = '/dms/dms';
}

$mainAppUrl = preg_replace('#/dms/dms$#', '', $scriptDir);
if ($mainAppUrl === $scriptDir) {
    $mainAppUrl = '/';
}

define('SITE_URL', $scriptDir);
define('MAIN_APP_URL', rtrim($mainAppUrl, '/') ?: '/');
define('MAIN_APP_LOGIN_URL', rtrim(MAIN_APP_URL, '/') . '/login.php');
define('SITE_TITLE', 'DMS - Dokumentenmanagement');

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('ORIGINALS_DIR', UPLOAD_DIR . 'originals/');
define('PDF_DIR', UPLOAD_DIR . 'pdfs/');

define('MAX_FILE_SIZE', 20 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif']);
define('PDF_AUTHOR', 'RD Formstack Solutions');

define('ITEMS_PER_PAGE', 12);

define('SESSION_NAME', 'dms_session');
define('AUTH_REQUIRED', true);
