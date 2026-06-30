-- Settings-Tabelle für zentrale Verwaltung
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
    setting_label VARCHAR(255) NOT NULL DEFAULT '',
    setting_type ENUM('text', 'textarea', 'email', 'url', 'tel', 'password', 'select', 'boolean') DEFAULT 'text',
    setting_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_group (setting_group),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- FIRMENDATEN
-- ===========================
INSERT INTO settings (setting_key, setting_value, setting_group, setting_label, setting_type, setting_order) VALUES
('company_name', 'RD Formstack Solutions', 'company', 'Firmenname', 'text', 1),
('company_owner', 'Ralph Domin', 'company', 'Inhaber', 'text', 2),
('company_legal_form', 'Einzelunternehmen', 'company', 'Rechtsform', 'text', 3),
('company_street', 'Am Wingert 2', 'company', 'Straße + Hausnr.', 'text', 4),
('company_zip', '63579', 'company', 'PLZ', 'text', 5),
('company_city', 'Freigericht', 'company', 'Ort', 'text', 6),
('company_country', 'Deutschland', 'company', 'Land', 'text', 7),
('company_phone', '', 'company', 'Telefon', 'tel', 8),
('company_email', 'info@timepro-solutions.de', 'company', 'E-Mail', 'email', 9),
('company_website', 'https://rd.timepro-solutions.de', 'company', 'Website', 'url', 10),
('company_ust_id', '', 'company', 'USt-IdNr.', 'text', 11),
('company_tax_number', '', 'company', 'Steuernummer', 'text', 12),
('company_register', '', 'company', 'Handelsregister', 'text', 13),
('company_kleinunternehmer', '1', 'company', 'Kleinunternehmerregelung (§19 UStG)', 'boolean', 14)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- ===========================
-- DOMAIN & APP
-- ===========================
INSERT INTO settings (setting_key, setting_value, setting_group, setting_label, setting_type, setting_order) VALUES
('app_name', 'RD Formstack Solutions', 'app', 'App-Name', 'text', 1),
('app_domain', 'https://rd.timepro-solutions.de', 'app', 'Domain (mit https://)', 'url', 2),
('app_description', 'Digitale Belegverwaltung und Buchhaltungs-Lösungen', 'app', 'Beschreibung (SEO)', 'textarea', 3),
('app_keywords', 'Belegverwaltung, Buchhaltung, SKR03, Belegerkennung, OCR', 'app', 'Keywords (SEO)', 'text', 4),
('app_og_image', '', 'app', 'Social-Media Vorschaubild (URL)', 'url', 5)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- ===========================
-- API-KEYS
-- ===========================
INSERT INTO settings (setting_key, setting_value, setting_group, setting_label, setting_type, setting_order) VALUES
('api_openai_key', '', 'api', 'OpenAI API-Key', 'password', 1),
('api_openai_model', 'gpt-4-turbo-preview', 'api', 'OpenAI Modell', 'text', 2),
('api_lexoffice_key', '', 'api', 'Lexoffice API-Key', 'password', 3),
('api_tesseract_path', '', 'api', 'Tesseract Pfad (leer = auto)', 'text', 4)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- ===========================
-- RECHTLICHES
-- ===========================
INSERT INTO settings (setting_key, setting_value, setting_group, setting_label, setting_type, setting_order) VALUES
('legal_dpo_name', '', 'legal', 'Datenschutzbeauftragter (Name)', 'text', 1),
('legal_dpo_email', '', 'legal', 'Datenschutzbeauftragter (E-Mail)', 'email', 2),
('legal_dispute_resolution', 'Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.', 'legal', 'Streitschlichtung (Text)', 'textarea', 3),
('legal_disclaimer_extra', '', 'legal', 'Zusätzlicher Haftungsausschluss', 'textarea', 4)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- ===========================
-- E-MAIL EINSTELLUNGEN
-- ===========================
INSERT INTO settings (setting_key, setting_value, setting_group, setting_label, setting_type, setting_order) VALUES
('mail_smtp_host', '', 'mail', 'SMTP Host', 'text', 1),
('mail_smtp_port', '587', 'mail', 'SMTP Port', 'text', 2),
('mail_smtp_user', '', 'mail', 'SMTP Benutzername', 'text', 3),
('mail_smtp_pass', '', 'mail', 'SMTP Passwort', 'password', 4),
('mail_from_name', 'RD Formstack Solutions', 'mail', 'Absender-Name', 'text', 5),
('mail_from_email', 'info@timepro-solutions.de', 'mail', 'Absender-E-Mail', 'email', 6)
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
