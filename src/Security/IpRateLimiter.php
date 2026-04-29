<?php

declare(strict_types=1);

namespace App\Security;

use App\Config\Env;
use App\Support\Logger;

final class IpRateLimiter
{
    /**
     * @param callable|null $clock Returns unix timestamp.
     */
    public function __construct(
        private string $storagePath,
        private int $maxAttempts,
        private int $windowSeconds,
        private $clock = null
    ) {
    }

    /**
     * @return array{allowed: bool, retry_after: int}
     */
    public function consume(string $ip): array
    {
        $now = is_callable($this->clock) ? (int) call_user_func($this->clock) : time();
        $key = hash('sha256', $ip);
        $windowStart = $now - $this->windowSeconds;

        $this->ensureStorageDirectory();
        $handle = fopen($this->storagePath, 'c+');
        if ($handle === false) {
            return $this->degrade('rate_limiter_storage_open_failed');
        }

        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            return $this->degrade('rate_limiter_lock_failed');
        }

        $contents = stream_get_contents($handle);
        $state = $this->decodeState($contents);

        foreach ($state as $stateKey => $timestamps) {
            $filtered = array_values(array_filter(
                is_array($timestamps) ? $timestamps : [],
                static fn ($value): bool => is_int($value) && $value >= $windowStart
            ));
            if ($filtered === []) {
                unset($state[$stateKey]);
                continue;
            }
            $state[$stateKey] = $filtered;
        }

        $current = $state[$key] ?? [];
        if (!is_array($current)) {
            $current = [];
        }

        if (count($current) >= $this->maxAttempts) {
            $oldest = (int) min($current);
            $retryAfter = max(1, ($oldest + $this->windowSeconds) - $now);
            $this->persistState($handle, $state);
            return ['allowed' => false, 'retry_after' => $retryAfter];
        }

        $current[] = $now;
        $state[$key] = $current;
        $this->persistState($handle, $state);
        return ['allowed' => true, 'retry_after' => 0];
    }

    private function ensureStorageDirectory(): void
    {
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    /**
     * @return array<string, array<int, int>>
     */
    private function decodeState(string|false $contents): array
    {
        if (!is_string($contents) || trim($contents) === '') {
            return [];
        }

        $decoded = json_decode($contents, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, array<int, int>> $state
     */
    private function persistState($handle, array $state): void
    {
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($state, JSON_THROW_ON_ERROR));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * @return array{allowed: bool, retry_after: int}
     */
    private function degrade(string $reason): array
    {
        $mode = strtolower((string) Env::get('RATE_LIMIT_FAIL_MODE', 'open'));
        if (!in_array($mode, ['open', 'closed'], true)) {
            $mode = 'open';
        }

        Logger::security('rate_limiter_degrade', 'high', $_SERVER['HTTP_X_REQUEST_ID'] ?? null, [
            'reason' => $reason,
            'mode' => $mode,
        ]);

        if ($mode === 'closed') {
            return ['allowed' => false, 'retry_after' => 60];
        }

        return ['allowed' => true, 'retry_after' => 0];
    }
}
