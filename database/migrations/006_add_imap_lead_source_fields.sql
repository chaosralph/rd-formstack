ALTER TABLE contacts
    ADD COLUMN source_type VARCHAR(20) NOT NULL DEFAULT 'form' AFTER updated_at,
    ADD COLUMN source_mailbox VARCHAR(190) NULL AFTER source_type,
    ADD COLUMN source_uid VARCHAR(190) NULL AFTER source_mailbox,
    ADD COLUMN source_subject VARCHAR(190) NULL AFTER source_uid,
    ADD COLUMN source_received_at DATETIME NULL AFTER source_subject,
    ADD COLUMN source_meta TEXT NULL AFTER source_received_at,
    ADD INDEX idx_contacts_source_type (source_type),
    ADD INDEX idx_contacts_source_mailbox_uid (source_mailbox, source_uid);
