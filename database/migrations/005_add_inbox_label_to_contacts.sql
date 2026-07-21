ALTER TABLE contacts
    ADD COLUMN inbox_label VARCHAR(80) NULL AFTER source_meta,
    ADD INDEX idx_contacts_source_type_inbox_label (source_type, inbox_label);
