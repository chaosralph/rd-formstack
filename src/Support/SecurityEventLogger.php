<?php

declare(strict_types=1);

namespace App\Support;

final class SecurityEventLogger
{
    public static function info(string $event, array $context = []): void
    {
        self::log('low', $event, $context);
    }

    public static function warning(string $event, array $context = []): void
    {
        self::log('medium', $event, $context);
    }

    public static function high(string $event, array $context = []): void
    {
        self::log('high', $event, $context);
    }

    private static function log(string $severity, string $event, array $context): void
    {
        Logger::security(
            $event,
            $severity,
            isset($_SERVER['HTTP_X_REQUEST_ID']) && is_string($_SERVER['HTTP_X_REQUEST_ID']) ? $_SERVER['HTTP_X_REQUEST_ID'] : null,
            $context
        );
    }
}
