<?php

declare(strict_types=1);

use App\Config\Env;

return [
    'dsn' => sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        Env::get('DB_HOST', '127.0.0.1'),
        Env::get('DB_PORT', '3306'),
        Env::get('DB_NAME', 'rd_formstack'),
        Env::get('DB_CHARSET', 'utf8mb4')
    ),
    'user' => Env::get('DB_USER', ''),
    'pass' => Env::get('DB_PASS', ''),
    'options' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
