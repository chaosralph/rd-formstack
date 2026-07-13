<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Application\Auth\LoginService;
use App\Repository\UserRepositoryInterface;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @param array<int, array<string, mixed>> $users */
    public function __construct(private array $users)
    {
    }

    public function findActiveByEmail(string $email): ?array
    {
        foreach ($this->users as $user) {
            if (strcasecmp((string) $user['email'], $email) === 0 && ($user['is_active'] ?? true) === true) {
                return $user;
            }
        }

        return null;
    }

    public function countUsers(): int
    {
        return count($this->users);
    }

    public function createAdminUser(string $displayName, string $email, string $passwordHash): int
    {
        $id = count($this->users) + 1;
        $this->users[] = [
            'id' => $id,
            'email' => $email,
            'password_hash' => $passwordHash,
            'display_name' => $displayName,
            'role' => 'admin',
            'is_active' => true,
        ];

        return $id;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->users as $user) {
            if ((int) ($user['id'] ?? 0) === $id) {
                return $user;
            }
        }

        return null;
    }
}

$hash = password_hash('geheim123', PASSWORD_DEFAULT);
assertTrue(is_string($hash), 'password hash must be generated for test setup');

$service = new LoginService(new InMemoryUserRepository([
    [
        'id' => 7,
        'email' => 'admin@rddigital.de',
        'password_hash' => $hash,
        'display_name' => 'Ralph Domin',
        'role' => 'admin',
        'is_active' => true,
    ],
    [
        'id' => 8,
        'email' => 'inactive@rddigital.de',
        'password_hash' => $hash,
        'display_name' => 'Inactive User',
        'role' => 'admin',
        'is_active' => false,
    ],
]));

$success = $service->authenticate('  ADMIN@rddigital.de ', 'geheim123');
assertTrue($success['ok'] === true, 'valid credentials must authenticate successfully');
assertSame(7, $success['user']['id'] ?? null, 'authenticated user id must be returned');
assertSame('admin@rddigital.de', $success['user']['email'] ?? null, 'email must be normalized to stored user email');
assertSame('admin', $success['user']['role'] ?? null, 'role must be returned for dashboard guards');

$wrongPassword = $service->authenticate('admin@rddigital.de', 'falsch');
assertTrue($wrongPassword['ok'] === false, 'wrong password must be rejected');
assertSame('E-Mail oder Passwort ist nicht korrekt.', $wrongPassword['error'] ?? null, 'wrong password must return generic error');

$inactiveUser = $service->authenticate('inactive@rddigital.de', 'geheim123');
assertTrue($inactiveUser['ok'] === false, 'inactive users must not authenticate');
assertSame('E-Mail oder Passwort ist nicht korrekt.', $inactiveUser['error'] ?? null, 'inactive users must receive generic error');

$missingUser = $service->authenticate('missing@rddigital.de', 'geheim123');
assertTrue($missingUser['ok'] === false, 'unknown users must not authenticate');
assertSame('E-Mail oder Passwort ist nicht korrekt.', $missingUser['error'] ?? null, 'unknown users must receive generic error');

fwrite(STDOUT, "OK: login service behavior verified\n");
