-- ================================================
-- RD Formstack Solutions - Komplettes Setup
-- Für Webserver-Datenbank: db_450393_6
-- 
-- Dieses Script in phpMyAdmin ausführen!
-- (SQL-Tab → einfügen → Ausführen)
-- ================================================

-- ===========================
-- TABELLEN ERSTELLEN
-- ===========================

-- Benutzer
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Belege
CREATE TABLE IF NOT EXISTS receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    category ENUM('einnahmen', 'ausgaben', 'sonstige') DEFAULT 'sonstige',
    amount DECIMAL(10,2) DEFAULT NULL,
    tax_amount DECIMAL(10,2) DEFAULT NULL,
    tax_rate DECIMAL(5,2) DEFAULT NULL,
    vendor_name VARCHAR(255) DEFAULT NULL,
    invoice_number VARCHAR(100) DEFAULT NULL,
    invoice_date DATE DEFAULT NULL,
    ocr_data TEXT DEFAULT NULL,
    ocr_confidence DECIMAL(5,2) DEFAULT NULL,
    status ENUM('pending', 'processed', 'booked', 'archived') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_invoice_date (invoice_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buchungsvorschläge (SKR 03)
CREATE TABLE IF NOT EXISTS booking_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id INT NOT NULL,
    account_number VARCHAR(10) NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    debit_amount DECIMAL(10,2) DEFAULT NULL,
    credit_amount DECIMAL(10,2) DEFAULT NULL,
    tax_account VARCHAR(10) DEFAULT NULL,
    tax_amount DECIMAL(10,2) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_accepted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (receipt_id) REFERENCES receipts(id) ON DELETE CASCADE,
    INDEX idx_receipt_id (receipt_id),
    INDEX idx_account_number (account_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- ADMIN-BENUTZER
-- (Passwort: admin123)
-- ===========================
INSERT INTO users (email, password_hash, name, role) VALUES
('admin@rd-formstack.de', '$2y$10$VRTB8WDzdVFFy/KCk2yYBeH61mQph2si8t4A7KeP5iInlBzupjxm.', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE email=email;

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

-- ===========================
-- FERTIG!
-- ===========================
-- Tabellen erstellt: users, receipts, booking_suggestions, settings
-- Admin-Login: admin@rd-formstack.de / admin123
-- 30 Einstellungen eingefügt (5 Gruppen)
