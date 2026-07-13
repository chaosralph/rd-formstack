<?php

declare(strict_types=1);

namespace App\Security;

final class AuthSession
{
    private const SESSION_KEY = 'auth_user';
    private const INTENDED_PATH_KEY = 'auth_intended_path';

    /** @param array<string,mixed> $user */
    public static function login(array $user): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'id' => (int) ($user['id'] ?? 0),
            'email' => (string) ($user['email'] ?? ''),
            'display_name' => (string) ($user['display_name'] ?? ''),
            'role' => (string) ($user['role'] ?? 'admin'),
        ];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::INTENDED_PATH_KEY]);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function check(): bool
    {
        return is_array($_SESSION[self::SESSION_KEY] ?? null)
            && (int) (($_SESSION[self::SESSION_KEY]['id'] ?? 0)) > 0;
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        $user = $_SESSION[self::SESSION_KEY] ?? null;
        return is_array($user) ? $user : null;
    }

    public static function rememberIntendedPath(string $path): void
    {
        $_SESSION[self::INTENDED_PATH_KEY] = $path;
    }

    public static function consumeIntendedPath(string $default = '/dashboard'): string
    {
        $path = $_SESSION[self::INTENDED_PATH_KEY] ?? $default;
        unset($_SESSION[self::INTENDED_PATH_KEY]);

        return is_string($path) && str_starts_with($path, '/') ? $path : $default;
    }
}
