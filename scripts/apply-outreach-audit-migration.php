<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config/env.php';

\App\Config\Env::load($root . '/.env');
$config = require $root . '/config/database.php';

$pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);

$columnCheck = $pdo->prepare(
    'SELECT COUNT(*)
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = :table
       AND COLUMN_NAME = :column'
);

$columnCheck->execute([
    ':table' => 'outreach_campaigns',
    ':column' => 'allow_known_resend',
]);
$hasColumns = (int) $columnCheck->fetchColumn() > 0;

$tableCheck = $pdo->prepare(
    'SELECT COUNT(*)
     FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = :table'
);
$tableCheck->execute([':table' => 'outreach_campaign_events']);
$hasEventsTable = (int) $tableCheck->fetchColumn() > 0;

if ($hasColumns && $hasEventsTable) {
    $backfill = $pdo->exec(
        "INSERT INTO outreach_campaign_events (campaign_id, user_id, event_type, summary, details_json, created_at)
         SELECT c.id,
                c.user_id,
                'campaign_backfilled',
                CONCAT('Bestandskampagne übernommen (Status: ', c.status, ')'),
                JSON_OBJECT(
                    'status', c.status,
                    'recipient_count', (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id),
                    'sent_count', (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = 'sent'),
                    'failed_count', (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = 'failed')
                ),
                COALESCE(c.sent_at, c.approved_at, c.updated_at, c.created_at)
         FROM outreach_campaigns c
         WHERE NOT EXISTS (
             SELECT 1 FROM outreach_campaign_events e WHERE e.campaign_id = c.id
         )"
    );

    echo "Outreach audit migration already applied" . ($backfill > 0 ? " (backfilled {$backfill} campaigns)" : '') . "\n";
    exit(0);
}

$sql = file_get_contents($root . '/database/migrations/008_outreach_audit_and_management.sql');
if (!is_string($sql) || trim($sql) === '') {
    fwrite(STDERR, "Migration SQL missing\n");
    exit(1);
}

$pdo->exec($sql);
$backfill = $pdo->exec(
    "INSERT INTO outreach_campaign_events (campaign_id, user_id, event_type, summary, details_json, created_at)
     SELECT c.id,
            c.user_id,
            'campaign_backfilled',
            CONCAT('Bestandskampagne übernommen (Status: ', c.status, ')'),
            JSON_OBJECT(
                'status', c.status,
                'recipient_count', (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id),
                'sent_count', (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = 'sent'),
                'failed_count', (SELECT COUNT(*) FROM outreach_recipients r WHERE r.campaign_id = c.id AND r.status = 'failed')
            ),
            COALESCE(c.sent_at, c.approved_at, c.updated_at, c.created_at)
     FROM outreach_campaigns c
     WHERE NOT EXISTS (
         SELECT 1 FROM outreach_campaign_events e WHERE e.campaign_id = c.id
     )"
);
echo "Outreach audit migration applied" . ($backfill > 0 ? " (backfilled {$backfill} campaigns)" : '') . "\n";
