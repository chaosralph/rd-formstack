<?php

declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;
use App\Http\ContactController;
use App\Http\Request;
use App\Repository\ContactRepository;
use App\Security\Csrf;
use App\View\HomepageContent;

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

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

require_once __DIR__ . '/../config/env.php';
Env::load(__DIR__ . '/../.env');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');
$path = $path === '' ? '/' : $path;

if (Request::method() === 'POST' && ($_POST['_action'] ?? '') === 'contact.submit') {
    try {
        $databaseConfig = require __DIR__ . '/../config/database.php';
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

$routes = [
    '/' => [
        'title' => 'Startseite',
        'headline' => 'Webentwicklung, Belegverwaltung und DMS für belastbare Geschäftsprozesse',
        'intro' => 'Wir unterstützen B2B-Teams dabei, manuelle Abläufe in robuste digitale Prozesse zu überführen: mit klarer Nutzerführung, wartbarer Architektur und messbarem operativem Nutzen.',
    ],
    '/leistungen' => [
        'title' => 'Leistungen',
        'headline' => 'Leistungen für digitale Prozessstabilität',
        'intro' => 'Vier Leistungsbereiche für klare Verantwortung in Umsetzung, Betrieb und Weiterentwicklung.',
    ],
    '/referenzen' => [
        'title' => 'Referenzen',
        'headline' => 'Praxisnahe Referenzszenarien',
        'intro' => 'Typische Projektkontexte, in denen wir Webentwicklung und Dokumentenprozesse zusammenführen.',
    ],
    '/kontakt' => [
        'title' => 'Kontakt',
        'headline' => 'Projekt unverbindlich besprechen',
        'intro' => 'Beschreiben Sie Ihr Vorhaben. Wir melden uns mit einer realistischen Einschätzung für die nächsten Schritte.',
    ],
    '/login' => [
        'title' => 'Login',
        'headline' => 'Kundenportal in Vorbereitung',
        'intro' => 'Der geschützte Login-Bereich wird in der nächsten Ausbaustufe bereitgestellt.',
    ],
    '/dms' => [
        'title' => 'DMS',
        'headline' => 'DMS-Bereich als technischer Platzhalter',
        'intro' => 'Die DMS-Fläche wird in Stufen mit Freigabe, Historie und Suchlogik ergänzt.',
    ],
];

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

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function navLink(string $href, string $label, string $currentPath): string
{
    $active = $href === $currentPath ? ' is-active' : '';
    return '<a class="nav-link' . $active . '" href="' . e($href) . '">' . e($label) . '</a>';
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>RD Formstack Solutions | <?= e($page['title']) ?></title>
    <meta name="description" content="RD Formstack Solutions entwickelt Weblösungen, digitale Workflows, Belegverwaltung und DMS-nahe Prozesse für den Mittelstand.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <section class="hero section">
        <div class="shell hero-grid <?= $path === '/' ? '' : 'hero-grid-single' ?>">
            <div>
                <p class="eyebrow">RD Formstack Solutions</p>
                <h1><?= e($page['headline']) ?></h1>
                <p class="lead"><?= e($page['intro']) ?></p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="/kontakt">Projekt anfragen</a>
                    <a class="btn btn-secondary" href="/leistungen">Leistungen ansehen</a>
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
        <section class="section">
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
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/referenzen'): ?>
        <section class="section <?= $path === '/referenzen' ? 'section-alt' : '' ?>">
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
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/login'): ?>
        <section class="section section-alt">
            <div class="shell placeholder-wrap">
                <div>
                    <p class="eyebrow">Login</p>
                    <h2>Kundenportal in Vorbereitung</h2>
                    <p>Der geschützte Login-Bereich wird in der nächsten Ausbaustufe bereitgestellt. Geplant sind rollenbasierte Zugriffe, persönliche Dashboards und sichere Dokumentenansichten.</p>
                </div>
                <aside class="placeholder-card" aria-label="Login-Platzhalter">
                    <h3>Geplante Funktionen</h3>
                    <ul class="feature-list">
                        <?php foreach ($loginFeatures as $feature): ?>
                            <li><?= e($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </aside>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($path === '/' || $path === '/dms'): ?>
        <section class="section">
            <div class="shell placeholder-wrap">
                <div>
                    <p class="eyebrow">DMS-Platzhalter</p>
                    <h2>DMS-Bereich wird ausgebaut</h2>
                    <p>Die DMS-Fläche ist als technischer Platzhalter integriert und wird schrittweise mit Dokumentenklassifikation, Revisionshistorie und Freigabeprozessen ergänzt.</p>
                </div>
                <aside class="placeholder-card" aria-label="DMS-Platzhalter">
                    <h3>Nächste Ausbaustufen</h3>
                    <ul class="feature-list">
                        <?php foreach ($dmsRoadmap as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
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

                    <label for="name">Name</label>
                    <input id="name" name="name" required value="<?= e((string) ($old['name'] ?? '')) ?>" autocomplete="name">

                    <label for="company">Unternehmen</label>
                    <input id="company" name="company" required value="<?= e((string) ($old['company'] ?? '')) ?>" autocomplete="organization">

                    <label for="email">E-Mail</label>
                    <input id="email" name="email" type="email" required value="<?= e((string) ($old['email'] ?? '')) ?>" autocomplete="email">

                    <label for="phone">Telefon (optional)</label>
                    <input id="phone" name="phone" type="tel" value="<?= e((string) ($old['phone'] ?? '')) ?>" autocomplete="tel">

                    <label for="message">Nachricht</label>
                    <textarea id="message" name="message" rows="5" required><?= e((string) ($old['message'] ?? '')) ?></textarea>

                    <button class="btn btn-primary" type="submit">Nachricht senden</button>
                </form>
            </div>
        </section>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="shell footer-inner">
        <p>© <?= date('Y') ?> RD Formstack Solutions</p>
        <a href="/">Startseite</a>
    </div>
</footer>

<a class="floating-cta" href="/kontakt">Projektanfrage</a>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
