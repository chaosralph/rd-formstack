<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class OutreachRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string,mixed>> */
    public function listCampaigns(string $statusFilter = 'active'): array
    {
        $sql = 'SELECT c.id, c.user_id, c.title, c.subject, c.body, c.from_email, c.from_name, c.status, c.allow_known_resend,
                       c.approved_at, c.approved_by_user_id, c.sent_at, c.last_sent_by_user_id,
                       c.archived_at, c.archived_by_user_id, c.send_attempt_count,
                       c.last_send_started_at, c.last_send_finished_at,
                       c.created_at, c.updated_at,
                       u.display_name AS user_display_name,
                       approver.display_name AS approved_by_display_name,
                       sender.display_name AS last_sent_by_display_name,
                       archiver.display_name AS archived_by_display_name,
                       (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id) AS recipient_count,
                       (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = "approved") AS approved_recipient_count,
                       (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = "sent") AS sent_recipient_count,
                       (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = "failed") AS failed_recipient_count
                FROM outreach_campaigns c
                INNER JOIN users u ON u.id = c.user_id
                LEFT JOIN users approver ON approver.id = c.approved_by_user_id
                LEFT JOIN users sender ON sender.id = c.last_sent_by_user_id
                LEFT JOIN users archiver ON archiver.id = c.archived_by_user_id';

        $conditions = [];
        $params = [];

        if ($statusFilter === 'active') {
            $conditions[] = 'c.archived_at IS NULL';
        } elseif ($statusFilter === 'archived') {
            $conditions[] = 'c.archived_at IS NOT NULL';
        } elseif ($statusFilter === 'failed') {
            $conditions[] = 'c.status IN ("partial", "failed")';
        } elseif ($statusFilter !== 'all') {
            $conditions[] = 'c.status = :status';
            $params[':status'] = $statusFilter;
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY COALESCE(c.archived_at, c.updated_at) DESC, c.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map([$this, 'hydrateCampaign'], $stmt->fetchAll());
    }

    /** @return array<string,mixed>|null */
    public function findCampaignById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.user_id, c.title, c.subject, c.body, c.from_email, c.from_name, c.status, c.allow_known_resend,
                    c.approved_at, c.approved_by_user_id, c.sent_at, c.last_sent_by_user_id,
                    c.archived_at, c.archived_by_user_id, c.send_attempt_count,
                    c.last_send_started_at, c.last_send_finished_at,
                    c.created_at, c.updated_at,
                    u.display_name AS user_display_name,
                    approver.display_name AS approved_by_display_name,
                    sender.display_name AS last_sent_by_display_name,
                    archiver.display_name AS archived_by_display_name,
                    (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id) AS recipient_count,
                    (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = "approved") AS approved_recipient_count,
                    (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = "sent") AS sent_recipient_count,
                    (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = "failed") AS failed_recipient_count
             FROM outreach_campaigns c
             INNER JOIN users u ON u.id = c.user_id
             LEFT JOIN users approver ON approver.id = c.approved_by_user_id
             LEFT JOIN users sender ON sender.id = c.last_sent_by_user_id
             LEFT JOIN users archiver ON archiver.id = c.archived_by_user_id
             WHERE c.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return is_array($row) ? $this->hydrateCampaign($row) : null;
    }

    public function createCampaign(int $userId, string $title, string $subject, string $body, string $fromEmail, string $fromName, bool $allowKnownResend): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO outreach_campaigns (user_id, title, subject, body, from_email, from_name, status, allow_known_resend, approved_at, approved_by_user_id, sent_at, last_sent_by_user_id, archived_at, archived_by_user_id, send_attempt_count, last_send_started_at, last_send_finished_at, created_at, updated_at)
             VALUES (:user_id, :title, :subject, :body, :from_email, :from_name, :status, :allow_known_resend, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NOW(), NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => trim($title),
            ':subject' => trim($subject),
            ':body' => trim($body),
            ':from_email' => trim($fromEmail),
            ':from_name' => trim($fromName),
            ':status' => 'draft',
            ':allow_known_resend' => $allowKnownResend ? 1 : 0,
        ]);

        $campaignId = (int) $this->pdo->lastInsertId();
        $this->recordCampaignEvent($campaignId, $userId, 'campaign_created', 'Kampagne angelegt');

        return $campaignId;
    }

    public function updateCampaign(int $campaignId, string $title, string $subject, string $body, string $fromEmail, string $fromName, bool $allowKnownResend, int $actorUserId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE outreach_campaigns
             SET title = :title,
                 subject = :subject,
                 body = :body,
                 from_email = :from_email,
                 from_name = :from_name,
                 status = :status,
                 allow_known_resend = :allow_known_resend,
                 approved_at = NULL,
                 approved_by_user_id = NULL,
                 sent_at = NULL,
                 last_sent_by_user_id = NULL,
                 archived_at = NULL,
                 archived_by_user_id = NULL,
                 last_send_started_at = NULL,
                 last_send_finished_at = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $campaignId,
            ':title' => trim($title),
            ':subject' => trim($subject),
            ':body' => trim($body),
            ':from_email' => trim($fromEmail),
            ':from_name' => trim($fromName),
            ':status' => 'draft',
            ':allow_known_resend' => $allowKnownResend ? 1 : 0,
        ]);

        $this->recordCampaignEvent($campaignId, $actorUserId, 'campaign_updated', 'Entwurf aktualisiert und wieder auf draft gesetzt');
    }

    /** @param list<array{email:string,company_name:string,contact_name:string,notes:string}> $recipients */
    public function replaceRecipients(int $campaignId, array $recipients, int $actorUserId): void
    {
        $delete = $this->pdo->prepare('DELETE FROM outreach_recipients WHERE campaign_id = :campaign_id');
        $delete->execute([':campaign_id' => $campaignId]);

        if ($recipients === []) {
            $this->recordCampaignEvent($campaignId, $actorUserId, 'recipients_replaced', 'Empfängerliste geleert', ['recipient_count' => 0]);
            return;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO outreach_recipients (campaign_id, email, company_name, contact_name, notes, status, error_message, sent_at, created_at, updated_at)
             VALUES (:campaign_id, :email, :company_name, :contact_name, :notes, :status, NULL, NULL, NOW(), NOW())'
        );

        foreach ($recipients as $recipient) {
            $insert->execute([
                ':campaign_id' => $campaignId,
                ':email' => trim($recipient['email']),
                ':company_name' => trim($recipient['company_name']) !== '' ? trim($recipient['company_name']) : null,
                ':contact_name' => trim($recipient['contact_name']) !== '' ? trim($recipient['contact_name']) : null,
                ':notes' => trim($recipient['notes']) !== '' ? trim($recipient['notes']) : null,
                ':status' => 'draft',
            ]);
        }

        $this->recordCampaignEvent($campaignId, $actorUserId, 'recipients_replaced', 'Empfängerliste ersetzt', ['recipient_count' => count($recipients)]);
    }

    public function approveCampaign(int $campaignId, int $actorUserId): void
    {
        $campaign = $this->pdo->prepare(
            'UPDATE outreach_campaigns
             SET status = :status,
                 approved_at = NOW(),
                 approved_by_user_id = :approved_by_user_id,
                 archived_at = NULL,
                 archived_by_user_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $campaign->execute([
            ':id' => $campaignId,
            ':status' => 'approved',
            ':approved_by_user_id' => $actorUserId,
        ]);

        $recipients = $this->pdo->prepare(
            'UPDATE outreach_recipients
             SET status = :status,
                 error_message = NULL,
                 sent_at = NULL,
                 updated_at = NOW()
             WHERE campaign_id = :campaign_id'
        );
        $recipients->execute([
            ':campaign_id' => $campaignId,
            ':status' => 'approved',
        ]);

        $this->recordCampaignEvent($campaignId, $actorUserId, 'campaign_approved', 'Anschreiben und Empfängerliste freigegeben');
    }

    public function resetCampaignToDraft(int $campaignId, int $actorUserId): void
    {
        $campaign = $this->pdo->prepare(
            'UPDATE outreach_campaigns
             SET status = :status,
                 approved_at = NULL,
                 approved_by_user_id = NULL,
                 sent_at = NULL,
                 last_sent_by_user_id = NULL,
                 archived_at = NULL,
                 archived_by_user_id = NULL,
                 last_send_started_at = NULL,
                 last_send_finished_at = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $campaign->execute([
            ':id' => $campaignId,
            ':status' => 'draft',
        ]);

        $recipients = $this->pdo->prepare(
            'UPDATE outreach_recipients
             SET status = :status,
                 error_message = NULL,
                 sent_at = NULL,
                 updated_at = NOW()
             WHERE campaign_id = :campaign_id'
        );
        $recipients->execute([
            ':campaign_id' => $campaignId,
            ':status' => 'draft',
        ]);

        $this->recordCampaignEvent($campaignId, $actorUserId, 'campaign_reset', 'Kampagne auf draft zurückgesetzt');
    }

    public function reapproveFailedRecipients(int $campaignId, int $actorUserId): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE outreach_recipients
             SET status = :status,
                 error_message = NULL,
                 sent_at = NULL,
                 updated_at = NOW()
             WHERE campaign_id = :campaign_id
               AND status = :failed_status'
        );
        $stmt->execute([
            ':campaign_id' => $campaignId,
            ':status' => 'approved',
            ':failed_status' => 'failed',
        ]);
        $count = $stmt->rowCount();

        if ($count > 0) {
            $campaign = $this->pdo->prepare(
                'UPDATE outreach_campaigns
                 SET status = :status,
                     approved_at = NOW(),
                     approved_by_user_id = :approved_by_user_id,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $campaign->execute([
                ':id' => $campaignId,
                ':status' => 'approved',
                ':approved_by_user_id' => $actorUserId,
            ]);

            $this->recordCampaignEvent($campaignId, $actorUserId, 'failed_reapproved', 'Fehlgeschlagene Empfänger erneut freigegeben', ['recipient_count' => $count]);
        }

        return $count;
    }

    public function archiveCampaign(int $campaignId, int $actorUserId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE outreach_campaigns
             SET status = :status,
                 archived_at = NOW(),
                 archived_by_user_id = :archived_by_user_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $campaignId,
            ':status' => 'archived',
            ':archived_by_user_id' => $actorUserId,
        ]);

        $this->recordCampaignEvent($campaignId, $actorUserId, 'campaign_archived', 'Kampagne archiviert');
    }

    public function duplicateCampaign(int $campaignId, int $actorUserId): int
    {
        $campaign = $this->findCampaignById($campaignId);
        if ($campaign === null) {
            return 0;
        }

        $newCampaignId = $this->createCampaign(
            (int) $campaign['user_id'],
            (string) $campaign['title'] . ' (Kopie)',
            (string) $campaign['subject'],
            (string) $campaign['body'],
            (string) $campaign['from_email'],
            (string) $campaign['from_name'],
            !empty($campaign['allow_known_resend'])
        );

        $recipients = $this->listRecipients($campaignId);
        $payload = array_map(
            static fn (array $recipient): array => [
                'email' => (string) ($recipient['email'] ?? ''),
                'company_name' => (string) ($recipient['company_name'] ?? ''),
                'contact_name' => (string) ($recipient['contact_name'] ?? ''),
                'notes' => (string) ($recipient['notes'] ?? ''),
            ],
            $recipients
        );

        $this->replaceRecipients($newCampaignId, $payload, $actorUserId);
        $this->recordCampaignEvent($newCampaignId, $actorUserId, 'campaign_duplicated', 'Kampagne aus Vorlage dupliziert', ['source_campaign_id' => $campaignId]);

        return $newCampaignId;
    }

    public function markSendStarted(int $campaignId, int $actorUserId, int $recipientCount): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE outreach_campaigns
             SET status = :status,
                 send_attempt_count = send_attempt_count + 1,
                 last_send_started_at = NOW(),
                 last_send_finished_at = NULL,
                 last_sent_by_user_id = :last_sent_by_user_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $campaignId,
            ':status' => 'sending',
            ':last_sent_by_user_id' => $actorUserId,
        ]);

        $this->recordCampaignEvent($campaignId, $actorUserId, 'send_started', 'Versandlauf gestartet', ['recipient_count' => $recipientCount]);
    }

    /** @param list<string> $failedEmails */
    public function markSendCompleted(int $campaignId, int $actorUserId, string $status, int $sentCount, int $failedCount, array $failedEmails = []): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE outreach_campaigns
             SET status = :status,
                 sent_at = :sent_at,
                 last_send_finished_at = NOW(),
                 last_sent_by_user_id = :last_sent_by_user_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $campaignId,
            ':status' => $status,
            ':sent_at' => $sentCount > 0 ? date('Y-m-d H:i:s') : null,
            ':last_sent_by_user_id' => $actorUserId,
        ]);

        $this->recordCampaignEvent($campaignId, $actorUserId, 'send_completed', 'Versandlauf abgeschlossen', [
            'status' => $status,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'failed_emails' => $failedEmails,
        ]);
    }

    /** @return list<array<string,mixed>> */
    public function listRecipients(int $campaignId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, campaign_id, email, company_name, contact_name, notes, status, error_message, sent_at, created_at, updated_at
             FROM outreach_recipients
             WHERE campaign_id = :campaign_id
             ORDER BY id ASC'
        );
        $stmt->execute([':campaign_id' => $campaignId]);

        return array_map([$this, 'hydrateRecipient'], $stmt->fetchAll());
    }

    /** @return list<array<string,mixed>> */
    public function listApprovedRecipients(int $campaignId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, campaign_id, email, company_name, contact_name, notes, status, error_message, sent_at, created_at, updated_at
             FROM outreach_recipients
             WHERE campaign_id = :campaign_id
               AND status = :status
             ORDER BY id ASC'
        );
        $stmt->execute([
            ':campaign_id' => $campaignId,
            ':status' => 'approved',
        ]);

        return array_map([$this, 'hydrateRecipient'], $stmt->fetchAll());
    }

    public function markRecipientSent(int $recipientId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE outreach_recipients
             SET status = :status,
                 error_message = NULL,
                 sent_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $recipientId,
            ':status' => 'sent',
        ]);
    }

    public function markRecipientFailed(int $recipientId, ?string $errorMessage): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE outreach_recipients
             SET status = :status,
                 error_message = :error_message,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $recipientId,
            ':status' => 'failed',
            ':error_message' => $errorMessage,
        ]);
    }

    /** @return list<array<string,mixed>> */
    public function listEvents(int $campaignId, int $limit = 25): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id, e.campaign_id, e.user_id, e.event_type, e.summary, e.details_json, e.created_at,
                    u.display_name AS user_display_name
             FROM outreach_campaign_events e
             LEFT JOIN users u ON u.id = e.user_id
             WHERE e.campaign_id = :campaign_id
             ORDER BY e.id DESC
             LIMIT ' . max(1, min(100, $limit))
        );
        $stmt->execute([':campaign_id' => $campaignId]);

        return array_map(function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'campaign_id' => (int) ($row['campaign_id'] ?? 0),
                'user_id' => (int) ($row['user_id'] ?? 0),
                'user_display_name' => (string) ($row['user_display_name'] ?? ''),
                'event_type' => (string) ($row['event_type'] ?? ''),
                'summary' => (string) ($row['summary'] ?? ''),
                'details' => $this->decodeDetails((string) ($row['details_json'] ?? '')),
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];
        }, $stmt->fetchAll());
    }

    /** @param list<string> $emails @return array<string,array<string,mixed>> */
    public function findPreviouslySentRecipientUsage(array $emails, ?int $excludeCampaignId = null): array
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (string $email): string => strtolower(trim($email)),
            $emails
        ), static fn (string $email): bool => $email !== '')));

        if ($normalized === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($normalized as $index => $email) {
            $key = ':email_' . $index;
            $placeholders[] = $key;
            $params[$key] = $email;
        }

        $sql = 'SELECT LOWER(r.email) AS email_key, r.email, c.id AS campaign_id, c.title, r.sent_at
                FROM outreach_recipients r
                INNER JOIN outreach_campaigns c ON c.id = r.campaign_id
                WHERE r.status = "sent"
                  AND LOWER(r.email) IN (' . implode(', ', $placeholders) . ')';

        if ($excludeCampaignId !== null && $excludeCampaignId > 0) {
            $sql .= ' AND c.id <> :exclude_campaign_id';
            $params[':exclude_campaign_id'] = $excludeCampaignId;
        }

        $sql .= ' ORDER BY r.sent_at DESC, r.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $key = (string) ($row['email_key'] ?? '');
            if ($key === '' || isset($result[$key])) {
                continue;
            }

            $result[$key] = [
                'email' => (string) ($row['email'] ?? ''),
                'campaign_id' => (int) ($row['campaign_id'] ?? 0),
                'title' => (string) ($row['title'] ?? ''),
                'sent_at' => (string) ($row['sent_at'] ?? ''),
            ];
        }

        return $result;
    }

    public function countDraftCampaigns(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM outreach_campaigns WHERE status = 'draft' AND archived_at IS NULL");
        return (int) $stmt->fetchColumn();
    }

    public function countApprovedCampaigns(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM outreach_campaigns WHERE status = 'approved' AND archived_at IS NULL");
        return (int) $stmt->fetchColumn();
    }

    public function countSentCampaigns(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM outreach_campaigns WHERE status = 'sent' AND archived_at IS NULL");
        return (int) $stmt->fetchColumn();
    }

    public function countFailedCampaigns(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM outreach_campaigns WHERE status IN ('partial', 'failed') AND archived_at IS NULL");
        return (int) $stmt->fetchColumn();
    }

    public function countArchivedCampaigns(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM outreach_campaigns WHERE archived_at IS NOT NULL");
        return (int) $stmt->fetchColumn();
    }

    /** @param array<string,mixed> $details */
    private function recordCampaignEvent(int $campaignId, ?int $userId, string $eventType, string $summary, array $details = []): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO outreach_campaign_events (campaign_id, user_id, event_type, summary, details_json, created_at)
             VALUES (:campaign_id, :user_id, :event_type, :summary, :details_json, NOW())'
        );
        $stmt->execute([
            ':campaign_id' => $campaignId,
            ':user_id' => $userId !== null && $userId > 0 ? $userId : null,
            ':event_type' => $eventType,
            ':summary' => $summary,
            ':details_json' => $details !== [] ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    }

    /** @return array<string,mixed> */
    private function decodeDetails(string $json): array
    {
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function hydrateCampaign(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'user_id' => (int) ($row['user_id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'subject' => (string) ($row['subject'] ?? ''),
            'body' => (string) ($row['body'] ?? ''),
            'from_email' => (string) ($row['from_email'] ?? ''),
            'from_name' => (string) ($row['from_name'] ?? ''),
            'status' => (string) ($row['status'] ?? 'draft'),
            'allow_known_resend' => !empty($row['allow_known_resend']),
            'approved_at' => (string) ($row['approved_at'] ?? ''),
            'approved_by_user_id' => (int) ($row['approved_by_user_id'] ?? 0),
            'approved_by_display_name' => (string) ($row['approved_by_display_name'] ?? ''),
            'sent_at' => (string) ($row['sent_at'] ?? ''),
            'last_sent_by_user_id' => (int) ($row['last_sent_by_user_id'] ?? 0),
            'last_sent_by_display_name' => (string) ($row['last_sent_by_display_name'] ?? ''),
            'archived_at' => (string) ($row['archived_at'] ?? ''),
            'archived_by_user_id' => (int) ($row['archived_by_user_id'] ?? 0),
            'archived_by_display_name' => (string) ($row['archived_by_display_name'] ?? ''),
            'send_attempt_count' => (int) ($row['send_attempt_count'] ?? 0),
            'last_send_started_at' => (string) ($row['last_send_started_at'] ?? ''),
            'last_send_finished_at' => (string) ($row['last_send_finished_at'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'user_display_name' => (string) ($row['user_display_name'] ?? ''),
            'recipient_count' => (int) ($row['recipient_count'] ?? 0),
            'approved_recipient_count' => (int) ($row['approved_recipient_count'] ?? 0),
            'sent_recipient_count' => (int) ($row['sent_recipient_count'] ?? 0),
            'failed_recipient_count' => (int) ($row['failed_recipient_count'] ?? 0),
        ];
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private function hydrateRecipient(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'campaign_id' => (int) ($row['campaign_id'] ?? 0),
            'email' => (string) ($row['email'] ?? ''),
            'company_name' => (string) ($row['company_name'] ?? ''),
            'contact_name' => (string) ($row['contact_name'] ?? ''),
            'notes' => (string) ($row['notes'] ?? ''),
            'status' => (string) ($row['status'] ?? 'draft'),
            'error_message' => (string) ($row['error_message'] ?? ''),
            'sent_at' => (string) ($row['sent_at'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }
}
