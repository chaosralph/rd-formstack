<?php

declare(strict_types=1);

namespace App\Mail;

final class NativeMailTransport
{
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
