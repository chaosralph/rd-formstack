<footer class="site-footer">
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
