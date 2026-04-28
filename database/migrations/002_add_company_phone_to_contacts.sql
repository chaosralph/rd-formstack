ALTER TABLE contacts
    ADD COLUMN company VARCHAR(160) NULL AFTER name,
    ADD COLUMN phone VARCHAR(40) NULL AFTER email,
    ADD INDEX idx_contacts_company (company);
