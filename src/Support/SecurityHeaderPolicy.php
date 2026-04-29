<?php

declare(strict_types=1);

namespace App\Support;

use App\Config\Env;

final class SecurityHeaderPolicy
{
    public static function apply(): void
    {
        foreach (self::buildHeaders($_SERVER) as $name => $value) {
            if ($value === null) {
                header_remove($name);
                continue;
            }
            header($name . ': ' . $value);
        }
    }

    /**
     * @param array<string, mixed> $server
     * @return array<string, string|null>
     */
    public static function buildHeaders(array $server): array
    {
        $headers = [];
        $isHttps = self::isHttpsRequest($server);
        $hstsEnabled = self::envFlag('SECURITY_HSTS_ENABLED', self::envFlag('ENABLE_HSTS', false));
        if ($isHttps && $hstsEnabled) {
            $maxAge = Env::get('SECURITY_HSTS_MAX_AGE', '31536000');
            $includeSubdomains = self::envFlag('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true) ? '; includeSubDomains' : '';
            $preload = self::envFlag('SECURITY_HSTS_PRELOAD', false) ? '; preload' : '';
            $headers['Strict-Transport-Security'] = 'max-age=' . preg_replace('/[^0-9]/', '', (string) $maxAge) . $includeSubdomains . $preload;
        }

        $csp = "default-src 'self'; script-src 'self'; style-src 'self'; font-src 'self' data:; img-src 'self' data:; connect-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'";
        if (self::envFlag('SECURITY_CSP_REPORT_ONLY', self::envFlag('CSP_REPORT_ONLY', false))) {
            $headers['Content-Security-Policy-Report-Only'] = $csp;
            $headers['Content-Security-Policy'] = null;
            return $headers;
        }

        $headers['Content-Security-Policy'] = $csp;
        return $headers;
    }

    /**
     * @param array<string, mixed> $server
     */
    private static function isHttpsRequest(array $server): bool
    {
        $https = strtolower((string) ($server['HTTPS'] ?? ''));
        if ($https === 'on' || $https === '1') {
            return true;
        }

        $forwardedProto = strtolower((string) ($server['HTTP_X_FORWARDED_PROTO'] ?? ''));
        return $forwardedProto === 'https';
    }

    private static function envFlag(string $key, bool $default): bool
    {
        $value = strtolower((string) Env::get($key, $default ? '1' : '0'));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}
