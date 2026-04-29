<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Contact\ContactSubmissionService;
use App\Http\Request;
use App\Http\Response;
use App\Security\Csrf;
use App\Security\IpRateLimiter;
use App\Support\SecurityEventLogger;

final class ContactController
{
    private const RATE_LIMIT_MAX_ATTEMPTS = 5;
    private const RATE_LIMIT_WINDOW_SECONDS = 600;
    private const RATE_LIMIT_STORAGE_PATH = __DIR__ . '/../../storage/rate-limits/contact-submit.json';

    public function __construct(private ContactSubmissionService $service)
    {
    }

    public function submit(): void
    {
        self::guardSubmitRequest();

        $input = [
            'name' => Request::post('name'),
            'company' => Request::post('company'),
            'email' => Request::post('email'),
            'phone' => Request::post('phone'),
            'message' => Request::post('message'),
            'website' => Request::post('website'),
        ];

        if ($input['website'] !== '') {
            SecurityEventLogger::info('contact_honeypot_triggered', [
                'event_category' => 'contact',
                'request_ip_hash' => hash('sha256', Request::ip()),
            ]);
            $_SESSION['flash_success'] = 'Vielen Dank, Ihre Nachricht wurde gespeichert.';
            unset($_SESSION['old']);
            Response::redirect('/kontakt');
        }

        $result = $this->service->submit($input);
        if ($result['ok'] === false) {
            if (count($result['errors']) >= 3) {
                SecurityEventLogger::high('contact_validation_anomaly', [
                    'event_category' => 'security',
                    'error_count' => count($result['errors']),
                    'request_ip_hash' => hash('sha256', Request::ip()),
                    'path' => $_SERVER['REQUEST_URI'] ?? '',
                ]);
            }
            $_SESSION['flash_error'] = 'Bitte korrigieren Sie die markierten Eingaben.';
            $_SESSION['flash_errors'] = $result['errors'];
            $_SESSION['old'] = [
                'name' => $input['name'],
                'company' => $input['company'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'message' => $input['message'],
            ];
            Response::redirect('/kontakt');
        }

        $_SESSION['flash_success'] = 'Vielen Dank, Ihre Nachricht wurde gespeichert.';
        unset($_SESSION['old']);
        Response::redirect('/kontakt');
    }

    public static function guardSubmitRequest(): void
    {
        if (!Csrf::validate(Request::post('_csrf'))) {
            SecurityEventLogger::warning('csrf_validation_failed', [
                'event_category' => 'security',
                'request_ip_hash' => hash('sha256', Request::ip()),
            ]);
            http_response_code(419);
            $_SESSION['flash_error'] = 'Ungültige Anfrage. Bitte erneut versuchen.';
            Response::redirect('/kontakt');
        }

        $failMode = strtolower((string) getenv('RATE_LIMIT_FAIL_MODE'));
        if ($failMode !== 'closed') {
            $failMode = 'open';
        }

        $limiterReady = self::isLimiterStorageReady();
        if ($limiterReady === false) {
            SecurityEventLogger::warning('rate_limiter_storage_unavailable', [
                'event_category' => 'security',
                'rate_limit_fail_mode' => $failMode,
            ]);
            if ($failMode === 'closed') {
                http_response_code(503);
                $_SESSION['flash_error'] = 'Anfrage kann aktuell nicht verarbeitet werden. Bitte später erneut versuchen.';
                Response::redirect('/kontakt');
            }
            return;
        }

        $limiter = new IpRateLimiter(
            self::RATE_LIMIT_STORAGE_PATH,
            self::RATE_LIMIT_MAX_ATTEMPTS,
            self::RATE_LIMIT_WINDOW_SECONDS
        );
        $rateLimitResult = $limiter->consume(Request::ip());
        if ($rateLimitResult['allowed'] === false) {
            SecurityEventLogger::warning('rate_limit_exceeded', [
                'event_category' => 'security',
                'retry_after' => (int) $rateLimitResult['retry_after'],
                'request_ip_hash' => hash('sha256', Request::ip()),
            ]);
            http_response_code(429);
            header('Retry-After: ' . (string) $rateLimitResult['retry_after']);
            $_SESSION['flash_error'] = 'Zu viele Anfragen von dieser IP. Bitte später erneut versuchen.';
            Response::redirect('/kontakt');
        }
    }

    private static function isLimiterStorageReady(): bool
    {
        $dir = dirname(self::RATE_LIMIT_STORAGE_PATH);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        if (is_file(self::RATE_LIMIT_STORAGE_PATH)) {
            return is_writable(self::RATE_LIMIT_STORAGE_PATH);
        }

        return is_writable($dir);
    }
}
