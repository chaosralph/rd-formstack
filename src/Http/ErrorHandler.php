<?php

declare(strict_types=1);

namespace App\Http;

use App\Support\Logger;
use Throwable;

final class ErrorHandler
{
    public static function handle(Throwable $exception, string $requestId): void
    {
        Logger::security('unhandled_exception', 'high', $requestId, [
            'type' => $exception::class,
            'path' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        ]);

        Logger::error('Unhandled exception', [
            'request_id' => $requestId,
            'type' => $exception::class,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        echo '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Temporärer Fehler</title></head><body><main><h1>Temporärer Fehler</h1><p>Die Anfrage konnte nicht verarbeitet werden. Bitte später erneut versuchen.</p></main></body></html>';
    }
}
