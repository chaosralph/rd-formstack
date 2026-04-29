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

        $fallback = self::normalize($scheme . '://' . $host);
        if ($fallback !== null && self::isTrustedHost($host)) {
            SecurityEventLogger::warning('app_base_url_host_fallback_used', [
                'event_category' => 'security',
            ]);
            return $fallback;
        }

        SecurityEventLogger::warning('untrusted_host_header_blocked_for_base_url', [
            'event_category' => 'security',
        ]);
        return $scheme . '://localhost';
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

    private static function isTrustedHost(string $host): bool
    {
        $normalizedHost = strtolower(trim($host));
        if ($normalizedHost === '' || !preg_match('/^[a-z0-9.-]+(?::[0-9]{1,5})?$/', $normalizedHost)) {
            return false;
        }

        $allowedHosts = Env::get('APP_TRUSTED_HOSTS', '');
        if (!is_string($allowedHosts) || trim($allowedHosts) === '') {
            return false;
        }

        $hostOnly = explode(':', $normalizedHost, 2)[0];
        $allowed = array_filter(array_map(
            static fn (string $value): string => strtolower(trim($value)),
            explode(',', $allowedHosts)
        ));

        return in_array($hostOnly, $allowed, true) || in_array($normalizedHost, $allowed, true);
    }
}
