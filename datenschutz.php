<?php
/**
 * Datenschutzerklärung – Alle Daten dynamisch aus der Verwaltung
 */
$pageTitle = 'Datenschutzerklärung';
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
            <h1>Datenschutzerklärung</h1>
            <p class="legal-subtitle">Stand: <?php echo date('d.m.Y'); ?></p>

            <section class="legal-section">
                <h2>1. Datenschutz auf einen Blick</h2>
                <h3>Allgemeine Hinweise</h3>
                <p>Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie diese Website besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie persönlich identifiziert werden können.</p>
            </section>

            <section class="legal-section">
                <h2>2. Verantwortliche Stelle</h2>
                <p>Verantwortlich für die Datenverarbeitung auf dieser Website ist:</p>
                <p>
                    <?php echo $settings->companyAddressHtml(); ?><br><br>
                    <?php if ($settings->companyPhone()): ?>
                    <strong>Telefon:</strong> <?php echo htmlspecialchars($settings->companyPhone()); ?><br>
                    <?php endif; ?>
                    <strong>E-Mail:</strong> <a href="mailto:<?php echo htmlspecialchars($settings->companyEmail()); ?>"><?php echo htmlspecialchars($settings->companyEmail()); ?></a>
                </p>
                <?php if ($settings->get('legal_dpo_name')): ?>
                <h3>Datenschutzbeauftragter</h3>
                <p>
                    <?php echo htmlspecialchars($settings->get('legal_dpo_name')); ?><br>
                    <?php if ($settings->get('legal_dpo_email')): ?>
                    E-Mail: <a href="mailto:<?php echo htmlspecialchars($settings->get('legal_dpo_email')); ?>"><?php echo htmlspecialchars($settings->get('legal_dpo_email')); ?></a>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </section>

            <section class="legal-section">
                <h2>3. Hosting</h2>
                <p>Diese Website wird extern gehostet. Die personenbezogenen Daten, die auf dieser Website erfasst werden, werden auf den Servern des Hosters gespeichert. Hierbei kann es sich v. a. um IP-Adressen, Kontaktanfragen, Meta- und Kommunikationsdaten, Vertragsdaten, Kontaktdaten, Namen, Websitezugriffe und sonstige Daten, die über eine Website generiert werden, handeln.</p>
                <p>Das externe Hosting erfolgt zum Zwecke der Vertragserfüllung gegenüber unseren potenziellen und bestehenden Kunden (Art. 6 Abs. 1 lit. b DSGVO) und im Interesse einer sicheren, schnellen und effizienten Bereitstellung unseres Online-Angebots durch einen professionellen Anbieter (Art. 6 Abs. 1 lit. f DSGVO).</p>
            </section>

            <section class="legal-section">
                <h2>4. Allgemeine Hinweise und Pflichtinformationen</h2>
                <h3>Datenschutz</h3>
                <p>Die Betreiber dieser Seiten nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend den gesetzlichen Datenschutzvorschriften sowie dieser Datenschutzerklärung.</p>
                
                <h3>Hinweis zur verantwortlichen Stelle</h3>
                <p>Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist:</p>
                <p>
                    <?php echo htmlspecialchars($settings->companyName()); ?><br>
                    Inh. <?php echo htmlspecialchars($settings->companyOwner()); ?><br>
                    <?php echo htmlspecialchars($settings->companyStreet()); ?><br>
                    <?php echo htmlspecialchars($settings->companyZip() . ' ' . $settings->companyCity()); ?>
                </p>
                
                <h3>Widerruf Ihrer Einwilligung zur Datenverarbeitung</h3>
                <p>Viele Datenverarbeitungsvorgänge sind nur mit Ihrer ausdrücklichen Einwilligung möglich. Sie können eine bereits erteilte Einwilligung jederzeit widerrufen. Die Rechtmäßigkeit der bis zum Widerruf erfolgten Datenverarbeitung bleibt vom Widerruf unberührt.</p>
                
                <h3>Recht auf Datenübertragbarkeit</h3>
                <p>Sie haben das Recht, Daten, die wir auf Grundlage Ihrer Einwilligung oder in Erfüllung eines Vertrags automatisiert verarbeiten, an sich oder an einen Dritten in einem gängigen, maschinenlesbaren Format aushändigen zu lassen.</p>
                
                <h3>Auskunft, Löschung und Berichtigung</h3>
                <p>Sie haben im Rahmen der geltenden gesetzlichen Bestimmungen jederzeit das Recht auf unentgeltliche Auskunft über Ihre gespeicherten personenbezogenen Daten, deren Herkunft und Empfänger und den Zweck der Datenverarbeitung und ggf. ein Recht auf Berichtigung oder Löschung dieser Daten. Hierzu sowie zu weiteren Fragen zum Thema personenbezogene Daten können Sie sich jederzeit an uns wenden.</p>
                
                <h3>Recht auf Einschränkung der Verarbeitung</h3>
                <p>Sie haben das Recht, die Einschränkung der Verarbeitung Ihrer personenbezogenen Daten zu verlangen.</p>
            </section>

            <section class="legal-section">
                <h2>5. Datenerfassung auf dieser Website</h2>
                
                <h3>Cookies</h3>
                <p>Unsere Internetseiten verwenden so genannte „Cookies". Cookies sind kleine Datenpakete und richten auf Ihrem Endgerät keinen Schaden an. Sie werden entweder vorübergehend für die Dauer einer Sitzung (Session-Cookies) oder dauerhaft (permanente Cookies) auf Ihrem Endgerät gespeichert.</p>
                <p>Wir verwenden ausschließlich technisch notwendige Cookies, die für den Betrieb der Website erforderlich sind (z.B. Session-Cookies für den Login-Bereich). Diese Cookies werden auf Grundlage von Art. 6 Abs. 1 lit. f DSGVO gespeichert.</p>
                
                <h3>Server-Log-Dateien</h3>
                <p>Der Provider der Seiten erhebt und speichert automatisch Informationen in so genannten Server-Log-Dateien, die Ihr Browser automatisch an uns übermittelt. Dies sind:</p>
                <ul>
                    <li>Browsertyp und Browserversion</li>
                    <li>verwendetes Betriebssystem</li>
                    <li>Referrer URL</li>
                    <li>Hostname des zugreifenden Rechners</li>
                    <li>Uhrzeit der Serveranfrage</li>
                    <li>IP-Adresse</li>
                </ul>
                <p>Eine Zusammenführung dieser Daten mit anderen Datenquellen wird nicht vorgenommen. Die Erfassung dieser Daten erfolgt auf Grundlage von Art. 6 Abs. 1 lit. f DSGVO.</p>
                
                <h3>Anfrage per E-Mail</h3>
                <p>Wenn Sie uns per E-Mail kontaktieren, wird Ihre Anfrage inklusive aller daraus hervorgehenden personenbezogenen Daten (Name, Anfrage) zum Zwecke der Bearbeitung Ihres Anliegens bei uns gespeichert und verarbeitet. Diese Daten geben wir nicht ohne Ihre Einwilligung weiter.</p>
                <p>Die Verarbeitung dieser Daten erfolgt auf Grundlage von Art. 6 Abs. 1 lit. b DSGVO, sofern Ihre Anfrage mit der Erfüllung eines Vertrags zusammenhängt oder zur Durchführung vorvertraglicher Maßnahmen erforderlich ist.</p>
                
                <h3>Registrierung und Benutzerkonten</h3>
                <p>Für die Nutzung bestimmter Funktionen ist eine Registrierung erforderlich. Die dabei eingegebenen Daten (E-Mail-Adresse, Name) werden zum Zweck der Nutzung des Angebots verwendet. Die Verarbeitung erfolgt auf Grundlage von Art. 6 Abs. 1 lit. b DSGVO.</p>
                
                <h3>Hochgeladene Belege</h3>
                <p>Wenn Sie Belege über unsere Plattform hochladen, werden diese sicher auf unseren Servern gespeichert. Die Verarbeitung (einschließlich OCR und Kategorisierung) erfolgt zum Zweck der Buchungsunterstützung. Diese Daten werden nur für Sie und autorisierte Benutzer zugänglich gemacht und nicht an Dritte weitergegeben.</p>
            </section>

            <section class="legal-section">
                <h2>6. Externe Dienste</h2>
                
                <h3>Keine externen Fonts</h3>
                <p>Diese Website lädt keine Schriftarten von externen Servern (wie z.B. Google Fonts). Alle Schriftarten werden lokal auf unserem Server gehostet, um eine Übermittlung Ihrer IP-Adresse an Dritte zu vermeiden.</p>

                <h3>KI-gestützte Belegerkennung</h3>
                <p>Für die automatische Erkennung und Kategorisierung von Belegen kann eine KI-gestützte Verarbeitung zum Einsatz kommen. Dabei werden die Belegdaten an den API-Dienst OpenAI übermittelt. Die Verarbeitung erfolgt auf Grundlage von Art. 6 Abs. 1 lit. b DSGVO (Vertragserfüllung). Es werden nur die für die Erkennung notwendigen Bilddaten übermittelt.</p>
            </section>

            <section class="legal-section">
                <h2>7. Ihre Rechte</h2>
                <p>Sie haben folgende Rechte hinsichtlich Ihrer personenbezogenen Daten:</p>
                <ul>
                    <li>Recht auf Auskunft (Art. 15 DSGVO)</li>
                    <li>Recht auf Berichtigung (Art. 16 DSGVO)</li>
                    <li>Recht auf Löschung (Art. 17 DSGVO)</li>
                    <li>Recht auf Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
                    <li>Recht auf Datenübertragbarkeit (Art. 20 DSGVO)</li>
                    <li>Widerspruchsrecht (Art. 21 DSGVO)</li>
                </ul>
                <p>Zur Ausübung Ihrer Rechte wenden Sie sich bitte an: <a href="mailto:<?php echo htmlspecialchars($settings->companyEmail()); ?>"><?php echo htmlspecialchars($settings->companyEmail()); ?></a></p>
                <p>Darüber hinaus haben Sie das Recht, sich bei einer Datenschutz-Aufsichtsbehörde über die Verarbeitung Ihrer personenbezogenen Daten durch uns zu beschweren.</p>
            </section>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
