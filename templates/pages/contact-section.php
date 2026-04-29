<section class="section contact-cta" id="kontakt">
    <div class="shell contact-layout">
        <div class="contact-intro">
            <p class="eyebrow">Kontakt</p>
            <h2>Vorhaben in 30 Minuten strukturieren</h2>
            <p>Beschreiben Sie den aktuellen Engpass. Sie erhalten eine realistische Empfehlung für die nächsten Schritte.</p>
            <ul class="contact-points">
                <li>Antwort in der Regel innerhalb eines Werktags</li>
                <li>Klare Einschätzung zu Aufwand und Prioritäten</li>
                <li>Unverbindlich und ohne Vertragsbindung</li>
            </ul>
            <a class="btn btn-ghost" href="#top">Zur Navigation</a>

            <?php if (is_string($flashError)): ?>
                <div class="alert alert-error" role="alert">
                    <p><?= $e($flashError) ?></p>
                    <?php if (is_array($flashErrors) && $flashErrors !== []): ?>
                        <ul class="alert-list">
                            <?php foreach ($flashErrors as $errorItem): ?>
                                <?php if (is_string($errorItem)): ?>
                                    <li><?= $e($errorItem) ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (is_string($flashSuccess)): ?>
                <div class="alert alert-success" role="status"><?= $e($flashSuccess) ?></div>
            <?php endif; ?>
        </div>

        <form method="post" action="/kontakt" id="contact-form" class="form-card" novalidate>
            <input type="hidden" name="_action" value="contact.submit">
            <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
            <div class="hp-field" aria-hidden="true">
                <label for="website">Website</label>
                <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>
            <p id="required-note" class="required-note">Felder mit <span class="req" aria-hidden="true">*</span> sind Pflichtfelder.</p>

            <div class="form-grid">
                <div class="field">
                    <label for="name">Name <span class="req" aria-hidden="true">*</span></label>
                    <input id="name" name="name" required aria-describedby="required-note" value="<?= $e((string) ($old['name'] ?? '')) ?>" autocomplete="name">
                </div>

                <div class="field">
                    <label for="company">Unternehmen (optional)</label>
                    <input id="company" name="company" value="<?= $e((string) ($old['company'] ?? '')) ?>" autocomplete="organization">
                </div>

                <div class="field">
                    <label for="email">E-Mail <span class="req" aria-hidden="true">*</span></label>
                    <input id="email" name="email" type="email" required aria-describedby="required-note" value="<?= $e((string) ($old['email'] ?? '')) ?>" autocomplete="email">
                </div>

                <div class="field">
                    <label for="phone">Telefon (optional)</label>
                    <input id="phone" name="phone" type="tel" value="<?= $e((string) ($old['phone'] ?? '')) ?>" autocomplete="tel">
                </div>
            </div>

            <label for="message">Nachricht <span class="req" aria-hidden="true">*</span></label>
            <textarea id="message" name="message" rows="5" maxlength="6000" required aria-describedby="required-note message-counter"><?= $e((string) ($old['message'] ?? '')) ?></textarea>
            <p id="message-counter" class="char-counter" aria-live="polite">0 / 6000 Zeichen</p>

            <button class="btn btn-primary" type="submit">Projektanfrage senden</button>
        </form>

        <aside class="contact-sidecard" aria-label="Kontaktinformationen">
            <h3>Rahmen für den Ersttermin</h3>
            <ul>
                <?php foreach ($contactHighlights as $highlight): ?>
                    <li><strong><?= $e($highlight['label']) ?>:</strong> <?= $e($highlight['value']) ?></li>
                <?php endforeach; ?>
            </ul>
            <p class="contact-note">Tipp: Je konkreter Use-Case, Systemlandschaft und Zielbild beschrieben sind, desto präziser kann die erste Einschätzung ausfallen.</p>
        </aside>
    </div>
</section>
