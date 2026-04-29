<?php

declare(strict_types=1);

use App\Bootstrap\AppBootstrap;
use App\Database\Connection;
use App\Http\ContactController;
use App\Http\ErrorHandler;
use App\Http\Routing\RouteCatalog;
use App\Http\Request;
use App\Repository\ContactRepository;
use App\Security\Csrf;
use App\View\HomepageContent;

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

$projectRoot = dirname(__DIR__);
AppBootstrap::init($projectRoot);

$requestId = bin2hex(random_bytes(8));
header('X-Request-Id: ' . $requestId);
set_exception_handler(static function (Throwable $exception) use ($requestId): void {
    ErrorHandler::handle($exception, $requestId);
    exit;
});

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');
$path = $path === '' ? '/' : $path;
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (Request::method() === 'GET' && $path === '/sitemap.xml') {
    $baseUrl = sprintf('%s://%s', $scheme, $host);
    $urls = ['/', '/leistungen', '/referenzen', '/kontakt'];
    $lastMod = gmdate('c');

    header('Content-Type: application/xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $urlPath) {
        $loc = htmlspecialchars($baseUrl . $urlPath, ENT_QUOTES, 'UTF-8');
        echo '<url><loc>' . $loc . '</loc><lastmod>' . $lastMod . '</lastmod></url>';
    }
    echo '</urlset>';
    exit;
}

if (Request::method() === 'POST' && ($_POST['_action'] ?? '') === 'contact.submit') {
    ContactController::guardSubmitRequest();

    try {
        $databaseConfig = require $projectRoot . '/config/database.php';
        $pdo = Connection::get($databaseConfig);
        $contactRepository = new ContactRepository($pdo);
        $contactController = new ContactController($contactRepository);
        $contactController->submit();
    } catch (Throwable) {
        $_SESSION['flash_error'] = 'Kontaktanfrage konnte temporär nicht gespeichert werden. Bitte später erneut versuchen.';
        $_SESSION['old'] = [
            'name' => Request::post('name'),
            'company' => Request::post('company'),
            'email' => Request::post('email'),
            'phone' => Request::post('phone'),
            'message' => Request::post('message'),
        ];
        header('Location: /kontakt', true, 302);
        exit;
    }
}

$routes = RouteCatalog::pages();
$isNotFound = !isset($routes[$path]);
if ($isNotFound) {
    http_response_code(404);
    $page = [
        'title' => 'Seite nicht gefunden',
        'description' => 'Die angeforderte Seite konnte nicht gefunden werden.',
        'headline' => 'Diese Seite wurde nicht gefunden',
        'intro' => 'Der Link ist möglicherweise veraltet oder die Adresse wurde falsch eingegeben. Nutzen Sie die Navigation oder starten Sie über die Startseite neu.',
    ];
} else {
    $page = $routes[$path];
}

$flashError = $_SESSION['flash_error'] ?? null;
$flashErrors = $_SESSION['flash_errors'] ?? [];
$flashSuccess = $_SESSION['flash_success'] ?? null;
$old = $_SESSION['old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['flash_errors'], $_SESSION['flash_success']);

$services = HomepageContent::services();
$references = HomepageContent::references();
$contactHighlights = HomepageContent::contactHighlights();
$bodyClass = 'page-' . ($path === '/' ? 'home' : trim(str_replace('/', '-', $path), '-'));
$canonicalUrl = sprintf('%s://%s%s', $scheme, $host, $path);
$siteName = 'RD Formstack Solutions';
$metaTitle = $siteName . ' | ' . $page['title'];
$metaRobots = $isNotFound || in_array($path, ['/login', '/dms'], true)
    ? 'noindex,follow,max-image-preview:large'
    : 'index,follow,max-image-preview:large';

$structuredData = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => sprintf('%s://%s/', $scheme, $host),
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => sprintf('%s://%s/', $scheme, $host),
        'inLanguage' => 'de-DE',
    ],
];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function navLink(string $href, string $label, string $currentPath): string
{
    $active = $href === $currentPath ? ' is-active' : '';
    $ariaCurrent = $href === $currentPath ? ' aria-current="page"' : '';
    return '<a class="nav-link' . $active . '" href="' . e($href) . '"' . $ariaCurrent . '>' . e($label) . '</a>';
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($metaTitle) ?></title>
    <meta name="description" content="<?= e($page['description']) ?>">
    <meta name="robots" content="<?= e($metaRobots) ?>">
    <meta name="theme-color" content="#10233d">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="de_DE">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($metaTitle) ?>">
    <meta property="og:description" content="<?= e($page['description']) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <script type="application/ld+json"><?=
        (string) json_encode(
            $structuredData,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        )
    ?></script>
    <link rel="stylesheet" href="/assets/css/site.css">
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Zum Inhalt springen</a>

<header class="site-header" id="top">
    <div class="shell header-inner">
        <a class="brand" href="/">RD Formstack Solutions</a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Navigation öffnen" aria-controls="main-nav" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
            <?= navLink('/', 'Start', $path) ?>
            <?= navLink('/leistungen', 'Leistungen', $path) ?>
            <?= navLink('/referenzen', 'Referenzen', $path) ?>
            <?= navLink('/kontakt', 'Kontakt', $path) ?>
            <?= navLink('/login', 'Login', $path) ?>
            <?= navLink('/dms', 'DMS', $path) ?>
            <a class="btn btn-accent btn-sm" href="/kontakt">Projektanfrage</a>
        </nav>
    </div>
</header>

<main id="main">
    <?php if ($path === '/'): ?>
        <section class="hero section">
            <div class="shell hero-grid">
                <div>
                    <p class="eyebrow">Digital Solutions für Prozessarbeit</p>
                    <h1>Webplattformen, die Abläufe vereinfachen und Teams entlasten</h1>
                    <p class="lead">RD Formstack Solutions verbindet Webentwicklung, Workflow-Logik und Dokumentenprozesse zu einer stabilen Lösung für Ihr Tagesgeschäft.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="/kontakt">Kostenloses Erstgespräch</a>
                        <a class="btn btn-ghost" href="#leistungen">Leistungsbereiche ansehen</a>
                    </div>
                </div>
                <aside class="hero-panel" aria-label="Projektfokus">
                    <h2>Womit wir starten</h2>
                    <ul>
                        <li>Konkreter Use-Case statt generischer Lastenliste</li>
                        <li>Schrittweiser Aufbau mit testbaren Inkrementen</li>
                        <li>Frühe Sichtbarkeit für Fachbereich und Technik</li>
                    </ul>
                </aside>
            </div>
        </section>

        <section class="section" id="leistungen">
            <div class="shell">
                <p class="eyebrow">Leistungsbereiche</p>
                <h2>Von Idee bis produktivem Workflow</h2>
                <div class="cards-grid">
                    <?php foreach ($services as $service): ?>
                        <article class="service-card">
                            <h3><?= e($service['title']) ?></h3>
                            <p><?= e($service['description']) ?></p>
                            <ul>
                                <?php foreach ($service['highlights'] as $highlight): ?>
                                    <li><?= e($highlight) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section section-muted" id="referenzen">
            <div class="shell">
                <p class="eyebrow">Projektbilder</p>
                <h2>Typische Szenarien aus der Praxis</h2>
                <div class="cards-grid ref-grid">
                    <?php foreach ($references as $reference): ?>
                        <article class="ref-card">
                            <p class="tag"><?= e($reference['industry']) ?></p>
                            <h3><?= e($reference['title']) ?></h3>
                            <p><?= e($reference['description']) ?></p>
                            <p><strong>Ergebnis:</strong> <?= e($reference['outcome']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="section page-hero">
            <div class="shell">
                <p class="eyebrow">RD Formstack Solutions</p>
                <h1><?= e($page['headline']) ?></h1>
                <p class="lead"><?= e($page['intro']) ?></p>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/kontakt'): ?>
        <section class="section contact-cta" id="kontakt">
            <div class="shell contact-layout">
                <div>
                    <p class="eyebrow">Kontakt</p>
                    <h2>Vorhaben in 30 Minuten strukturieren</h2>
                    <p>Beschreiben Sie den aktuellen Engpass. Sie erhalten eine realistische Empfehlung für die nächsten Schritte.</p>
                    <ul class="contact-points">
                        <li>Antwort in der Regel innerhalb eines Werktags</li>
                        <li>Klare Einschätzung zu Aufwand und Prioritäten</li>
                        <li>Unverbindlich und ohne Vertragsbindung</li>
                    </ul>

                    <?php if (is_string($flashError)): ?>
                        <div class="alert alert-error" role="alert">
                            <p><?= e($flashError) ?></p>
                            <?php if (is_array($flashErrors) && $flashErrors !== []): ?>
                                <ul class="alert-list">
                                    <?php foreach ($flashErrors as $errorItem): ?>
                                        <?php if (is_string($errorItem)): ?>
                                            <li><?= e($errorItem) ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_string($flashSuccess)): ?>
                        <div class="alert alert-success" role="status"><?= e($flashSuccess) ?></div>
                    <?php endif; ?>
                </div>

                <form method="post" action="/kontakt" id="contact-form" class="form-card" novalidate>
                    <input type="hidden" name="_action" value="contact.submit">
                    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                    <p id="required-note" class="required-note">Felder mit <span class="req" aria-hidden="true">*</span> sind Pflichtfelder.</p>

                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Name <span class="req" aria-hidden="true">*</span></label>
                            <input id="name" name="name" required aria-describedby="required-note" value="<?= e((string) ($old['name'] ?? '')) ?>" autocomplete="name">
                        </div>

                        <div class="field">
                            <label for="company">Unternehmen (optional)</label>
                            <input id="company" name="company" value="<?= e((string) ($old['company'] ?? '')) ?>" autocomplete="organization">
                        </div>

                        <div class="field">
                            <label for="email">E-Mail <span class="req" aria-hidden="true">*</span></label>
                            <input id="email" name="email" type="email" required aria-describedby="required-note" value="<?= e((string) ($old['email'] ?? '')) ?>" autocomplete="email">
                        </div>

                        <div class="field">
                            <label for="phone">Telefon (optional)</label>
                            <input id="phone" name="phone" type="tel" value="<?= e((string) ($old['phone'] ?? '')) ?>" autocomplete="tel">
                        </div>
                    </div>

                    <label for="message">Nachricht <span class="req" aria-hidden="true">*</span></label>
                    <textarea id="message" name="message" rows="5" maxlength="6000" required aria-describedby="required-note message-counter"><?= e((string) ($old['message'] ?? '')) ?></textarea>
                    <p id="message-counter" class="char-counter" aria-live="polite">0 / 6000 Zeichen</p>

                    <button class="btn btn-primary" type="submit">Projektanfrage senden</button>
                </form>

                <aside class="contact-sidecard" aria-label="Kontaktinformationen">
                    <h3>Rahmen für den Ersttermin</h3>
                    <ul>
                        <?php foreach ($contactHighlights as $highlight): ?>
                            <li><strong><?= e($highlight['label']) ?>:</strong> <?= e($highlight['value']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </aside>
            </div>
        </section>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="shell footer-inner">
        <p>© <?= date('Y') ?> RD Formstack Solutions</p>
        <nav class="footer-links" aria-label="Footer Navigation">
            <?= navLink('/', 'Startseite', $path) ?>
            <?= navLink('/leistungen', 'Leistungen', $path) ?>
            <?= navLink('/referenzen', 'Referenzen', $path) ?>
            <?= navLink('/kontakt', 'Kontakt', $path) ?>
        </nav>
    </div>
</footer>

<script src="/assets/js/site.js" defer></script>
</body>
</html>
