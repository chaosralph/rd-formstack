<?php

declare(strict_types=1);

namespace App\Application\Lead;

use App\Mail\ImapMailboxReader;
use App\Repository\ContactRepository;

final class LeadInboxSyncService
{
    public function __construct(
        private ContactRepository $contacts,
        private ImapMailboxReader $reader,
    ) {
    }

    /**
     * @return array{ok:bool, imported:int, skipped:int, total:int, mailbox?:string, error?:string}
     */
    public function sync(): array
    {
        $result = $this->reader->fetchRecentMessages();
        if (($result['ok'] ?? false) !== true) {
            return [
                'ok' => false,
                'imported' => 0,
                'skipped' => 0,
                'total' => 0,
                'error' => (string) ($result['error'] ?? 'IMAP-Sync fehlgeschlagen.'),
            ];
        }

        $imported = 0;
        $skipped = 0;
        $messages = is_array($result['messages'] ?? null) ? $result['messages'] : [];
        foreach ($messages as $message) {
            if (!is_array($message)) {
                $skipped++;
                continue;
            }

            $email = trim((string) ($message['from_email'] ?? ''));
            $subject = trim((string) ($message['subject'] ?? ''));
            $body = trim((string) ($message['body'] ?? ''));
            $uid = trim((string) ($message['uid'] ?? ''));
            $mailbox = trim((string) ($result['mailbox'] ?? ''));

            if ($email === '' || $uid === '' || $body === '') {
                $skipped++;
                continue;
            }

            $name = trim((string) ($message['from_name'] ?? ''));
            if ($name === '') {
                $name = strstr($email, '@', true) ?: $email;
            }

            $created = $this->contacts->upsertInboundLead([
                'name' => $name,
                'company' => '',
                'email' => $email,
                'phone' => '',
                'message' => $body,
                'source_type' => 'imap',
                'source_mailbox' => $mailbox,
                'source_uid' => $uid,
                'source_subject' => $subject,
                'source_received_at' => (string) ($message['received_at'] ?? date('Y-m-d H:i:s')),
                'source_meta' => json_encode([
                    'message_id' => (string) ($message['message_id'] ?? ''),
                    'preview' => (string) ($message['preview'] ?? ''),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null,
            ]);

            if ($created) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        return [
            'ok' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'total' => count($messages),
            'mailbox' => (string) ($result['mailbox'] ?? ''),
        ];
    }
}
