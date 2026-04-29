<header class="site-header" id="top">
    <div class="shell header-inner">
        <a class="brand" href="/">
            <span class="brand-mark" aria-hidden="true">RD</span>
            <span>RD Formstack Solutions</span>
        </a>
        <button class="nav-toggle" id="nav-toggle" aria-label="Navigation öffnen" aria-controls="main-nav" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
            <?= $navLink('/', 'Start', $path) ?>
            <?= $navLink('/leistungen', 'Leistungen', $path) ?>
            <?= $navLink('/referenzen', 'Referenzen', $path) ?>
            <?= $navLink('/kontakt', 'Kontakt', $path) ?>
            <?= $navLink('/login', 'Login', $path) ?>
            <?= $navLink('/dms', 'DMS', $path) ?>
            <a class="btn btn-accent btn-sm" href="/kontakt">Projektanfrage</a>
        </nav>
    </div>
</header>
