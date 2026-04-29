<?php

declare(strict_types=1);

namespace App\Support;

final class SecurityEventLogger
{
    public static function info(string $event, array $context = []): void
    {
        self::log('info', $event, $context);
    }

    public static function warning(string $event, array $context = []): void
    {
        self::log('warning', $event, $context);
    }

    private static function log(string $level, string $event, array $context): void
    {
        Logger::log($level, $event, $context + [
            'event_type' => 'security',
        ]);
    }
}
