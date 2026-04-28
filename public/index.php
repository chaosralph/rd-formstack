<?php

declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;
use App\Http\ContactController;
use App\Http\Request;
use App\Repository\ContactRepository;
use App\Security\Csrf;

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
    <title>RD Formstack Solutions | Digitaler Prozessaufbau</title>
    <meta name="description" content="RD Formstack Solutions entwickelt robuste Formular- und Dokumentenprozesse mit klarer technischer Umsetzung für den Mittelstand.">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<header class="site-header">
    <div class="shell header-inner">
        <a class="brand" href="#top">RD Formstack Solutions</a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Navigation öffnen" aria-controls="main-nav" aria-expanded="false">Menü</button>
        <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
            <a href="#start">Start</a>
            <a href="#leistungen">Leistungen</a>
            <a href="#referenzen">Referenzen</a>
            <a href="#kontakt">Kontakt</a>
            <a href="#login">Login</a>
            <a href="#dms">DMS</a>
        </nav>
    </div>
</header>

<main id="top">
    <section id="start" class="hero">
        <div class="shell hero-grid">
            <div>
                <p class="eyebrow">Startseite</p>
                <h1>Technische Weblösungen für Formulare, Workflows und Dokumentenprozesse</h1>
                <p class="lead">Wir bauen digital belastbare Abläufe, die Teams sofort nutzen können: strukturiert, integrationsfähig und wartbar.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="#kontakt">Projekt anfragen</a>
                    <a class="btn btn-secondary" href="#leistungen">Leistungen ansehen</a>
                </div>
            </div>
            <aside class="hero-panel" aria-label="Leistungsfokus">
                <h2>Fokus in der ersten Version</h2>
                <ul>
                    <li>Formstack-nahe Prozessberatung</li>
                    <li>Saubere PHP-Basis für Erweiterungen</li>
                    <li>Vorbereitung für Login- und DMS-Module</li>
                </ul>
            </aside>
        </div>
    </section>

    <section id="leistungen" class="section">
        <div class="shell">
            <p class="eyebrow">Leistungen</p>
            <h2>Von Prozessanalyse bis produktiver Umsetzung</h2>
            <div class="card-grid">
                <article class="card">
                    <h3>Workflow-Design</h3>
                    <p>Prozessaufnahme, Rollenmodell und Übergaben zwischen Fachbereich, Backoffice und Management.</p>
                </article>
                <article class="card">
                    <h3>Formulare &amp; Validierung</h3>
                    <p>Digitale Eingabewege mit Datenqualität von Anfang an, inklusive klarer Pflichtfelder und Fehlerführung.</p>
                </article>
                <article class="card">
                    <h3>Integrationen</h3>
                    <p>Vorbereitung für Anbindungen an CRM, Mail, DMS und interne APIs auf stabiler technischer Grundlage.</p>
                </article>
                <article class="card">
                    <h3>Betrieb &amp; Wartung</h3>
                    <p>Nachvollziehbarer Code, modularer Aufbau und geregelte Weiterentwicklung ohne technische Schulden.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="referenzen" class="section section-alt">
        <div class="shell">
            <p class="eyebrow">Referenzen</p>
            <h2>Beispielhafte Einsatzszenarien</h2>
            <div class="reference-grid">
                <article class="reference-card">
                    <h3>Industrie-Zulieferer</h3>
                    <p>Aufbau eines digitalen Freigabeprozesses für Bestell- und Qualitätsdokumente.</p>
                    <span class="tag">Durchlaufzeit reduziert</span>
                </article>
                <article class="reference-card">
                    <h3>Dienstleistungsgruppe</h3>
                    <p>Standardisierte Angebotsformulare mit automatischer Übergabe in interne Bearbeitungsstrecken.</p>
                    <span class="tag">Weniger Medienbrüche</span>
                </article>
                <article class="reference-card">
                    <h3>Verwaltungsnaher Bereich</h3>
                    <p>Digitale Antragsstrecke mit nachvollziehbarer Dokumentation und revisionssicherer Ablage.</p>
                    <span class="tag">Bessere Nachvollziehbarkeit</span>
                </article>
            </div>
        </div>
    </section>

    <section id="kontakt" class="section">
        <div class="shell contact-layout">
            <div>
                <p class="eyebrow">Kontaktbereich</p>
                <h2>Kontakt aufnehmen</h2>
                <p>Schildern Sie kurz Ihren Prozess und wir melden uns mit einem konkreten Umsetzungsvorschlag.</p>

                <?php if (is_string($flashError)): ?>
                    <div class="alert alert-error"><?= e($flashError) ?></div>
                <?php endif; ?>

                <?php if (is_string($flashSuccess)): ?>
                    <div class="alert alert-success"><?= e($flashSuccess) ?></div>
                <?php endif; ?>
            </div>

            <form method="post" action="/" id="contact-form" class="card" novalidate>
                <input type="hidden" name="_action" value="contact.submit">
                <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">

                <label for="name">Name</label>
                <input id="name" name="name" required value="<?= e((string)($old['name'] ?? '')) ?>">

                <label for="email">E-Mail</label>
                <input id="email" name="email" type="email" required value="<?= e((string)($old['email'] ?? '')) ?>">

                <label for="message">Nachricht</label>
                <textarea id="message" name="message" rows="5" required><?= e((string)($old['message'] ?? '')) ?></textarea>

                <button type="submit">Nachricht senden</button>
            </form>
        </div>
    </section>

    <section id="login" class="section section-alt">
        <div class="shell placeholder">
            <p class="eyebrow">Login-Platzhalter</p>
            <h2>Kunden-Login folgt in der nächsten Ausbaustufe</h2>
            <p>Geplant ist ein gesicherter Bereich für Projektstatus, Dokumentenaustausch und Berechtigungsverwaltung.</p>
            <a class="btn btn-secondary" href="#kontakt">Zugang vormerken</a>
        </div>
    </section>

    <section id="dms" class="section">
        <div class="shell placeholder">
            <p class="eyebrow">DMS-Platzhalter</p>
            <h2>DMS-Anbindung wird vorbereitet</h2>
            <p>Die Architektur sieht einen späteren Anschluss an ein Dokumentenmanagementsystem mit sauberer Metadatenübergabe vor.</p>
            <a class="btn btn-secondary" href="#leistungen">Integrationsberatung anfragen</a>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="shell footer-inner">
        <p>© <?= date('Y') ?> RD Formstack Solutions</p>
        <a href="#top">Nach oben</a>
    </div>
</footer>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
