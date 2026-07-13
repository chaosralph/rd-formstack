<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Security\AuthSession;

$_SESSION = [];

assertTrue(AuthSession::check() === false, 'session must start unauthenticated');
assertSame(null, AuthSession::user(), 'session user must be null before login');

AuthSession::login([
    'id' => 42,
    'email' => 'admin@rddigital.de',
    'display_name' => 'Ralph Domin',
    'role' => 'admin',
]);

assertTrue(AuthSession::check() === true, 'login must mark session as authenticated');
assertSame(42, AuthSession::user()['id'] ?? null, 'session must expose authenticated user id');
assertSame('admin@rddigital.de', AuthSession::user()['email'] ?? null, 'session must expose authenticated user email');
assertSame('admin', AuthSession::user()['role'] ?? null, 'session must expose authenticated user role');

AuthSession::logout();

assertTrue(AuthSession::check() === false, 'logout must clear authentication state');
assertSame(null, AuthSession::user(), 'logout must clear authenticated user');

fwrite(STDOUT, "OK: auth session behavior verified\n");
