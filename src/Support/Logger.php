<?php

declare(strict_types=1);

namespace App\Support;

final class Logger
{
    public static function error(string $message, array $context = []): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $payload = [
            'level' => 'error',
            'timestamp' => gmdate('c'),
            'message' => $message,
            'context' => $context,
        ];

        error_log(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, 3, $logDir . '/app.log');
    }
}
