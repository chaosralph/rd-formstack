<?php

declare(strict_types=1);

if ($path === '/leistungen'): ?>
    <section class="section" id="leistungen">
        <div class="shell">
            <p class="eyebrow">Leistungsbereiche</p>
            <h2>Von Idee bis produktivem Workflow</h2>
            <div class="cards-grid">
                <?php foreach ($services as $service): ?>
                    <article class="service-card">
                        <h3><?= $e($service['title']) ?></h3>
                        <p><?= $e($service['description']) ?></p>
                        <ul>
                            <?php foreach ($service['highlights'] as $highlight): ?>
                                <li><?= $e($highlight) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="section-cta-row">
                <a class="btn btn-primary" href="/kontakt">Leistungspaket abstimmen</a>
                <a class="btn btn-ghost" href="/referenzen">Passende Referenzen sehen</a>
            </div>
        </div>
    </section>
<?php elseif ($path === '/referenzen'): ?>
    <section class="section section-muted" id="referenzen">
        <div class="shell">
            <p class="eyebrow">Projektbilder</p>
            <h2>Typische Szenarien aus der Praxis</h2>
            <div class="cards-grid ref-grid">
                <?php foreach ($references as $reference): ?>
                    <article class="ref-card">
                        <p class="tag"><?= $e($reference['industry']) ?></p>
                        <h3><?= $e($reference['title']) ?></h3>
                        <p><?= $e($reference['description']) ?></p>
                        <p><strong>Ergebnis:</strong> <?= $e($reference['outcome']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="section-cta-row">
                <a class="btn btn-primary" href="/kontakt">Ähnliches Projekt anfragen</a>
                <a class="btn btn-ghost" href="/leistungen">Leistungsbereiche prüfen</a>
            </div>
        </div>
    </section>
<?php elseif ($path === '/login'): ?>
    <section class="section" id="login-placeholder">
        <div class="shell contact-layout">
            <article class="service-card">
                <p class="eyebrow">Login-Platzhalter</p>
                <h2>Kundenportal ist vorbereitet</h2>
                <p>Der Login-Bereich ist technisch angebunden und wird in der nächsten Ausbaustufe mit Nutzerrollen, Projektstatus und Benachrichtigungen ergänzt.</p>
                <ul>
                    <?php foreach (\App\View\HomepageContent::loginFeatures() as $item): ?>
                        <li><?= $e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
                <a class="btn btn-primary" href="/kontakt">Pilotzugang anfragen</a>
            </article>
            <aside class="subpage-sidecard" aria-label="Login-Ausbauphasen">
                <h3>Ausbauphasen</h3>
                <ul class="phase-list">
                    <?php foreach (\App\View\HomepageContent::loginPhases() as $phase): ?>
                        <li>
                            <strong><?= $e($phase['phase']) ?>:</strong>
                            <span><?= $e($phase['focus']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>
    </section>
<?php elseif ($path === '/dms'): ?>
    <section class="section" id="dms-placeholder">
        <div class="shell contact-layout">
            <article class="service-card">
                <p class="eyebrow">DMS-Platzhalter</p>
                <h2>DMS-Ausbau ist geplant</h2>
                <p>Die DMS-Fläche ist als Platzhalter verfügbar und wird iterativ mit Suche, Versionierung und Freigabelogik ausgebaut.</p>
                <ul>
                    <?php foreach (\App\View\HomepageContent::dmsRoadmap() as $item): ?>
                        <li><?= $e($item) ?></li>
                    <?php endforeach; ?>
                </ul>
                <a class="btn btn-primary" href="/kontakt">DMS-Use-Case besprechen</a>
            </article>
            <aside class="subpage-sidecard" aria-label="DMS-Ausbauphasen">
                <h3>Ausbauphasen</h3>
                <ul class="phase-list">
                    <?php foreach (\App\View\HomepageContent::dmsPhases() as $phase): ?>
                        <li>
                            <strong><?= $e($phase['phase']) ?>:</strong>
                            <span><?= $e($phase['focus']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>
    </section>
<?php endif;
