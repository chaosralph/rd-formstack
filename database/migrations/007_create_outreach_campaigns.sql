CREATE TABLE IF NOT EXISTS outreach_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    subject VARCHAR(190) NOT NULL,
    body TEXT NOT NULL,
    from_email VARCHAR(190) NOT NULL,
    from_name VARCHAR(190) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    approved_at DATETIME NULL,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_outreach_campaigns_status_updated (status, updated_at),
    KEY idx_outreach_campaigns_user_created (user_id, created_at),
    CONSTRAINT fk_outreach_campaigns_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS outreach_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(190) NOT NULL,
    company_name VARCHAR(190) NULL,
    contact_name VARCHAR(190) NULL,
    notes TEXT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    error_message TEXT NULL,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_outreach_recipients_campaign_status (campaign_id, status),
    KEY idx_outreach_recipients_email (email),
    CONSTRAINT fk_outreach_recipients_campaign FOREIGN KEY (campaign_id) REFERENCES outreach_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
