<?php
/**
 * Impressum – Alle Daten dynamisch aus der Verwaltung
 */
$pageTitle = 'Impressum';
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
            <h1>Impressum</h1>
            <p class="legal-subtitle">Angaben gemäß § 5 TMG</p>

            <section class="legal-section">
                <h2>Diensteanbieter</h2>
                <p>
                    <?php echo $settings->companyAddressHtml(); ?>
                </p>
            </section>

            <section class="legal-section">
                <h2>Kontakt</h2>
                <?php if ($settings->companyPhone()): ?>
                <p><strong>Telefon:</strong> <?php echo htmlspecialchars($settings->companyPhone()); ?></p>
                <?php endif; ?>
                <?php if ($settings->companyEmail()): ?>
                <p><strong>E-Mail:</strong> <a href="mailto:<?php echo htmlspecialchars($settings->companyEmail()); ?>"><?php echo htmlspecialchars($settings->companyEmail()); ?></a></p>
                <?php endif; ?>
                <?php if ($settings->companyWebsite()): ?>
                <p><strong>Website:</strong> <a href="<?php echo htmlspecialchars($settings->companyWebsite()); ?>"><?php echo htmlspecialchars($settings->companyWebsite()); ?></a></p>
                <?php endif; ?>
            </section>

            <?php if ($settings->companyUstId()): ?>
            <section class="legal-section">
                <h2>Umsatzsteuer-ID</h2>
                <p>Umsatzsteuer-Identifikationsnummer gemäß § 27a Umsatzsteuergesetz:<br>
                <?php echo htmlspecialchars($settings->companyUstId()); ?></p>
            </section>
            <?php endif; ?>

            <?php if ($settings->companyTaxNr()): ?>
            <section class="legal-section">
                <h2>Steuernummer</h2>
                <p><?php echo htmlspecialchars($settings->companyTaxNr()); ?></p>
            </section>
            <?php endif; ?>

            <?php if ($settings->get('company_register')): ?>
            <section class="legal-section">
                <h2>Handelsregister</h2>
                <p><?php echo htmlspecialchars($settings->get('company_register')); ?></p>
            </section>
            <?php endif; ?>

            <?php if ($settings->isKleinunternehmer()): ?>
            <section class="legal-section">
                <h2>Umsatzsteuer</h2>
                <p>Gemäß § 19 UStG wird keine Umsatzsteuer berechnet (Kleinunternehmerregelung).</p>
            </section>
            <?php endif; ?>

            <section class="legal-section">
                <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
                <p>
                    <?php echo htmlspecialchars($settings->companyOwner()); ?><br>
                    <?php echo htmlspecialchars($settings->companyStreet()); ?><br>
                    <?php echo htmlspecialchars($settings->companyZip() . ' ' . $settings->companyCity()); ?>
                </p>
            </section>

            <section class="legal-section">
                <h2>Streitschlichtung</h2>
                <p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
                <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener">https://ec.europa.eu/consumers/odr/</a></p>
                <?php if ($settings->get('legal_dispute_resolution')): ?>
                <p><?php echo nl2br(htmlspecialchars($settings->get('legal_dispute_resolution'))); ?></p>
                <?php endif; ?>
            </section>

            <section class="legal-section">
                <h2>Haftung für Inhalte</h2>
                <p>Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.</p>
                <p>Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.</p>
                <?php if ($settings->get('legal_disclaimer_extra')): ?>
                <p><?php echo nl2br(htmlspecialchars($settings->get('legal_disclaimer_extra'))); ?></p>
                <?php endif; ?>
            </section>

            <section class="legal-section">
                <h2>Haftung für Links</h2>
                <p>Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar.</p>
                <p>Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.</p>
            </section>

            <section class="legal-section">
                <h2>Urheberrecht</h2>
                <p>Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet.</p>
                <p>Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>
            </section>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
