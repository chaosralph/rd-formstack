<section class="hero section">
    <div class="shell hero-grid">
        <div class="hero-copy">
            <p class="eyebrow">Digital Solutions für Prozessarbeit</p>
            <h1>Digitale Abläufe, die Teams wirklich entlasten</h1>
            <p class="lead">RD Formstack Solutions verbindet Webentwicklung, Workflow-Logik und Dokumentenprozesse zu einer belastbaren Plattform für Ihr Tagesgeschäft.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="#kontakt">Kostenloses Erstgespräch</a>
                <a class="btn btn-ghost" href="#leistungen">Leistungen ansehen</a>
            </div>
            <p class="hero-meta">Technisch sauber umgesetzt, fachlich verständlich dokumentiert und auf realistische Betriebsabläufe ausgerichtet.</p>
            <ul class="hero-trust" aria-label="Schnelle Orientierung">
                <li><strong>Schnell:</strong> Erstes Feedback in der Regel innerhalb eines Werktags</li>
                <li><strong>Klar:</strong> Fokus auf konkrete Engpässe statt Buzzwords</li>
                <li><strong>Planbar:</strong> Schrittweise Umsetzung mit messbaren Etappen</li>
            </ul>
        </div>

        <aside class="hero-panel" aria-label="Projektfokus">
            <p class="hero-panel-title">Typischer Start</p>
            <h2>Vom Problem zur umsetzbaren Roadmap</h2>
            <ul>
                <li>Ist-Analyse der aktuellen Prozesskette</li>
                <li>Priorisierung nach Aufwand und Wirkung</li>
                <li>Prototyping mit frühem Fachbereichsfeedback</li>
            </ul>
            <a class="btn btn-accent" href="#ablauf">Ablauf ansehen</a>
        </aside>
    </div>
</section>

<section class="section section-quicknav" aria-label="Direkteinstieg">
    <div class="shell quicknav-grid">
        <a class="quick-link" href="#leistungen">
            <p>Leistungen</p>
            <strong>Was wir konkret umsetzen</strong>
        </a>
        <a class="quick-link" href="#referenzen">
            <p>Referenzen</p>
            <strong>Typische Szenarien aus der Praxis</strong>
        </a>
        <a class="quick-link" href="#ablauf">
            <p>Ablauf</p>
            <strong>Wie wir ein Projekt strukturieren</strong>
        </a>
    </div>
</section>

<section class="section section-proof" aria-label="Warum RD Formstack Solutions">
    <div class="shell proof-grid">
        <article class="proof-card">
            <p>Transparente Priorisierung</p>
            <strong>Jeder Umsetzungsschritt ist fachlich begründet und budgetierbar.</strong>
        </article>
        <article class="proof-card">
            <p>Produktionsreife Umsetzung</p>
            <strong>Von Formular bis Prozesslogik werden Sicherheits- und Betriebsaspekte früh mitgedacht.</strong>
        </article>
        <article class="proof-card">
            <p>Messbarer Fortschritt</p>
            <strong>Kurze Iterationen mit klaren Ergebnissen statt monolithischer Big-Bang-Projekte.</strong>
        </article>
    </div>
</section>

<section class="section" id="leistungen">
    <div class="shell">
        <p class="eyebrow">Leistungsbereiche</p>
        <h2>Von der Idee bis zum produktiven Workflow</h2>
        <p class="section-intro">Wir kombinieren technische Umsetzung mit Prozessverständnis, damit Lösungen nicht nur funktionieren, sondern im Alltag genutzt werden.</p>
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
            <a class="btn btn-primary" href="#kontakt">Passendes Leistungspaket abstimmen</a>
            <a class="btn btn-ghost" href="/leistungen">Alle Leistungsdetails</a>
        </div>
    </div>
</section>

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
                    <ul>
                        <?php foreach ($reference['focus'] as $focus): ?>
                            <li><?= $e($focus) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p>
                        <a class="btn btn-ghost" href="<?= $e($reference['url']) ?>" target="_blank" rel="noopener noreferrer"><?= $e($reference['linkLabel']) ?></a>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="section-cta-row">
            <a class="btn btn-primary" href="/referenzen">Weitere Projektszenarien</a>
            <a class="btn btn-ghost" href="#kontakt">Eigenen Case besprechen</a>
        </div>
    </div>
</section>

<section class="section section-faq" aria-label="Häufige Fragen">
    <div class="shell">
        <p class="eyebrow">Häufige Fragen</p>
        <h2>Was Entscheider vor dem Start typischerweise klären</h2>
        <div class="faq-grid">
            <details class="faq-card" open>
                <summary>Wie schnell kann ein erster Umsetzungsschritt starten?</summary>
                <p>Nach dem Erstgespräch kann die erste priorisierte Maßnahme in der Regel kurzfristig geplant und vorbereitet werden.</p>
            </details>
            <details class="faq-card">
                <summary>Arbeiten Sie auch mit bestehenden Systemen?</summary>
                <p>Ja. Ziel ist eine saubere Integration in bestehende Abläufe, statt isolierter Einzellösungen.</p>
            </details>
            <details class="faq-card">
                <summary>Wie wird der Projektfortschritt transparent gemacht?</summary>
                <p>Durch klar abgegrenzte Etappen mit nachvollziehbaren Ergebnissen und abgestimmten Prioritäten.</p>
            </details>
        </div>
    </div>
</section>

<section class="section section-process" id="ablauf">
    <div class="shell">
        <p class="eyebrow">Ablauf</p>
        <h2>So wird aus einer Anfrage ein belastbarer Umsetzungsplan</h2>
        <ol class="process-grid">
            <?php foreach ($processSteps as $step): ?>
                <li class="process-card">
                    <h3><?= $e($step['title']) ?></h3>
                    <p><?= $e($step['description']) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
        <div class="next-steps">
            <?php foreach ($nextSteps as $step): ?>
                <p><strong><?= $e($step['title']) ?>:</strong> <?= $e($step['text']) ?></p>
            <?php endforeach; ?>
        </div>
        <div class="section-cta-row">
            <a class="btn btn-accent" href="#kontakt">Nächsten Schritt terminieren</a>
        </div>
    </div>
</section>
