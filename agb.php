<?php
/**
 * Allgemeine Geschäftsbedingungen – Daten dynamisch aus Verwaltung
 */
$pageTitle = 'Allgemeine Geschäftsbedingungen';
$basePath = '';
require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <a href="index.php" class="nav-brand">
                <h2>
                    <span class="logo-rd">RD</span> <span class="logo-formstack">Formstack</span>
                    <span class="logo-subtitle">Form Solutions</span>
                </h2>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php">Startseite</a></li>
                <li><a href="references.php">Referenzen</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </div>
    </nav>

    <main class="legal-page">
        <div class="container">
            <h1>Allgemeine Geschäftsbedingungen</h1>
            <p class="legal-subtitle">Stand: <?php echo date('d.m.Y'); ?></p>

            <section class="legal-section">
                <h2>§ 1 Geltungsbereich</h2>
                <p>Diese Allgemeinen Geschäftsbedingungen (AGB) gelten für die Nutzung der von <strong><?php echo htmlspecialchars($settings->companyName()); ?></strong>, <?php echo htmlspecialchars($settings->companyForm()); ?>, Inh. <?php echo htmlspecialchars($settings->companyOwner()); ?>, <?php echo htmlspecialchars($settings->companyStreet()); ?>, <?php echo htmlspecialchars($settings->companyZip() . ' ' . $settings->companyCity()); ?> (nachfolgend „Anbieter") bereitgestellten Dienstleistungen.</p>
                <p>Mit der Registrierung und Nutzung der Plattform akzeptiert der Nutzer diese AGB.</p>
            </section>

            <section class="legal-section">
                <h2>§ 2 Leistungsbeschreibung</h2>
                <p>Der Anbieter stellt eine webbasierte Plattform zur Verfügung, die folgende Funktionen umfasst:</p>
                <ul>
                    <li>Upload und Verwaltung von Belegen (Rechnungen, Quittungen etc.)</li>
                    <li>Automatische Erkennung und Kategorisierung von Belegen mittels OCR und KI</li>
                    <li>Buchungsvorschläge nach SKR 03</li>
                    <li>Verwaltung und Export von Belegdaten</li>
                </ul>
                <p>Der Anbieter behält sich vor, den Funktionsumfang der Plattform jederzeit zu erweitern, einzuschränken oder zu ändern.</p>
            </section>

            <section class="legal-section">
                <h2>§ 3 Registrierung und Benutzerkonto</h2>
                <p>Für die Nutzung der Plattform ist ein Benutzerkonto erforderlich. Der Nutzer ist verpflichtet, wahrheitsgemäße Angaben bei der Registrierung zu machen und seine Zugangsdaten vertraulich zu behandeln.</p>
                <p>Der Nutzer haftet für alle Aktivitäten, die unter seinem Benutzerkonto vorgenommen werden.</p>
            </section>

            <section class="legal-section">
                <h2>§ 4 Pflichten des Nutzers</h2>
                <p>Der Nutzer verpflichtet sich:</p>
                <ul>
                    <li>Die Plattform nur für rechtmäßige Zwecke zu nutzen</li>
                    <li>Keine urheberrechtlich geschützten Inhalte Dritter hochzuladen</li>
                    <li>Seine Zugangsdaten vor dem Zugriff Dritter zu schützen</li>
                    <li>Keine automatisierten Zugriffe auf die Plattform durchzuführen</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>§ 5 Haftung</h2>
                <p>Die automatische Belegerkennung und die Buchungsvorschläge dienen lediglich als Hilfestellung und ersetzen keine professionelle steuerliche Beratung. Der Anbieter übernimmt keine Haftung für die Richtigkeit der automatisch generierten Daten.</p>
                <p>Der Nutzer ist selbst dafür verantwortlich, die erkannten Daten und Buchungsvorschläge zu prüfen.</p>
                <p>Der Anbieter haftet nicht für Schäden, die durch höhere Gewalt, Störungen im Internet oder durch unverschuldete Ausfälle der Plattform entstehen.</p>
            </section>

            <section class="legal-section">
                <h2>§ 6 Datenschutz</h2>
                <p>Der Schutz Ihrer personenbezogenen Daten ist uns wichtig. Einzelheiten zur Datenverarbeitung finden Sie in unserer <a href="datenschutz.php">Datenschutzerklärung</a>.</p>
            </section>

            <section class="legal-section">
                <h2>§ 7 Verfügbarkeit</h2>
                <p>Der Anbieter bemüht sich um eine hohe Verfügbarkeit der Plattform, garantiert jedoch keine unterbrechungsfreie Nutzung. Wartungsarbeiten und technische Störungen können zu vorübergehenden Einschränkungen führen.</p>
            </section>

            <?php if ($settings->isKleinunternehmer()): ?>
            <section class="legal-section">
                <h2>§ 8 Preise und Umsatzsteuer</h2>
                <p>Es wird gemäß § 19 UStG keine Umsatzsteuer berechnet (Kleinunternehmerregelung). Alle angegebenen Preise sind Endpreise.</p>
            </section>
            <?php endif; ?>

            <section class="legal-section">
                <h2>§ <?php echo $settings->isKleinunternehmer() ? '9' : '8'; ?> Kündigung</h2>
                <p>Beide Seiten können das Nutzungsverhältnis jederzeit ohne Angabe von Gründen kündigen. Im Falle einer Kündigung werden die Nutzerdaten gemäß den datenschutzrechtlichen Bestimmungen gelöscht.</p>
            </section>

            <section class="legal-section">
                <h2>§ <?php echo $settings->isKleinunternehmer() ? '10' : '9'; ?> Änderung der AGB</h2>
                <p>Der Anbieter behält sich vor, diese AGB jederzeit zu ändern. Über wesentliche Änderungen werden registrierte Nutzer per E-Mail informiert.</p>
            </section>

            <section class="legal-section">
                <h2>§ <?php echo $settings->isKleinunternehmer() ? '11' : '10'; ?> Schlussbestimmungen</h2>
                <p>Es gilt das Recht der Bundesrepublik Deutschland. Gerichtsstand ist, soweit gesetzlich zulässig, <?php echo htmlspecialchars($settings->companyCity()); ?>.</p>
                <p>Sollten einzelne Bestimmungen dieser AGB unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt.</p>
            </section>

            <section class="legal-section">
                <h2>Kontakt</h2>
                <p>Bei Fragen zu diesen AGB wenden Sie sich bitte an:<br>
                <a href="mailto:<?php echo htmlspecialchars($settings->companyEmail()); ?>"><?php echo htmlspecialchars($settings->companyEmail()); ?></a></p>
            </section>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
