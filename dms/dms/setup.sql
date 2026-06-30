-- DMS Datenbank-Setup
-- Ausführen über phpMyAdmin oder MySQL-CLI

CREATE TABLE IF NOT EXISTS `dms_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(7) DEFAULT '#4f46e5',
    `icon` VARCHAR(50) DEFAULT 'folder',
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dms_documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `receipt_date` DATE DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `pdf_filename` VARCHAR(255) NOT NULL,
    `pdf_size` BIGINT UNSIGNED DEFAULT 0,
    `page_count` TINYINT UNSIGNED DEFAULT 1,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `dms_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dms_document_pages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT UNSIGNED NOT NULL,
    `page_number` TINYINT UNSIGNED NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`document_id`) REFERENCES `dms_documents`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dms_categories` (`name`, `color`, `icon`, `sort_order`) VALUES
    ('Rechnungen', '#ef4444', 'receipt', 1),
    ('Verträge', '#3b82f6', 'description', 2),
    ('Belege', '#10b981', 'receipt_long', 3),
    ('Persönliche Dokumente', '#f59e0b', 'person', 4),
    ('Sonstiges', '#6b7280', 'folder', 5);
