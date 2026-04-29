<?php

declare(strict_types=1);

namespace App\Http;

use App\Application\Contact\SubmitContactService;
use App\Security\Csrf;
use App\Security\IpRateLimiter;
use App\Support\Logger;

final class ContactController
{
    private const RATE_LIMIT_MAX_ATTEMPTS = 5;
    private const RATE_LIMIT_WINDOW_SECONDS = 600;
    private const RATE_LIMIT_STORAGE_PATH = __DIR__ . '/../../storage/rate-limits/contact-submit.json';

    public function __construct(private SubmitContactService $service)
    {
    }

    public function submit(): void
    {
        self::guardSubmitRequest();

        $name = Request::post('name');
        $company = Request::post('company');
        $email = Request::post('email');
        $phone = Request::post('phone');
        $message = Request::post('message');
        $website = Request::post('website');

        // Honeypot: bots tend to fill hidden fields; drop silently.
        if ($website !== '') {
            $_SESSION['flash_success'] = 'Vielen Dank, Ihre Nachricht wurde gespeichert.';
            unset($_SESSION['old']);
            Response::redirect('/kontakt');
        }

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

        if ($errors !== []) {
            $_SESSION['flash_error'] = 'Bitte korrigieren Sie die markierten Eingaben.';
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['old'] = [
                'name' => $name,
                'company' => $company,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
            ];
            Response::redirect('/kontakt');
        }

        $this->service->handle([
            'name' => $name,
            'company' => $company,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
        ]);
        $_SESSION['flash_success'] = 'Vielen Dank, Ihre Nachricht wurde gespeichert.';
        unset($_SESSION['old']);
        Response::redirect('/kontakt');
    }

    public static function guardSubmitRequest(): void
    {
        if (!Csrf::validate(Request::post('_csrf'))) {
            Logger::security('csrf_invalid', 'medium', $_SERVER['HTTP_X_REQUEST_ID'] ?? null, [
                'path' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => Request::method(),
            ]);
            http_response_code(419);
            $_SESSION['flash_error'] = 'Ungültige Anfrage. Bitte erneut versuchen.';
            Response::redirect('/kontakt');
        }

        $limiter = new IpRateLimiter(
            self::RATE_LIMIT_STORAGE_PATH,
            self::RATE_LIMIT_MAX_ATTEMPTS,
            self::RATE_LIMIT_WINDOW_SECONDS
        );
        $rateLimitResult = $limiter->consume(Request::ip());
        if ($rateLimitResult['allowed'] === false) {
            Logger::security('rate_limiter_block', 'medium', $_SERVER['HTTP_X_REQUEST_ID'] ?? null, [
                'path' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => Request::method(),
                'retry_after' => $rateLimitResult['retry_after'],
            ]);
            http_response_code(429);
            header('Retry-After: ' . (string) $rateLimitResult['retry_after']);
            $_SESSION['flash_error'] = 'Zu viele Anfragen von dieser IP. Bitte später erneut versuchen.';
            Response::redirect('/kontakt');
        }
    }
}
