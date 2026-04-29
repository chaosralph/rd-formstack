<?php

declare(strict_types=1);

namespace App\View;

final class SiteRenderer
{
    /** @param array<string, mixed> $context */
    public static function render(string $template, array $context = []): void
    {
        $templatePath = dirname(__DIR__, 2) . '/templates/' . ltrim($template, '/');

        if (!is_file($templatePath)) {
            throw new \RuntimeException('Template not found: ' . $template);
        }

        extract($context, EXTR_SKIP);
        require $templatePath;
    }
}
