<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Config\Env;

final class AppBootstrap
{
    public static function init(string $projectRoot): void
    {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

        require_once $projectRoot . '/config/env.php';
        Env::load($projectRoot . '/.env');
    }
}
