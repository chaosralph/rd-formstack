<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Application\Auth\AdminSetupService;
use App\Repository\UserRepositoryInterface;

final class SetupRepository implements UserRepositoryInterface
{
    /** @var array<int, array<string, mixed>> */
    private array $users = [];

    public function __construct(array $users = [])
    {
        $this->users = $users;
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

$service = new AdminSetupService(new SetupRepository());

$result = $service->createInitialAdmin(' Ralph Domin ', ' INFO@rddigital.de ', 'geheim123', 'geheim123');
assertTrue($result['ok'] === true, 'first admin setup must succeed for empty user store');
assertSame('info@rddigital.de', $result['user']['email'] ?? null, 'admin setup must normalize email');
assertTrue(password_verify('geheim123', $result['user']['password_hash'] ?? ''), 'stored password hash must verify the chosen password');
assertSame('admin', $result['user']['role'] ?? null, 'first admin must get admin role');

$serviceWithExistingUser = new AdminSetupService(new SetupRepository([
    [
        'id' => 1,
        'email' => 'existing@rddigital.de',
        'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
        'display_name' => 'Existing',
        'role' => 'admin',
        'is_active' => true,
    ],
]));

$blocked = $serviceWithExistingUser->createInitialAdmin('Second User', 'second@rddigital.de', 'geheim123', 'geheim123');
assertTrue($blocked['ok'] === false, 'initial admin setup must be blocked once users exist');
assertSame('Der erste Zugang wurde bereits eingerichtet.', $blocked['error'] ?? null, 'existing users must block initial setup');

$mismatchService = new AdminSetupService(new SetupRepository());
$mismatch = $mismatchService->createInitialAdmin('Ralph Domin', 'other@rddigital.de', 'geheim123', 'anders');
assertTrue($mismatch['ok'] === false, 'password confirmation mismatch must fail');
assertSame('Bitte prüfen Sie die markierten Eingaben.', $mismatch['error'] ?? null, 'mismatch must return validation error');
assertTrue(isset($mismatch['errors']['password_confirmation']), 'password confirmation error must be exposed');

fwrite(STDOUT, "OK: admin setup service behavior verified\n");
