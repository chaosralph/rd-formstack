<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function post(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    public static function ip(): string
    {
        $candidates = [];
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if (is_string($forwardedFor) && $forwardedFor !== '') {
            $candidates = array_map('trim', explode(',', $forwardedFor));
        }

        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        if (is_string($remoteAddr) && $remoteAddr !== '') {
            $candidates[] = trim($remoteAddr);
        }

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        return '0.0.0.0';
    }
}
