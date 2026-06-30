-- RD Formstack Solutions - Datenbankschema

CREATE DATABASE IF NOT EXISTS rd_formstack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rd_formstack;

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

-- Standard-Admin-Benutzer erstellen (Passwort: admin123)
INSERT INTO users (email, password_hash, name, role) VALUES
('admin@rd-formstack.de', '$2y$10$VRTB8WDzdVFFy/KCk2yYBeH61mQph2si8t4A7KeP5iInlBzupjxm.', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE email=email;
