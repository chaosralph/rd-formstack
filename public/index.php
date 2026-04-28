<?php

declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;
use App\Http\ContactController;
use App\Http\Request;
use App\Repository\ContactRepository;
use App\Security\Csrf;
use App\View\HomepageContent;

session_start();

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

$databaseConfig = require __DIR__ . '/../config/database.php';
$pdo = Connection::get($databaseConfig);
$contactRepository = new ContactRepository($pdo);
$contactController = new ContactController($contactRepository);

if (Request::method() === 'POST' && ($_POST['_action'] ?? '') === 'contact.submit') {
    $contactController->submit();
}

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
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>RD Formstack Solutions | Webentwicklung, DMS & digitale Prozesse</title>
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
        <a class="brand" href="#top">RD Formstack Solutions</a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Navigation öffnen" aria-controls="main-nav" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
            <a href="#leistungen">Leistungen</a>
            <a href="#referenzen">Referenzen</a>
            <a href="#login">Login</a>
            <a href="#dms">DMS</a>
            <a href="#prozess">Vorgehen</a>
            <a href="#kontakt">Kontakt</a>
            <a class="btn btn-primary btn-small" href="#kontakt">Projektanfrage</a>
        </nav>
    </div>
</header>

<main id="main">
    <section class="hero section">
        <div class="shell hero-grid">
            <div>
                <p class="eyebrow">Digitale Lösungen für klare Abläufe</p>
                <h1>Webentwicklung, Belegverwaltung und DMS für belastbare Geschäftsprozesse</h1>
                <p class="lead">Wir unterstützen B2B-Teams dabei, manuelle Abläufe in robuste digitale Prozesse zu überführen: mit klarer Nutzerführung, wartbarer Architektur und messbarem operativem Nutzen.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="#kontakt">Projekt anfragen</a>
                    <a class="btn btn-secondary" href="#leistungen">Leistungen ansehen</a>
                </div>
            </div>
            <aside class="hero-panel" aria-label="Kernvorteile">
                <h2>Worauf wir uns fokussieren</h2>
                <ul>
                    <li>Klare UX und saubere Nutzerführung</li>
                    <li>Digitale Prozesse mit messbarem Nutzen</li>
                    <li>Modulare Basis für Skalierung und Betrieb</li>
                </ul>
            </aside>
        </div>
    </section>

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

    <section id="leistungen" class="section">
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

    <section id="referenzen" class="section">
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

    <section id="login" class="section section-alt">
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

    <section id="dms" class="section">
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

    <section id="prozess" class="section section-alt">
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

    <section id="kontakt" class="section">
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

            <form method="post" action="/" id="contact-form" class="form-card" novalidate>
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
</main>

<footer class="site-footer">
    <div class="shell footer-inner">
        <p>© <?= date('Y') ?> RD Formstack Solutions</p>
        <a href="#top">Nach oben</a>
    </div>
</footer>

<a class="floating-cta" href="#kontakt">Projektanfrage</a>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
