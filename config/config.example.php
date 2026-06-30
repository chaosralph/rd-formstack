<?php
/**
 * RD Formstack Solutions - Konfiguration (Vorlage)
 * Kopieren nach config.php und Werte anpassen.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

define('JWT_SECRET', 'change-me-to-a-long-random-string');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400);

define('APP_NAME', 'RD Formstack Solutions');
define('APP_VERSION', '1.0.0');
define('DEBUG', false);

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif', 'pdf']);

define('OCR_SERVICE', 'tesseract');
define('OCR_API_KEY', '');

function getSettingFromDb($key, $default = '') {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cache[$key] = $result ? ($result['setting_value'] ?? $default) : $default;
        return $cache[$key];
    } catch (PDOException $e) {
        return $default;
    }
}

define('SKR03_EINNAHMEN', [
    '8400' => 'Erlöse 19% USt',
    '8401' => 'Erlöse 7% USt',
    '8402' => 'Erlöse 0% USt',
    '8403' => 'Erlöse ohne USt'
]);

define('SKR03_AUSGABEN', [
    '3400' => 'Wareneingang 19% USt',
    '3401' => 'Wareneingang 7% USt',
    '3402' => 'Wareneingang 0% USt',
    '3403' => 'Wareneingang ohne USt',
    '6000' => 'Bürobedarf',
    '6001' => 'Bürobedarf 19% USt',
    '6002' => 'Bürobedarf 7% USt',
    '6300' => 'Werbekosten',
    '6301' => 'Werbekosten 19% USt',
    '6302' => 'Werbekosten 7% USt',
    '6305' => 'Reisekosten',
    '6306' => 'Reisekosten 19% USt',
    '6307' => 'Reisekosten 7% USt',
    '6308' => 'Bewirtungskosten',
    '6309' => 'Bewirtungskosten 19% USt',
    '6310' => 'Bewirtungskosten 7% USt',
    '6400' => 'Miete',
    '6401' => 'Miete 19% USt',
    '6402' => 'Miete 7% USt',
    '6500' => 'Versicherungen',
    '6501' => 'Versicherungen 19% USt',
    '6502' => 'Versicherungen 7% USt',
    '6600' => 'Telefon/Internet',
    '6601' => 'Telefon/Internet 19% USt',
    '6602' => 'Telefon/Internet 7% USt',
    '6700' => 'Fahrzeugkosten',
    '6701' => 'Fahrzeugkosten 19% USt',
    '6702' => 'Fahrzeugkosten 7% USt',
    '6800' => 'Sonstige Betriebsausgaben',
    '6801' => 'Sonstige Betriebsausgaben 19% USt',
    '6802' => 'Sonstige Betriebsausgaben 7% USt'
]);

date_default_timezone_set('Europe/Berlin');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
}

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
