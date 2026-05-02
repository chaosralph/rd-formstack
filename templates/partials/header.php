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
            <?= $path === '/' ? $navLink('#leistungen', 'Leistungen', $path) : $navLink('/leistungen', 'Leistungen', $path) ?>
            <?= $path === '/' ? $navLink('#referenzen', 'Referenzen', $path) : $navLink('/referenzen', 'Referenzen', $path) ?>
            <?= $path === '/' ? $navLink('#ablauf', 'Ablauf', $path) : $navLink('/', 'Ablauf', $path) ?>
            <?= $path === '/' ? $navLink('#kontakt', 'Kontakt', $path) : $navLink('/kontakt', 'Kontakt', $path) ?>
            <a class="nav-link nav-link-muted" href="/login">Login</a>
            <a class="nav-link nav-link-muted" href="/dms">DMS</a>
            <a class="btn btn-accent btn-sm" href="/kontakt">Projektanfrage</a>
        </nav>
    </div>
</header>
