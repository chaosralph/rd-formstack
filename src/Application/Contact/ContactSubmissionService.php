<?php

declare(strict_types=1);

namespace App\Application\Contact;

use App\Repository\ContactRepository;
use App\Support\SecurityEventLogger;
use Throwable;

final class ContactSubmissionService
{
    public function __construct(private ContactRepository $repository)
    {
    }

    /**
     * @return array{ok: bool, errors: array<int, string>}
     */
    public function submit(array $input): array
    {
        $name = $this->sanitize($input['name'] ?? '');
        $company = $this->sanitize($input['company'] ?? '');
        $email = $this->sanitize($input['email'] ?? '');
        $phone = $this->sanitize($input['phone'] ?? '');
        $message = $this->sanitize($input['message'] ?? '');

        $errors = $this->validate($name, $company, $email, $phone, $message);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        try {
            $this->repository->create($name, $company, $email, $phone, $message);
        } catch (Throwable $e) {
            SecurityEventLogger::high('contact_submission_persistence_failed', [
                'event_category' => 'security',
                'exception_class' => $e::class,
            ]);

            return ['ok' => false, 'errors' => ['Nachricht konnte aktuell nicht gespeichert werden. Bitte später erneut versuchen.']];
        }

        return ['ok' => true, 'errors' => []];
    }

    /**
     * @return array<int, string>
     */
    private function validate(string $name, string $company, string $email, string $phone, string $message): array
    {
        $errors = [];

        if ($name === '') {
            $errors[] = 'Name ist erforderlich.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Eine gültige E-Mail ist erforderlich.';
        }
        if ($message === '') {
            $errors[] = 'Nachricht ist erforderlich.';
        }
        if (mb_strlen($name) > 120) {
            $errors[] = 'Name darf maximal 120 Zeichen enthalten.';
        }
        if (mb_strlen($company) > 160) {
            $errors[] = 'Unternehmen darf maximal 160 Zeichen enthalten.';
        }
        if (mb_strlen($email) > 190) {
            $errors[] = 'E-Mail darf maximal 190 Zeichen enthalten.';
        }
        if ($phone !== '' && mb_strlen($phone) > 40) {
            $errors[] = 'Telefon darf maximal 40 Zeichen enthalten.';
        }
        if (mb_strlen($message) > 6000) {
            $errors[] = 'Nachricht darf maximal 6000 Zeichen enthalten.';
        }

        return $errors;
    }

    private function sanitize(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
