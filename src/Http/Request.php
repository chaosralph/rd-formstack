<?php

declare(strict_types=1);

namespace App\Http;

use App\Support\SecurityEventLogger;

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
        $invalidForwardedForParts = 0;
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if (is_string($forwardedFor) && $forwardedFor !== '') {
            foreach (explode(',', $forwardedFor) as $item) {
                $candidate = trim($item);
                if ($candidate === '') {
                    continue;
                }
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    $candidates[] = $candidate;
                    continue;
                }
                $invalidForwardedForParts++;
            }
        }

        if ($invalidForwardedForParts > 0) {
            SecurityEventLogger::warning('request_forwarded_for_invalid', [
                'invalid_parts' => $invalidForwardedForParts,
                'path' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => self::method(),
            ]);
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
