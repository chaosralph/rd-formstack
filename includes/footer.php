<?php
/**
 * Gemeinsamer Footer – Alle Daten dynamisch aus Settings
 */
if (!isset($settings)) {
    require_once __DIR__ . '/../classes/Settings.php';
    $settings = Settings::getInstance();
}
?>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>
                        <span class="logo-rd">RD</span> <span class="logo-formstack">Formstack</span>
                    </h3>
                    <p><?php echo htmlspecialchars($settings->appDescription()); ?></p>
                </div>
                <div class="footer-links">
                    <h4>Links</h4>
                    <ul>
                        <li><a href="<?php echo $basePath ?? ''; ?>dms/dms/">DMS</a></li>
                        <li><a href="<?php echo $basePath ?? ''; ?>impressum.php">Impressum</a></li>
                        <li><a href="<?php echo $basePath ?? ''; ?>datenschutz.php">Datenschutzerklärung</a></li>
                        <li><a href="<?php echo $basePath ?? ''; ?>agb.php">AGB</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Kontakt</h4>
                    <ul>
                        <?php if ($settings->companyEmail()): ?>
                        <li>📧 <a href="mailto:<?php echo htmlspecialchars($settings->companyEmail()); ?>"><?php echo htmlspecialchars($settings->companyEmail()); ?></a></li>
                        <?php endif; ?>
                        <?php if ($settings->companyPhone()): ?>
                        <li>📞 <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^+0-9]/', '', $settings->companyPhone())); ?>"><?php echo htmlspecialchars($settings->companyPhone()); ?></a></li>
                        <?php endif; ?>
                        <?php if ($settings->companyWebsite()): ?>
                        <li>🌐 <a href="<?php echo htmlspecialchars($settings->companyWebsite()); ?>" target="_blank"><?php echo htmlspecialchars(str_replace(['https://', 'http://'], '', $settings->companyWebsite())); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings->companyName()); ?>. Alle Rechte vorbehalten.</p>
            </div>
        </div>
    </footer>

    <!-- Cookie Consent Banner -->
    <div id="cookieConsentBanner" class="cookie-banner">
        <div class="cookie-content">
            <p>🍪 Wir verwenden Cookies, um die Funktionalität unserer Website zu gewährleisten. 
               <a href="<?php echo $basePath ?? ''; ?>datenschutz.php">Mehr erfahren</a></p>
            <div class="cookie-actions">
                <button id="acceptCookies" class="btn btn-primary btn-sm">Alle akzeptieren</button>
                <button id="necessaryCookies" class="btn btn-secondary btn-sm">Nur notwendige</button>
            </div>
        </div>
    </div>

    <script src="<?php echo $basePath ?? ''; ?>assets/js/main.js?v=<?php echo time(); ?>"></script>
