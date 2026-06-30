<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/');
    exit;
}

$target = urlencode($_SERVER['REQUEST_URI'] ?? (SITE_URL . '/'));
header('Location: ' . MAIN_APP_LOGIN_URL . '?redirect=' . $target);
exit;
