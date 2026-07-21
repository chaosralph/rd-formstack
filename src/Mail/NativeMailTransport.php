<?php

declare(strict_types=1);

namespace App\Mail;

use App\Config\Env;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

final class NativeMailTransport
{
    public static function smtpConfigured(): bool
    {
        return trim((string) Env::get('SMTP_HOST', '')) !== '';
    }

    public static function smtpReady(): bool
    {
        return self::smtpConfigured()
            && trim((string) Env::get('SMTP_USERNAME', (string) Env::get('IMAP_USERNAME', ''))) !== ''
            && (string) Env::get('SMTP_PASSWORD', (string) Env::get('IMAP_PASSWORD', '')) !== ''
            && class_exists(PHPMailer::class);
    }

    /** @return array{ok:bool,error?:string} */
    public function send(string $to, string $subject, string $body, string $fromAddress, string $fromName = 'RD Formstack Solutions'): array
    {
        $to = trim($to);
        $subject = trim($subject);
        $body = trim($body);
        $fromAddress = trim($fromAddress);
        $fromName = trim($fromName);

        if ($to === '' || $subject === '' || $body === '' || $fromAddress === '') {
            return ['ok' => false, 'error' => 'Unvollständige Maildaten.'];
        }

        if (self::smtpConfigured()) {
            return $this->sendViaSmtp($to, $subject, $body, $fromAddress, $fromName);
        }

        return $this->sendViaPhpMail($to, $subject, $body, $fromAddress, $fromName);
    }

    /** @return array{ok:bool,error?:string} */
    private function sendViaSmtp(string $to, string $subject, string $body, string $fromAddress, string $fromName): array
    {
        if (!class_exists(PHPMailer::class)) {
            return ['ok' => false, 'error' => 'SMTP-Mailer ist im Runtime-Image nicht verfügbar.'];
        }

        $host = trim((string) Env::get('SMTP_HOST', ''));
        $port = max(1, (int) Env::get('SMTP_PORT', '465'));
        $encryption = strtolower(trim((string) Env::get('SMTP_ENCRYPTION', 'ssl')));
        $username = trim((string) Env::get('SMTP_USERNAME', (string) Env::get('IMAP_USERNAME', '')));
        $password = (string) Env::get('SMTP_PASSWORD', (string) Env::get('IMAP_PASSWORD', ''));

        if ($host === '' || $username === '' || $password === '') {
            return ['ok' => false, 'error' => 'SMTP-Konfiguration ist unvollständig.'];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 20;

            if ($encryption === 'tls' || $encryption === 'starttls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl' || $encryption === 'smtps') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $body;
            $mail->send();

            return ['ok' => true];
        } catch (Exception $exception) {
            return ['ok' => false, 'error' => $exception->getMessage() !== '' ? $exception->getMessage() : 'SMTP-Versand fehlgeschlagen.'];
        }
    }

    /** @return array{ok:bool,error?:string} */
    private function sendViaPhpMail(string $to, string $subject, string $body, string $fromAddress, string $fromName): array
    {
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            sprintf('From: %s <%s>', $encodedFromName, $fromAddress),
            sprintf('Reply-To: %s', $fromAddress),
            sprintf('X-Mailer: PHP/%s', PHP_VERSION),
        ];

        $success = @mail($to, $encodedSubject, $body, implode("\r\n", $headers));

        if ($success !== true) {
            return ['ok' => false, 'error' => 'Mailversand wurde von der Runtime nicht bestätigt.'];
        }

        return ['ok' => true];
    }
}
