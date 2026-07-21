ALTER TABLE outreach_campaigns
    ADD COLUMN allow_known_resend TINYINT(1) NOT NULL DEFAULT 0 AFTER status,
    ADD COLUMN approved_by_user_id BIGINT UNSIGNED NULL AFTER approved_at,
    ADD COLUMN last_sent_by_user_id BIGINT UNSIGNED NULL AFTER sent_at,
    ADD COLUMN archived_at DATETIME NULL AFTER last_sent_by_user_id,
    ADD COLUMN archived_by_user_id BIGINT UNSIGNED NULL AFTER archived_at,
    ADD COLUMN send_attempt_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER archived_by_user_id,
    ADD COLUMN last_send_started_at DATETIME NULL AFTER send_attempt_count,
    ADD COLUMN last_send_finished_at DATETIME NULL AFTER last_send_started_at,
    ADD KEY idx_outreach_campaigns_archived_updated (archived_at, updated_at),
    ADD KEY idx_outreach_campaigns_status_archived (status, archived_at),
    ADD KEY idx_outreach_campaigns_approved_by (approved_by_user_id),
    ADD KEY idx_outreach_campaigns_last_sent_by (last_sent_by_user_id),
    ADD KEY idx_outreach_campaigns_archived_by (archived_by_user_id),
    ADD CONSTRAINT fk_outreach_campaigns_approved_by FOREIGN KEY (approved_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_outreach_campaigns_last_sent_by FOREIGN KEY (last_sent_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_outreach_campaigns_archived_by FOREIGN KEY (archived_by_user_id) REFERENCES users(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS outreach_campaign_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(50) NOT NULL,
    summary VARCHAR(255) NOT NULL,
    details_json JSON NULL,
    created_at DATETIME NOT NULL,
    KEY idx_outreach_campaign_events_campaign_created (campaign_id, created_at),
    KEY idx_outreach_campaign_events_user_created (user_id, created_at),
    CONSTRAINT fk_outreach_campaign_events_campaign FOREIGN KEY (campaign_id) REFERENCES outreach_campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_outreach_campaign_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
