<section class="hero section">
    <div class="shell hero-grid">
        <div>
            <p class="eyebrow">Digital Solutions für Prozessarbeit</p>
            <h1>Webplattformen, die Abläufe vereinfachen und Teams entlasten</h1>
            <p class="lead">RD Formstack Solutions verbindet Webentwicklung, Workflow-Logik und Dokumentenprozesse zu einer stabilen Lösung für Ihr Tagesgeschäft.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="#kontakt">Kostenloses Erstgespräch</a>
                <a class="btn btn-ghost" href="#leistungen">Leistungsbereiche ansehen</a>
            </div>
            <p class="hero-meta">Direkte Rückmeldung, klare nächste Schritte, keine langfristige Bindung.</p>
        </div>
        <aside class="hero-panel" aria-label="Projektfokus">
            <h2>Womit wir starten</h2>
            <ul>
                <li>Konkreter Use-Case statt generischer Lastenliste</li>
                <li>Schrittweiser Aufbau mit testbaren Inkrementen</li>
                <li>Frühe Sichtbarkeit für Fachbereich und Technik</li>
            </ul>
        </aside>
    </div>
</section>

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
                </article>
            <?php endforeach; ?>
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
    </div>
</section>
