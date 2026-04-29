<?php

declare(strict_types=1);

namespace App\Support;

use App\Config\Env;

final class AppUrl
{
    public static function baseUrl(string $scheme, string $host): string
    {
        $configured = Env::get('APP_BASE_URL') ?? Env::get('APP_URL');
        if (is_string($configured) && $configured !== '') {
            $validated = self::normalize($configured);
            if ($validated !== null) {
                return $validated;
            }

            SecurityEventLogger::warning('invalid_app_base_url_config', [
                'event_category' => 'config',
            ]);
        }

        return self::normalize($scheme . '://' . $host) ?? ($scheme . '://' . $host);
    }

    public static function absolute(string $baseUrl, string $path): string
    {
        $normalizedPath = '/' . ltrim($path, '/');
        if ($normalizedPath === '//') {
            $normalizedPath = '/';
        }

        return rtrim($baseUrl, '/') . ($normalizedPath === '/' ? '/' : $normalizedPath);
    }

    private static function normalize(string $url): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        $parts = parse_url($trimmed);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            return null;
        }

        $port = isset($parts['port']) ? ':' . (string) $parts['port'] : '';
        return $scheme . '://' . $host . $port;
    }
}
