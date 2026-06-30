<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/helpers.php';

checkAuth();

$pageTitle = 'Dokumente';
$currentPage = 'dashboard';

$db = Database::getConnection();

$totalDocs = (int)$db->query('SELECT COUNT(*) FROM dms_documents')->fetchColumn();
$totalSize = (int)$db->query('SELECT COALESCE(SUM(pdf_size), 0) FROM dms_documents')->fetchColumn();

$categories = $db->query(
    'SELECT c.*, COUNT(d.id) as document_count
     FROM dms_categories c
     LEFT JOIN dms_documents d ON c.id = d.category_id
     GROUP BY c.id
     ORDER BY c.sort_order, c.name'
)->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/templates/header.php';
?>

<div class="dms-toolbar">
    <div class="dms-toolbar-left">
        <h1 class="dms-page-title">Meine Dokumente</h1>
        <div class="dms-stats">
            <div class="dms-stat">
                <span class="material-icons-round" style="font-size:1rem">description</span>
                <span class="dms-stat-value" id="totalDocs"><?= $totalDocs ?></span> Dokumente
            </div>
            <div class="dms-stat">
                <span class="material-icons-round" style="font-size:1rem">storage</span>
                <span class="dms-stat-value"><?= formatFileSize($totalSize) ?></span> belegt
            </div>
        </div>
    </div>
    <div class="dms-toolbar-right">
        <div class="dms-search">
            <span class="material-icons-round dms-search-icon">search</span>
            <input type="text" id="searchInput" placeholder="Dokumente suchen..." autocomplete="off">
        </div>
        <button class="btn btn-secondary btn-icon" onclick="openCategoryManager()" title="Kategorien verwalten">
            <span class="material-icons-round" style="font-size:1.125rem">tune</span>
        </button>
    </div>
</div>

<div class="dms-categories" id="categoryFilter">
    <button class="dms-category-chip active" data-category="all">
        <span class="material-icons-round" style="font-size:0.875rem">apps</span>
        Alle
        <span class="dms-category-count"><?= $totalDocs ?></span>
    </button>
    <?php foreach ($categories as $cat): ?>
        <button class="dms-category-chip" data-category="<?= $cat['id'] ?>">
            <span class="dms-category-dot" style="background:<?= sanitize($cat['color']) ?>"></span>
            <?= sanitize($cat['name']) ?>
            <span class="dms-category-count"><?= $cat['document_count'] ?></span>
        </button>
    <?php endforeach; ?>
</div>

<div class="dms-grid" id="documentGrid">
    <!-- Dokumente werden per JavaScript geladen -->
</div>

<div class="dms-empty" id="emptyState" style="display:none">
    <div class="dms-empty-icon">
        <span class="material-icons-round" style="font-size:inherit">folder_open</span>
    </div>
    <h3>Keine Dokumente vorhanden</h3>
    <p style="margin-bottom:1rem">Erstelle dein erstes Dokument, indem du ein Foto aufnimmst oder eine Datei hochlädst.</p>
    <a href="<?= SITE_URL ?>/upload.php" class="btn btn-primary btn-lg">
        <span class="material-icons-round">add_a_photo</span>
        Neues Dokument erstellen
    </a>
</div>

<div class="dms-pagination" id="pagination"></div>

<?php require __DIR__ . '/templates/footer.php'; ?>
