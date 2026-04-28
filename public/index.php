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

if (Request::method() === 'POST' && ($_POST['_action'] ?? '') === 'contact.submit') {
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

if (!isset($routes[$path])) {
    http_response_code(404);
    $path = '/';
}

$page = $routes[$path];
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
$old = $_SESSION['old'] ?? [];
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$services = HomepageContent::services();
$references = HomepageContent::references();
$processSteps = HomepageContent::processSteps();
$loginFeatures = HomepageContent::loginFeatures();
$dmsRoadmap = HomepageContent::dmsRoadmap();
$contactHighlights = HomepageContent::contactHighlights();
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$canonicalUrl = sprintf('%s://%s%s', $scheme, $host, $path);
$siteName = 'RD Formstack Solutions';
$metaTitle = $siteName . ' | ' . $page['title'];

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
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="theme-color" content="#0c2747">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="de_DE">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($metaTitle) ?>">
    <meta property="og:description" content="<?= e($page['description']) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($metaTitle) ?>">
    <meta name="twitter:description" content="<?= e($page['description']) ?>">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
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
            <a class="btn btn-primary btn-small" href="/kontakt">Projektanfrage</a>
        </nav>
    </div>
</header>

<main id="main">
    <?php if ($path !== '/'): ?>
        <section class="context-bar" aria-label="Seitenkontext">
            <div class="shell context-row">
                <a href="/">Startseite</a>
                <span>/</span>
                <strong><?= e($page['title']) ?></strong>
            </div>
        </section>
    <?php endif; ?>

    <section class="hero section">
        <div class="shell hero-grid <?= $path === '/' ? '' : 'hero-grid-single' ?>">
            <div>
                <p class="eyebrow">RD Formstack Solutions</p>
                <h1><?= e($page['headline']) ?></h1>
                <p class="lead"><?= e($page['intro']) ?></p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="/kontakt">Kostenloses Erstgespräch anfragen</a>
                    <a class="btn btn-secondary" href="/leistungen">Leistungen entdecken</a>
                </div>
                <div class="hero-metrics" aria-label="Wertversprechen">
                    <span>Klare Roadmap statt Technik-Risiko</span>
                    <span>Antwort in 1 Werktag</span>
                    <span>Fokus auf umsetzbare Lösungen</span>
                </div>
            </div>
            <?php if ($path === '/'): ?>
                <aside class="hero-panel" aria-label="Kernvorteile">
                    <h2>Worauf wir uns fokussieren</h2>
                    <ul>
                        <li>Klare UX und saubere Nutzerführung</li>
                        <li>Digitale Prozesse mit messbarem Nutzen</li>
                        <li>Modulare Basis für Skalierung und Betrieb</li>
                    </ul>
                </aside>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($path === '/'): ?>
        <section class="trust-strip" aria-label="Projektfokus">
            <div class="shell trust-grid">
                <div class="trust-item">
                    <strong>Webentwicklung</strong>
                    <span>Strukturierte Informationsarchitektur und klare Frontend-Flows</span>
                </div>
                <div class="trust-item">
                    <strong>Digitale Prozesse</strong>
                    <span>Weniger manuelle Übergaben und transparentere Abläufe</span>
                </div>
                <div class="trust-item">
                    <strong>Beleg &amp; DMS</strong>
                    <span>Saubere Dokumentenstrecken mit hoher Nachvollziehbarkeit</span>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/leistungen'): ?>
        <section class="section" id="leistungen">
            <div class="shell">
                <p class="eyebrow">Leistungsbereiche</p>
                <h2>Von der Konzeption bis zur produktiven Lösung</h2>
                <p class="section-intro">Vier Bausteine für moderne Web- und Dokumentenprozesse mit klarer Verantwortung und guter Bedienbarkeit.</p>
                <div class="card-grid">
                    <?php foreach ($services as $service): ?>
                        <article class="card">
                            <h3><?= e($service['title']) ?></h3>
                            <p><?= e($service['description']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="section-cta-row">
                    <a class="btn btn-primary" href="/kontakt">Leistungen im Erstgespräch klären</a>
                    <a class="btn btn-secondary" href="/referenzen">Passende Referenzen ansehen</a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/referenzen'): ?>
        <section class="section <?= $path === '/referenzen' ? 'section-alt' : '' ?>" id="referenzen">
            <div class="shell">
                <p class="eyebrow">Referenzen</p>
                <h2>Ausgewählte Projektbeispiele</h2>
                <p class="section-intro">Drei typische Kontexte, in denen wir Webentwicklung, Prozesslogik und Dokumentenmanagement wirksam zusammenführen.</p>
                <div class="reference-grid">
                    <?php foreach ($references as $reference): ?>
                        <article class="card reference-card">
                            <h3><?= e($reference['title']) ?></h3>
                            <p><?= e($reference['description']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="section-cta-row">
                    <a class="btn btn-primary" href="/kontakt">Ähnliches Projekt starten</a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/login'): ?>
        <section class="section section-alt" id="login">
            <div class="shell placeholder-wrap">
                <div>
                    <p class="eyebrow">Login</p>
                    <h2>Kundenportal in Vorbereitung</h2>
                    <p>Der geschützte Login-Bereich ist als technischer Platzhalter integriert. Geplant sind rollenbasierte Zugriffe, persönliche Dashboards und sichere Dokumentenansichten.</p>
                    <p class="placeholder-note">Status: Platzhalterseite für die Integrationsphase. Zugriff wird nach technischer Freigabe aktiviert.</p>
                </div>
                <aside class="placeholder-card" aria-label="Login-Platzhalter">
                    <h3>Geplante Funktionen</h3>
                    <ul class="feature-list">
                        <?php foreach ($loginFeatures as $feature): ?>
                            <li><?= e($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a class="text-link" href="/kontakt">Interesse am Pilotzugang melden</a>
                </aside>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/dms'): ?>
        <section class="section" id="dms">
            <div class="shell placeholder-wrap">
                <div>
                    <p class="eyebrow">DMS-Platzhalter</p>
                    <h2>DMS-Bereich wird ausgebaut</h2>
                    <p>Die DMS-Fläche ist als technischer Platzhalter integriert und wird schrittweise mit Dokumentenklassifikation, Revisionshistorie und Freigabeprozessen ergänzt.</p>
                    <p class="placeholder-note">Status: Kernbereiche vorbereitet, fachliche Workflows werden in Iterationen ergänzt.</p>
                </div>
                <aside class="placeholder-card" aria-label="DMS-Platzhalter">
                    <h3>Nächste Ausbaustufen</h3>
                    <ul class="feature-list">
                        <?php foreach ($dmsRoadmap as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a class="text-link" href="/kontakt">DMS-Use-Case besprechen</a>
                </aside>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/'): ?>
        <section class="section section-alt">
            <div class="shell">
                <p class="eyebrow">Vorgehen</p>
                <h2>Strukturiertes Vorgehen mit klaren Meilensteinen</h2>
                <div class="steps-grid">
                    <?php foreach ($processSteps as $index => $step): ?>
                        <article class="step-card">
                            <span><?= $index + 1 ?></span>
                            <h3><?= e($step['title']) ?></h3>
                            <p><?= e($step['description']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/kontakt'): ?>
        <section class="section" id="kontakt">
            <div class="shell contact-layout">
                <div>
                    <p class="eyebrow">Kontakt</p>
                    <h2>Projekt unverbindlich besprechen</h2>
                    <p>Beschreiben Sie kurz Ihr Vorhaben. Wir melden uns mit einer realistischen Empfehlung für den nächsten Schritt.</p>
                    <p class="form-helper">Antwort in der Regel innerhalb eines Werktags.</p>
                    <ul class="contact-points">
                        <li>Klare Einschätzung zu Aufwand und nächstem Schritt</li>
                        <li>Technisch realistische Empfehlung statt Standardangebot</li>
                        <li>Unverbindlich und ohne lange Vorqualifizierung</li>
                    </ul>

                    <?php if (is_string($flashError)): ?>
                        <div class="alert alert-error" role="alert"><?= e($flashError) ?></div>
                    <?php endif; ?>

                    <?php if (is_string($flashSuccess)): ?>
                        <div class="alert alert-success" role="status"><?= e($flashSuccess) ?></div>
                    <?php endif; ?>
                </div>

                <form method="post" action="/kontakt" id="contact-form" class="form-card" novalidate>
                    <input type="hidden" name="_action" value="contact.submit">
                    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">

                    <div class="form-grid">
                        <div class="field">
                            <label for="name">Name <span class="req" aria-hidden="true">*</span></label>
                            <input id="name" name="name" required value="<?= e((string) ($old['name'] ?? '')) ?>" autocomplete="name">
                        </div>

                        <div class="field">
                            <label for="company">Unternehmen (optional)</label>
                            <input id="company" name="company" value="<?= e((string) ($old['company'] ?? '')) ?>" autocomplete="organization">
                        </div>

                        <div class="field">
                            <label for="email">E-Mail <span class="req" aria-hidden="true">*</span></label>
                            <input id="email" name="email" type="email" required value="<?= e((string) ($old['email'] ?? '')) ?>" autocomplete="email">
                        </div>

                        <div class="field">
                            <label for="phone">Telefon (optional)</label>
                            <input id="phone" name="phone" type="tel" value="<?= e((string) ($old['phone'] ?? '')) ?>" autocomplete="tel">
                        </div>
                    </div>

                    <label for="message">Nachricht <span class="req" aria-hidden="true">*</span></label>
                    <textarea id="message" name="message" rows="5" required><?= e((string) ($old['message'] ?? '')) ?></textarea>

                    <button class="btn btn-primary" type="submit">Projektanfrage senden</button>
                </form>

                <aside class="callout-card contact-sidecard" aria-label="Kontaktinformationen">
                    <h3>Was Sie im Erstgespräch erwarten können</h3>
                    <ul class="feature-list">
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
        <div class="footer-links">
            <a href="/">Startseite</a>
            <a href="/login">Login</a>
            <a href="/dms">DMS</a>
        </div>
    </div>
</footer>

<a class="floating-cta" href="/kontakt">Erstgespräch</a>
<div class="mobile-action-bar" aria-label="Schnellaktionen">
    <a href="/leistungen">Leistungen</a>
    <a href="/kontakt">Kontakt</a>
    <a href="/kontakt">Erstgespräch</a>
</div>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
