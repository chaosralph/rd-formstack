<?php

declare(strict_types=1);

namespace App\Support;

final class Logger
{
    /** @var array<int, string> */
    private const SENSITIVE_KEYS = [
        'password',
        'pass',
        'secret',
        'token',
        'authorization',
        'cookie',
        'email',
        'phone',
        'message',
        'name',
    ];

    public static function error(string $message, array $context = []): void
    {
        self::write([
            'level' => 'error',
            'timestamp' => gmdate('c'),
            'message' => $message,
            'context' => self::sanitizeContext($context),
        ]);
    }

    public static function security(string $eventType, string $severity, ?string $requestId, array $context = []): void
    {
        self::write([
            'level' => 'security',
            'timestamp' => gmdate('c'),
            'event_type' => $eventType,
            'severity' => $severity,
            'request_id' => $requestId,
            'context' => self::sanitizeContext($context),
        ]);
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        self::write([
            'level' => $level,
            'timestamp' => gmdate('c'),
            'message' => $message,
            'context' => self::sanitizeContext($context),
        ]);
    }

    private static function write(array $payload): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        error_log(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, 3, $logDir . '/app.log');
    }

    private static function sanitizeContext(array $context): array
    {
        $sanitized = [];
        foreach ($context as $key => $value) {
            $keyString = is_string($key) ? strtolower($key) : (string) $key;
            if (self::containsSensitiveKey($keyString)) {
                $sanitized[$key] = '[redacted]';
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $sanitized[$key] = $value;
                continue;
            }

            $sanitized[$key] = '[' . gettype($value) . ']';
        }

        return $sanitized;
    }

    private static function containsSensitiveKey(string $key): bool
    {
        foreach (self::SENSITIVE_KEYS as $needle) {
            if (str_contains($key, $needle)) {
                return true;
            }
        }

        return false;
    }
}
