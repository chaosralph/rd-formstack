CREATE TABLE IF NOT EXISTS references_portfolio (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    industry VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    outcome TEXT NOT NULL,
    focus_lines TEXT NOT NULL,
    url VARCHAR(255) NOT NULL,
    link_label VARCHAR(120) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_references_portfolio_visible_sort (is_visible, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO references_portfolio (title, industry, description, outcome, focus_lines, url, link_label, sort_order, is_visible, created_at, updated_at)
SELECT 'TimePro Solutions', 'Digitale Zeiterfassung', 'Webplattform für Zeiterfassung, Schichtplanung und abrechnungsreife Exporte mit Fokus auf mobile Teams und Dienstleistungsunternehmen.', 'Ein klar positioniertes SaaS-Angebot für digitale Arbeitszeitprozesse mit mobiler Nutzung und branchenspezifischem Einsatz.', 'Zeiterfassung\nSchichtplanung\nMobile App', 'https://timepro-solutions.de', 'Zur Website', 10, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM references_portfolio LIMIT 1);

INSERT INTO references_portfolio (title, industry, description, outcome, focus_lines, url, link_label, sort_order, is_visible, created_at, updated_at)
SELECT 'RM CargoTec', 'Transport & Logistik', 'Digitale Präsenz für Transport, Schwerlastlogistik und interne Online-Tools wie Begleitschein- und Prozessmodule.', 'Leistungsdarstellung und digitale Prozessbausteine für Logistikabläufe in einem konsistenten Unternehmensauftritt gebündelt.', 'Logistikprozesse\nOnline-Tools\nSystem-Integration', 'https://rm-cargotec.de', 'Zur Website', 20, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM references_portfolio WHERE title = 'RM CargoTec');

INSERT INTO references_portfolio (title, industry, description, outcome, focus_lines, url, link_label, sort_order, is_visible, created_at, updated_at)
SELECT 'Zusteller', 'Tourenplanung & Zustellung', 'Webanwendung für Touranlage, Import, Geocoding, Routenoptimierung und mobile Zustellung im operativen Tagesgeschäft.', 'Durchgängiger Ablauf von der Stoppliste bis zur mobilen Abarbeitung mit klarem Fokus auf produktive Feldnutzung.', 'Import & Geocoding\nRoutenoptimierung\nMobile Zustellung', 'https://zusteller.rddigital.de', 'Zur Plattform', 30, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM references_portfolio WHERE title = 'Zusteller');
