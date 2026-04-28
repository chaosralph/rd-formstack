<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    public static function redirect(string $path): void
    {
        header('Location: ' . $path, true, 302);
        exit;
    }
}
