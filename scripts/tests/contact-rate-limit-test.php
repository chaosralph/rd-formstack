<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/Security/IpRateLimiter.php';

use App\Security\IpRateLimiter;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$tmpFile = sys_get_temp_dir() . '/contact-rate-limit-test-' . bin2hex(random_bytes(8)) . '.json';
$now = 1_700_000_000;
$clock = static function () use (&$now): int {
    return $now;
};

$limiter = new IpRateLimiter($tmpFile, 3, 60, $clock);

$result1 = $limiter->consume('203.0.113.10');
assertTrue($result1['allowed'] === true, 'first request should pass');

$result2 = $limiter->consume('203.0.113.10');
assertTrue($result2['allowed'] === true, 'second request should pass');

$result3 = $limiter->consume('203.0.113.10');
assertTrue($result3['allowed'] === true, 'third request should pass');

$result4 = $limiter->consume('203.0.113.10');
assertTrue($result4['allowed'] === false, 'fourth request should be blocked');
assertTrue($result4['retry_after'] > 0, 'blocked response should include retry-after');

$otherIp = $limiter->consume('198.51.100.7');
assertTrue($otherIp['allowed'] === true, 'different IP should not be blocked');

$now += 61;
$resultAfterWindow = $limiter->consume('203.0.113.10');
assertTrue($resultAfterWindow['allowed'] === true, 'request should pass after window elapsed');

if (is_file($tmpFile)) {
    unlink($tmpFile);
}

fwrite(STDOUT, "OK: contact IP rate-limit behavior verified\n");
