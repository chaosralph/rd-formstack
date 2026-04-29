<footer class="site-footer">
    <div class="shell footer-cta">
        <p class="footer-cta-text">Bereit für den nächsten Schritt?</p>
        <a class="btn btn-primary" href="/kontakt">Unverbindliches Erstgespräch starten</a>
    </div>
    <div class="shell footer-inner">
        <p>© <?= date('Y') ?> RD Formstack Solutions</p>
        <nav class="footer-links" aria-label="Footer Navigation">
            <?= $navLink('/', 'Startseite', $path) ?>
            <?= $navLink('/leistungen', 'Leistungen', $path) ?>
            <?= $navLink('/referenzen', 'Referenzen', $path) ?>
            <?= $navLink('/kontakt', 'Kontakt', $path) ?>
        </nav>
    </div>
</footer>
