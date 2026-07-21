CREATE TABLE IF NOT EXISTS dms_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    category VARCHAR(120) NOT NULL,
    summary TEXT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    current_version_id BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    approved_by_user_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_dms_documents_status_updated (status, updated_at),
    KEY idx_dms_documents_category_updated (category, updated_at),
    KEY idx_dms_documents_user_created (user_id, created_at),
    KEY idx_dms_documents_approved_by (approved_by_user_id),
    CONSTRAINT fk_dms_documents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_dms_documents_approved_by FOREIGN KEY (approved_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dms_document_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(190) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    binary_content LONGBLOB NOT NULL,
    change_note TEXT NULL,
    uploaded_by_user_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uniq_dms_document_versions_number (document_id, version_number),
    KEY idx_dms_document_versions_document_created (document_id, created_at),
    KEY idx_dms_document_versions_uploader_created (uploaded_by_user_id, created_at),
    CONSTRAINT fk_dms_document_versions_document FOREIGN KEY (document_id) REFERENCES dms_documents(id) ON DELETE CASCADE,
    CONSTRAINT fk_dms_document_versions_uploader FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE dms_documents
    ADD CONSTRAINT fk_dms_documents_current_version FOREIGN KEY (current_version_id) REFERENCES dms_document_versions(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS dms_document_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(50) NOT NULL,
    summary VARCHAR(255) NOT NULL,
    details_json JSON NULL,
    created_at DATETIME NOT NULL,
    KEY idx_dms_document_events_document_created (document_id, created_at),
    KEY idx_dms_document_events_user_created (user_id, created_at),
    CONSTRAINT fk_dms_document_events_document FOREIGN KEY (document_id) REFERENCES dms_documents(id) ON DELETE CASCADE,
    CONSTRAINT fk_dms_document_events_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
