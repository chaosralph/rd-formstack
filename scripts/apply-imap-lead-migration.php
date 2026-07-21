<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config/env.php';

\App\Config\Env::load($root . '/.env');
$config = require $root . '/config/database.php';

$pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
$table = 'contacts';
$column = 'source_type';

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
    echo "IMAP lead migration already applied\n";
    exit(0);
}

$sql = <<<SQL
ALTER TABLE contacts
    ADD COLUMN source_type VARCHAR(20) NOT NULL DEFAULT 'form' AFTER updated_at,
    ADD COLUMN source_mailbox VARCHAR(190) NULL AFTER source_type,
    ADD COLUMN source_uid VARCHAR(190) NULL AFTER source_mailbox,
    ADD COLUMN source_subject VARCHAR(190) NULL AFTER source_uid,
    ADD COLUMN source_received_at DATETIME NULL AFTER source_subject,
    ADD COLUMN source_meta TEXT NULL AFTER source_received_at,
    ADD INDEX idx_contacts_source_type (source_type),
    ADD INDEX idx_contacts_source_mailbox_uid (source_mailbox, source_uid)
SQL;

$pdo->exec($sql);
echo "IMAP lead migration applied\n";
