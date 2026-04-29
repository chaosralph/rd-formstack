<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../src/Support/SecurityHeaderPolicy.php';

use App\Support\SecurityHeaderPolicy;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function applyEnv(array $values): void
{
    foreach ($values as $key => $value) {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

// CSP report-only mode must set Report-Only header and remove enforce header.
applyEnv([
    'CSP_REPORT_ONLY' => 'true',
    'SECURITY_CSP_REPORT_ONLY' => 'true',
    'ENABLE_HSTS' => 'false',
    'SECURITY_HSTS_ENABLED' => 'false',
]);
$headers = SecurityHeaderPolicy::buildHeaders(['HTTPS' => 'off']);
assertTrue(isset($headers['Content-Security-Policy-Report-Only']), 'missing CSP report-only header');
assertTrue(array_key_exists('Content-Security-Policy', $headers) && $headers['Content-Security-Policy'] === null, 'CSP enforce header must be removed in report-only mode');

// CSP enforce mode must set standard CSP header.
applyEnv([
    'CSP_REPORT_ONLY' => 'false',
    'SECURITY_CSP_REPORT_ONLY' => 'false',
]);
$headers = SecurityHeaderPolicy::buildHeaders(['HTTPS' => 'off']);
assertTrue(isset($headers['Content-Security-Policy']), 'missing CSP enforce header');
assertTrue(!isset($headers['Content-Security-Policy-Report-Only']), 'report-only header must not be present in enforce mode');

// HSTS must only be present for HTTPS requests when enabled.
applyEnv([
    'ENABLE_HSTS' => 'true',
    'SECURITY_HSTS_ENABLED' => 'true',
    'SECURITY_HSTS_MAX_AGE' => '31536000',
    'SECURITY_HSTS_INCLUDE_SUBDOMAINS' => 'true',
]);
$headersHttp = SecurityHeaderPolicy::buildHeaders(['HTTPS' => 'off']);
assertTrue(!isset($headersHttp['Strict-Transport-Security']), 'HSTS must not be set for HTTP');

$headersHttps = SecurityHeaderPolicy::buildHeaders(['HTTPS' => 'on']);
assertTrue(isset($headersHttps['Strict-Transport-Security']), 'HSTS must be set for HTTPS');
assertTrue(str_contains((string) $headersHttps['Strict-Transport-Security'], 'max-age=31536000'), 'HSTS max-age mismatch');

fwrite(STDOUT, "OK: header policy regression checks passed\n");
