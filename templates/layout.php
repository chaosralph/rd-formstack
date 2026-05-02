<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $e($metaTitle) ?></title>
    <meta name="description" content="<?= $e($page['description']) ?>">
    <meta name="robots" content="<?= $e($metaRobots) ?>">
    <meta name="theme-color" content="#10233d">
    <link rel="canonical" href="<?= $e($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="de_DE">
    <meta property="og:site_name" content="<?= $e($siteName) ?>">
    <meta property="og:title" content="<?= $e($metaTitle) ?>">
    <meta property="og:description" content="<?= $e($page['description']) ?>">
    <meta property="og:url" content="<?= $e($canonicalUrl) ?>">
    <script type="application/ld+json"><?=
        (string) json_encode(
            $structuredData,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        )
    ?></script>
    <link rel="stylesheet" href="/assets/css/site.css">
</head>
<body class="<?= $e($bodyClass) ?>">
<a class="skip-link" href="#main">Zum Inhalt springen</a>
<div class="scroll-progress" aria-hidden="true"><span id="scroll-progress-bar"></span></div>

<?php require __DIR__ . '/partials/header.php'; ?>

<?php if ($path !== '/'): ?>
    <div class="page-context">
        <div class="shell page-context-inner">
            <p><a href="/">Startseite</a> <span aria-hidden="true">/</span> <strong><?= $e($page['title']) ?></strong></p>
            <a class="btn btn-ghost btn-context" href="/kontakt">Kurz abstimmen</a>
        </div>
    </div>
<?php endif; ?>

<main id="main">
    <?php if ($path === '/'): ?>
        <?php require __DIR__ . '/pages/home.php'; ?>
    <?php else: ?>
        <?php require __DIR__ . '/pages/page-hero.php'; ?>
        <?php require __DIR__ . '/pages/subpage-content.php'; ?>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/kontakt'): ?>
        <?php require __DIR__ . '/pages/contact-section.php'; ?>
    <?php endif; ?>
</main>

<aside class="conversion-rail" aria-label="Schnellkontakt">
    <p>Projektstart in klaren Etappen</p>
    <a class="btn btn-accent" href="/kontakt">Erstgespräch sichern</a>
</aside>

<a class="floating-cta btn btn-accent" href="<?= $e($mobileActionCta[0]) ?>"><?= $e($mobileActionCta[1]) ?></a>

<?php require __DIR__ . '/partials/footer.php'; ?>

<script src="/assets/js/site.js" defer></script>
</body>
</html>
