<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findActiveByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, password_hash, display_name, role, is_active, last_login_at, created_at, updated_at
             FROM users
             WHERE LOWER(email) = LOWER(:email)
               AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute([':email' => trim($email)]);

        $user = $stmt->fetch();
        return is_array($user) ? $user : null;
    }

    public function countUsers(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
        return (int) $stmt->fetchColumn();
    }

    public function createAdminUser(string $displayName, string $email, string $passwordHash): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (display_name, email, password_hash, role, is_active, created_at, updated_at)
             VALUES (:display_name, :email, :password_hash, :role, 1, NOW(), NOW())'
        );

        $stmt->execute([
            ':display_name' => trim($displayName),
            ':email' => strtolower(trim($email)),
            ':password_hash' => $passwordHash,
            ':role' => 'admin',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, password_hash, display_name, role, is_active, last_login_at, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $user = $stmt->fetch();
        return is_array($user) ? $user : null;
    }

    public function touchLastLogin(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function emailExistsForOtherUser(string $email, int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE LOWER(email) = LOWER(:email) AND id <> :id');
        $stmt->execute([
            ':email' => strtolower(trim($email)),
            ':id' => $userId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function updateProfile(int $id, string $displayName, string $email): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET display_name = :display_name,
                 email = :email,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':display_name' => trim($displayName),
            ':email' => strtolower(trim($email)),
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':password_hash' => $passwordHash,
        ]);
    }

    public function listUsers(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, email, display_name, role, is_active, last_login_at, created_at, updated_at
             FROM users
             ORDER BY CASE role
                 WHEN "admin" THEN 1
                 WHEN "reviewer" THEN 2
                 WHEN "editor" THEN 3
                 ELSE 9
             END,
             is_active DESC,
             display_name ASC,
             email ASC'
        );

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function updateUserAdmin(int $id, string $displayName, string $email, string $role, bool $isActive): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
             SET display_name = :display_name,
                 email = :email,
                 role = :role,
                 is_active = :is_active,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':display_name' => trim($displayName),
            ':email' => strtolower(trim($email)),
            ':role' => strtolower(trim($role)),
            ':is_active' => $isActive ? 1 : 0,
        ]);
    }

    public function countActiveAdmins(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1");
        return (int) $stmt->fetchColumn();
    }
}
