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
        $enrichedMessage = trim($message) . "\n\n"
            . 'Unternehmen: ' . trim($company) . "\n"
            . 'Telefon: ' . ($phone !== '' ? trim($phone) : 'nicht angegeben');

        $stmt = $this->pdo->prepare(
            'INSERT INTO contacts (name, email, message, created_at) VALUES (:name, :email, :message, NOW())'
        );

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':message' => $enrichedMessage,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
