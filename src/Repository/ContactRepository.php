<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class ContactRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(string $name, string $company, string $email, string $phone, string $message): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contacts (name, company, email, phone, message, created_at)
            VALUES (:name, :company, :email, :phone, :message, NOW())'
        );

        $stmt->execute([
            ':name' => trim($name),
            ':company' => $company !== '' ? trim($company) : null,
            ':email' => trim($email),
            ':phone' => $phone !== '' ? trim($phone) : null,
            ':message' => trim($message),
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
