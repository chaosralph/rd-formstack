<?php

declare(strict_types=1);

namespace App\Repository;

interface UserRepositoryInterface
{
    /** @return array<string, mixed>|null */
    public function findActiveByEmail(string $email): ?array;

    public function countUsers(): int;

    public function createAdminUser(string $displayName, string $email, string $passwordHash): int;

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array;

    public function touchLastLogin(int $id): void;

    public function emailExistsForOtherUser(string $email, int $userId): bool;

    public function updateProfile(int $id, string $displayName, string $email): void;

    public function updatePassword(int $id, string $passwordHash): void;
}
