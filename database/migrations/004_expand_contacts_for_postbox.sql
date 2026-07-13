ALTER TABLE contacts
    ADD COLUMN status VARCHAR(40) NOT NULL DEFAULT 'new' AFTER message,
    ADD COLUMN admin_note TEXT NULL AFTER status,
    ADD COLUMN replied_at DATETIME NULL AFTER admin_note,
    ADD COLUMN updated_at DATETIME NULL AFTER created_at,
    ADD INDEX idx_contacts_status (status),
    ADD INDEX idx_contacts_replied_at (replied_at);

UPDATE contacts
SET status = 'new', updated_at = created_at
WHERE updated_at IS NULL;

CREATE TABLE IF NOT EXISTS contact_replies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id INT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    recipient_email VARCHAR(190) NOT NULL,
    subject VARCHAR(190) NOT NULL,
    body TEXT NOT NULL,
    sent_success TINYINT(1) NOT NULL DEFAULT 0,
    error_message TEXT NULL,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_contact_replies_contact_created (contact_id, created_at),
    INDEX idx_contact_replies_user_created (user_id, created_at),
    CONSTRAINT fk_contact_replies_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    CONSTRAINT fk_contact_replies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
