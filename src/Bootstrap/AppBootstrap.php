<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Config\Env;

final class AppBootstrap
{
    public static function init(string $projectRoot): void
    {
        require_once $projectRoot . '/config/env.php';
        Env::load($projectRoot . '/.env');

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

        $csp = "default-src 'self'; script-src 'self'; style-src 'self'; font-src 'self' data:; img-src 'self' data:; connect-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'";
        if (strtolower((string) Env::get('CSP_REPORT_ONLY', 'false')) === 'true') {
            header('Content-Security-Policy-Report-Only: ' . $csp);
        } else {
            header('Content-Security-Policy: ' . $csp);
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        if ($isHttps && strtolower((string) Env::get('ENABLE_HSTS', 'false')) === 'true') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
