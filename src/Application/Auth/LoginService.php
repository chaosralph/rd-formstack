<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Repository\UserRepositoryInterface;

final class LoginService
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    /** @return array{ok:bool,user?:array<string,mixed>,error?:string} */
    public function authenticate(string $email, string $password): array
    {
        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail === '' || trim($password) === '') {
            return [
                'ok' => false,
                'error' => 'E-Mail oder Passwort ist nicht korrekt.',
            ];
        }

        $user = $this->users->findActiveByEmail($normalizedEmail);
        if (!is_array($user)) {
            return [
                'ok' => false,
                'error' => 'E-Mail oder Passwort ist nicht korrekt.',
            ];
        }

        $passwordHash = (string) ($user['password_hash'] ?? '');
        if ($passwordHash === '' || !password_verify($password, $passwordHash)) {
            return [
                'ok' => false,
                'error' => 'E-Mail oder Passwort ist nicht korrekt.',
            ];
        }

        $user['email'] = (string) ($user['email'] ?? $normalizedEmail);
        $user['display_name'] = (string) ($user['display_name'] ?? '');
        $user['role'] = (string) ($user['role'] ?? 'admin');
        $user['id'] = (int) ($user['id'] ?? 0);

        return [
            'ok' => true,
            'user' => $user,
        ];
    }
}
