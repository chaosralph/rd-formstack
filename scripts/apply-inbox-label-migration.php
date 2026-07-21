<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config/env.php';

\App\Config\Env::load($root . '/.env');
$config = require $root . '/config/database.php';

$pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
$table = 'contacts';
$column = 'inbox_label';

$check = $pdo->prepare(
    'SELECT COUNT(*)
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = :table
       AND COLUMN_NAME = :column'
);
$check->execute([':table' => $table, ':column' => $column]);
$hasColumn = (int) $check->fetchColumn() > 0;

if ($hasColumn) {
    echo "Inbox label migration already applied\n";
    exit(0);
}

$sql = <<<SQL
ALTER TABLE contacts
    ADD COLUMN inbox_label VARCHAR(80) NULL AFTER source_meta,
    ADD INDEX idx_contacts_source_type_inbox_label (source_type, inbox_label)
SQL;

$pdo->exec($sql);
echo "Inbox label migration applied\n";
