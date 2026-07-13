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
            'INSERT INTO contacts (name, company, email, phone, message, status, created_at, updated_at)
             VALUES (:name, :company, :email, :phone, :message, :status, NOW(), NOW())'
        );

        $stmt->execute([
            ':name' => trim($name),
            ':company' => $company !== '' ? trim($company) : null,
            ':email' => trim($email),
            ':phone' => $phone !== '' ? trim($phone) : null,
            ':message' => trim($message),
            ':status' => 'new',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public function listForDashboard(): array
    {
        $stmt = $this->pdo->query(
            'SELECT c.id, c.name, c.company, c.email, c.phone, c.message, c.status, c.admin_note, c.replied_at, c.created_at, c.updated_at,
                    (SELECT COUNT(*) FROM contact_replies r WHERE r.contact_id = c.id) AS reply_count
             FROM contacts c
             ORDER BY
                CASE c.status
                    WHEN "new" THEN 0
                    WHEN "in_progress" THEN 1
                    WHEN "answered" THEN 2
                    WHEN "archived" THEN 3
                    ELSE 4
                END,
                c.created_at DESC'
        );

        return array_map([$this, 'hydrateContact'], $stmt->fetchAll());
    }

    /** @return array<string,mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.name, c.company, c.email, c.phone, c.message, c.status, c.admin_note, c.replied_at, c.created_at, c.updated_at,
                    (SELECT COUNT(*) FROM contact_replies r WHERE r.contact_id = c.id) AS reply_count
             FROM contacts c
             WHERE c.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        return is_array($row) ? $this->hydrateContact($row) : null;
    }

    public function updateMeta(int $id, string $status, string $adminNote): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE contacts
             SET status = :status,
                 admin_note = :admin_note,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':status' => $status,
            ':admin_note' => trim($adminNote) !== '' ? trim($adminNote) : null,
        ]);
    }

    public function markAnswered(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE contacts
             SET status = :status,
                 replied_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':status' => 'answered',
        ]);
    }

    public function addReply(int $contactId, int $userId, string $recipientEmail, string $subject, string $body, bool $sentSuccess, ?string $errorMessage = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contact_replies (contact_id, user_id, recipient_email, subject, body, sent_success, error_message, sent_at, created_at)
             VALUES (:contact_id, :user_id, :recipient_email, :subject, :body, :sent_success, :error_message, :sent_at, NOW())'
        );
        $stmt->execute([
            ':contact_id' => $contactId,
            ':user_id' => $userId,
            ':recipient_email' => trim($recipientEmail),
            ':subject' => trim($subject),
            ':body' => trim($body),
            ':sent_success' => $sentSuccess ? 1 : 0,
            ':error_message' => $errorMessage,
            ':sent_at' => $sentSuccess ? date('Y-m-d H:i:s') : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public function listReplies(int $contactId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.id, r.contact_id, r.user_id, r.recipient_email, r.subject, r.body, r.sent_success, r.error_message, r.sent_at, r.created_at,
                    u.display_name AS user_display_name
             FROM contact_replies r
             INNER JOIN users u ON u.id = r.user_id
             WHERE r.contact_id = :contact_id
             ORDER BY r.created_at DESC, r.id DESC'
        );
        $stmt->execute([':contact_id' => $contactId]);

        return array_map(static function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'contact_id' => (int) ($row['contact_id'] ?? 0),
                'user_id' => (int) ($row['user_id'] ?? 0),
                'recipient_email' => (string) ($row['recipient_email'] ?? ''),
                'subject' => (string) ($row['subject'] ?? ''),
                'body' => (string) ($row['body'] ?? ''),
                'sent_success' => (bool) ($row['sent_success'] ?? false),
                'error_message' => (string) ($row['error_message'] ?? ''),
                'sent_at' => (string) ($row['sent_at'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'user_display_name' => (string) ($row['user_display_name'] ?? ''),
            ];
        }, $stmt->fetchAll());
    }

    public function countOpen(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM contacts WHERE status IN ('new', 'in_progress')");
        return (int) $stmt->fetchColumn();
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function hydrateContact(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'company' => (string) ($row['company'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'phone' => (string) ($row['phone'] ?? ''),
            'message' => (string) ($row['message'] ?? ''),
            'status' => (string) ($row['status'] ?? 'new'),
            'admin_note' => (string) ($row['admin_note'] ?? ''),
            'replied_at' => (string) ($row['replied_at'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'reply_count' => (int) ($row['reply_count'] ?? 0),
        ];
    }
}
