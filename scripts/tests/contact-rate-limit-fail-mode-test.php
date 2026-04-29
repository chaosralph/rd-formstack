<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../src/Support/Logger.php';
require_once __DIR__ . '/../../src/Security/IpRateLimiter.php';

use App\Security\IpRateLimiter;

function assertSame(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$logPath = __DIR__ . '/../../storage/logs/app.log';

if (is_file($logPath)) {
    unlink($logPath);
}

putenv('RATE_LIMIT_FAIL_MODE=open');
$openLimiter = new IpRateLimiter('/proc/rd-formstack-rate-limit-open.json', 3, 60, static fn (): int => 1_700_000_000);
$openResult = $openLimiter->consume('203.0.113.15');
assertSame($openResult['allowed'] === true, 'open mode must allow request on storage failure');

putenv('RATE_LIMIT_FAIL_MODE=closed');
$closedLimiter = new IpRateLimiter('/proc/rd-formstack-rate-limit-closed.json', 3, 60, static fn (): int => 1_700_000_000);
$closedResult = $closedLimiter->consume('203.0.113.15');
assertSame($closedResult['allowed'] === false, 'closed mode must deny request on storage failure');
assertSame($closedResult['retry_after'] > 0, 'closed mode must provide retry-after');

assertSame(is_file($logPath), 'security degrade events should be written');
$log = file_get_contents($logPath) ?: '';
assertSame(str_contains($log, '"event_type":"rate_limiter_degrade"'), 'degrade event type missing in security log');

fwrite(STDOUT, "OK: rate-limit fail mode behavior verified\n");
