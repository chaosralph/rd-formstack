<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config/env.php';

\App\Config\Env::load($root . '/.env');
$config = require $root . '/config/database.php';

$pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);

$tableCheck = $pdo->prepare(
    'SELECT COUNT(*)
     FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = :table'
);

$requiredTables = [
    'dms_documents',
    'dms_document_versions',
    'dms_document_events',
];

$allPresent = true;
foreach ($requiredTables as $table) {
    $tableCheck->execute([':table' => $table]);
    if ((int) $tableCheck->fetchColumn() === 0) {
        $allPresent = false;
        break;
    }
}

if ($allPresent) {
    echo "DMS migration already applied\n";
    exit(0);
}

$sql = file_get_contents($root . '/database/migrations/009_create_dms_documents.sql');
if (!is_string($sql) || trim($sql) === '') {
    fwrite(STDERR, "Migration SQL missing\n");
    exit(1);
}

$pdo->exec($sql);
echo "DMS migration applied\n";
