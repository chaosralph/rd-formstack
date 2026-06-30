<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle ?? 'Dokumentenmanagement') ?> - RD Formstack Solutions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="dms-header">
        <div class="dms-header-inner">
            <a href="<?= SITE_URL ?>/" class="dms-logo">
                <div class="dms-logo-icon">
                    <span class="material-icons-round">description</span>
                </div>
                <span>DMS</span>
            </a>
            <nav class="dms-nav">
                <a href="<?= SITE_URL ?>/" class="dms-nav-btn <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <span class="material-icons-round" style="font-size:1.125rem">dashboard</span>
                    <span>Dokumente</span>
                </a>
                <a href="<?= SITE_URL ?>/upload.php" class="dms-nav-btn primary">
                    <span class="material-icons-round" style="font-size:1.125rem">add_a_photo</span>
                    <span>Neues Dokument</span>
                </a>
                <a href="<?= SITE_URL ?>/api/export.php?all=1" class="dms-nav-btn" title="Alle exportieren">
                    <span class="material-icons-round" style="font-size:1.125rem">download</span>
                    <span>Export</span>
                </a>
                <a href="<?= MAIN_APP_URL ?>/" class="dms-nav-btn" title="Zurück zur Hauptseite">
                    <span class="material-icons-round" style="font-size:1.125rem">home</span>
                </a>
            </nav>
        </div>
    </header>
    <main class="dms-main">
