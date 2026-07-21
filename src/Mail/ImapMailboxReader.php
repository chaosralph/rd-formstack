<?php

declare(strict_types=1);

namespace App\Mail;

use App\Config\Env;
use RuntimeException;
use Webklex\PHPIMAP\ClientManager;

final class ImapMailboxReader
{
    /** @return array{ok:bool, mailbox?:string, messages?:list<array<string,mixed>>, error?:string} */
    public function fetchRecentMessages(): array
    {
        $host = trim((string) Env::get('IMAP_HOST', ''));
        $port = (int) Env::get('IMAP_PORT', '993');
        $encryption = strtolower(trim((string) Env::get('IMAP_ENCRYPTION', 'ssl')));
        $validateCert = strtolower((string) Env::get('IMAP_VALIDATE_CERT', 'true')) === 'true';
        $username = trim((string) Env::get('IMAP_USERNAME', ''));
        $password = (string) Env::get('IMAP_PASSWORD', '');
        $mailboxName = trim((string) Env::get('IMAP_MAILBOX', 'INBOX'));
        $limit = max(1, (int) Env::get('IMAP_SYNC_LIMIT', '25'));

        if ($host === '' || $username === '' || $password === '') {
            return ['ok' => false, 'error' => 'IMAP-Konfiguration ist unvollständig.'];
        }

        if (!class_exists(ClientManager::class)) {
            return ['ok' => false, 'error' => 'Webklex/PHP-IMAP ist nicht installiert.'];
        }

        try {
            $clientManager = new ClientManager();
            $client = $clientManager->make([
                'host' => $host,
                'port' => $port,
                'encryption' => $encryption === '' ? null : $encryption,
                'validate_cert' => $validateCert,
                'username' => $username,
                'password' => $password,
                'protocol' => 'imap',
            ]);
            $client->connect();

            $folder = $client->getFolderByPath($mailboxName) ?? $client->getFolder($mailboxName);
            if ($folder === null) {
                return ['ok' => false, 'error' => sprintf('IMAP-Ordner "%s" nicht gefunden.', $mailboxName)];
            }

            $messages = $folder->messages()->all()->limit($limit)->get();
            $normalized = [];

            foreach ($messages as $message) {
                $from = $this->extractFrom($message->getFrom());
                $subject = trim((string) ($message->getSubject() ?? ''));
                $body = $this->extractBody($message->getTextBody(), $message->getHTMLBody());
                $receivedAt = date('Y-m-d H:i:s');
                $uid = (string) ($message->uid ?? '');

                $normalized[] = [
                    'uid' => $uid,
                    'message_id' => '',
                    'from_email' => $from['email'],
                    'from_name' => $from['name'],
                    'subject' => $subject,
                    'received_at' => $receivedAt,
                    'body' => $body,
                    'preview' => mb_substr(trim($body), 0, 500),
                ];
            }

            return [
                'ok' => true,
                'mailbox' => $mailboxName,
                'messages' => $normalized,
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'error' => $exception->getMessage() ?: 'IMAP-Abfrage fehlgeschlagen.',
            ];
        }
    }

    /** @return array{name:string,email:string} */
    private function extractFrom(mixed $fromList): array
    {
        $name = '';
        $email = '';

        if (is_object($fromList) && method_exists($fromList, 'toArray')) {
            $fromList = $fromList->toArray();
        }

        if (is_array($fromList) && isset($fromList[0]) && is_object($fromList[0])) {
            $from = $fromList[0];
            $name = trim((string) ($from->personal ?? ''));
            $mail = trim((string) ($from->mail ?? ''));
            $host = trim((string) ($from->host ?? ''));
            if ($mail !== '' && str_contains($mail, '@')) {
                $email = $mail;
            } elseif ($mail !== '' && $host !== '') {
                $email = $mail . '@' . $host;
            } elseif ($mail !== '') {
                $email = $mail;
            }
        }

        return [
            'name' => $name,
            'email' => $email,
        ];
    }

    private function extractBody(?string $textBody, ?string $htmlBody): string
    {
        $body = trim((string) ($textBody ?: $htmlBody ?: ''));
        if ($body === '' && $htmlBody !== null) {
            $body = trim(strip_tags($htmlBody));
        }

        return $body;
    }
}
