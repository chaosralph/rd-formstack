<?php

declare(strict_types=1);

namespace App\Support;

final class SecurityEventLogger
{
    private static ?string $requestId = null;

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
            self::normalizeSeverity($severity),
            self::resolveRequestId(),
            $context
        );
    }

    private static function normalizeSeverity(string $severity): string
    {
        $normalized = strtolower(trim($severity));
        if (!in_array($normalized, ['low', 'medium', 'high'], true)) {
            return 'medium';
        }

        return $normalized;
    }

    private static function resolveRequestId(): string
    {
        if (self::$requestId !== null && self::$requestId !== '') {
            return self::$requestId;
        }

        $serverKeys = ['HTTP_X_REQUEST_ID', 'REQUEST_ID'];
        foreach ($serverKeys as $key) {
            $value = $_SERVER[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                self::$requestId = trim($value);
                return self::$requestId;
            }
        }

        self::$requestId = bin2hex(random_bytes(8));
        $_SERVER['HTTP_X_REQUEST_ID'] = self::$requestId;
        return self::$requestId;
    }
}
