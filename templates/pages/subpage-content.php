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
                <a class="btn btn-primary" href="/kontakt">Ähnliches Projekt anfragen</a>
                <a class="btn btn-ghost" href="/leistungen">Leistungsbereiche prüfen</a>
            </div>
        </div>
    </section>
<?php elseif ($path === '/impressum'): ?>
    <section class="section section-muted legal-section" id="impressum">
        <div class="shell legal-layout">
            <article class="service-card legal-card">
                <div class="legal-notice" role="note">
                    <strong>Hinweis zum Projektstand:</strong>
                    Diese Seite bildet die technische Grundlage für die Anbieterkennzeichnung ab. Nicht bereitgestellte oder rechtlich gesondert zu prüfende Angaben werden nicht geraten, sondern nur dort als offen benannt, wo sie noch nicht vorliegen.
                </div>

                <h2>Angaben gemäß § 5 TMG</h2>
                <dl class="legal-facts">
                    <div>
                        <dt>Diensteanbieter</dt>
                        <dd>RD Formstack Solutions Inh. Ralph Domin</dd>
                    </div>
                    <div>
                        <dt>Vertretungsberechtigte Person</dt>
                        <dd>Ralph Domin</dd>
                    </div>
                    <div>
                        <dt>Anschrift</dt>
                        <dd>Am Wingert 2, 63579 Freigericht</dd>
                    </div>
                    <div>
                        <dt>Website</dt>
                        <dd><a href="https://rddigital.de">https://rddigital.de</a></dd>
                    </div>
                    <div>
                        <dt>Kontakt</dt>
                        <dd><a href="/kontakt">Kontaktformular unter /kontakt</a><br><a href="mailto:info@rddigital.de">info@rddigital.de</a></dd>
                    </div>
                    <div>
                        <dt>Umsatzsteuer-ID</dt>
                        <dd>DE457064219</dd>
                    </div>
                    <div>
                        <dt>Registereintrag</dt>
                        <dd>Nicht vorhanden, Einzelunternehmen</dd>
                    </div>
                </dl>

                <h3>Weitere Angaben</h3>
                <ul>
                    <li>Berufsrechtliche Angaben bestehen nur, sofern eine reglementierte Tätigkeit vorliegt.</li>
                </ul>

                <h3>Redaktionell verantwortlich</h3>
                <p>Ralph Domin, Am Wingert 2, 63579 Freigericht</p>

                <h3>Streitbeilegung</h3>
                <p>Plattform der EU-Kommission zur Online-Streitbeilegung: <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener noreferrer">https://ec.europa.eu/consumers/odr/</a>.</p>
                <p>Ob eine Verpflichtung oder Bereitschaft zur Teilnahme an Verbraucherstreitbeilegungsverfahren besteht, ist im aktuellen Projektstand nicht verifiziert und muss ergänzt werden.</p>
            </article>

            <aside class="subpage-sidecard legal-sidecard" aria-label="Impressum Status">
                <h3>Technisch verifiziert</h3>
                <ul>
                    <li>Projektname und Markenbezeichnung: RD Formstack Solutions</li>
                    <li>Primäre Domain: rddigital.de</li>
                    <li>Kontaktseite: /kontakt</li>
                    <li>Eigene Impressums-URL: /impressum</li>
                </ul>
                <p>Vor endgültiger Live-Abnahme müssen die offenen Stammdaten ergänzt und fachlich geprüft werden.</p>
            </aside>
        </div>
    </section>
<?php elseif ($path === '/datenschutz'): ?>
    <section class="section legal-section" id="datenschutz">
        <div class="shell legal-layout">
            <article class="service-card legal-card">
                <div class="legal-notice" role="note">
                    <strong>Hinweis zum Projektstand:</strong>
                    Diese Datenschutzerklärung basiert auf dem aktuell verifizierbaren technischen Verhalten der Website und den bereitgestellten Anbieterangaben. Angaben zu Aufbewahrungsfristen, externen Dienstleistern und ggf. einem Datenschutzbeauftragten müssen bei Bedarf mit den realen Betriebsdaten ergänzt werden.
                </div>

                <h2>1. Verantwortlicher</h2>
                <p>Verantwortlich für die Datenverarbeitung auf dieser Website ist:</p>
                <ul>
                    <li>RD Formstack Solutions Inh. Ralph Domin</li>
                    <li>Am Wingert 2, 63579 Freigericht</li>
                    <li>E-Mail für Anfragen: <a href="mailto:info@rddigital.de">info@rddigital.de</a></li>
                    <li>Alternativ erreichbar über das <a href="/kontakt">Kontaktformular</a></li>
                    <li>Datenschutzbeauftragte Person, falls gesetzlich erforderlich: derzeit nicht separat ausgewiesen</li>
                </ul>

                <h2>2. Aufruf der Website</h2>
                <p>Beim technischen Betrieb einer Website fallen üblicherweise serverseitige Zugriffsdaten an. Im aktuellen Projekt ist HTTPS aktiv; außerdem werden Sicherheitsheader wie Content-Security-Policy, Referrer-Policy und X-Frame-Options gesetzt.</p>
                <p>Zu den technisch erforderlichen Verarbeitungen können insbesondere IP-Adresse, Datum und Uhrzeit des Zugriffs, angeforderte URL, HTTP-Status, Referrer und Angaben zum verwendeten Browser gehören. Die konkrete Log-Aufbewahrung ist im aktuellen Projektstand nicht dokumentiert und muss bei Bedarf ergänzt werden.</p>

                <h2>3. Kontaktformular</h2>
                <p>Wenn Sie das Kontaktformular nutzen, verarbeitet die Website die folgenden Eingaben:</p>
                <ul>
                    <li>Name</li>
                    <li>Unternehmen (optional)</li>
                    <li>E-Mail-Adresse</li>
                    <li>Telefon (optional)</li>
                    <li>Nachricht</li>
                </ul>
                <p>Diese Daten werden serverseitig entgegengenommen und in der Projekt-Datenbank gespeichert, um die Anfrage zu bearbeiten. Rechtsgrundlage ist je nach Anfrage Art. 6 Abs. 1 lit. b DSGVO (vorvertragliche Kommunikation) oder Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse an strukturierter Bearbeitung eingehender Anfragen).</p>

                <h2>4. Schutz vor Missbrauch und Sessions</h2>
                <p>Für die Formularsicherheit werden technisch notwendige Maßnahmen eingesetzt, darunter CSRF-Schutz, Rate-Limiting, ein Honeypot-Feld sowie eine PHP-Session für Sicherheits- und Formularzustände.</p>
                <p>Dabei kann ein technisch erforderliches Session-Cookie gesetzt werden. Dieses Cookie dient nicht Marketing- oder Analysezwecken, sondern ausschließlich der sicheren Sitzungsverwaltung und Formularfunktion.</p>

                <h2>5. Cookies und ähnliche Technologien</h2>
                <p>Im aktuell geprüften Projektstand wurden keine verifizierten Analyse-, Marketing- oder Consent-Cookies festgestellt. Deshalb ist derzeit kein separates Cookie-Banner für nicht-essenzielle Cookies technisch hinterlegt.</p>
                <p>Falls später Analyse-Tools, externe Medien, Chat-Widgets oder andere einwilligungspflichtige Dienste ergänzt werden, müssen diese Hinweise und die Consent-Mechanik vor Aktivierung erweitert werden.</p>

                <h2>6. Empfänger und Weitergabe</h2>
                <p>Im aktuellen Quellstand ist keine verifizierte Weitergabe von Formulardaten an externe Marketing- oder Analyseplattformen ersichtlich. Eine Weitergabe kann jedoch an den Hosting- oder Infrastrukturbetreiber im Rahmen des technischen Betriebs erfolgen. Konkrete Auftragsverarbeiter sind im Projektstand nicht dokumentiert und müssen bei Bedarf ergänzt werden.</p>

                <h2>7. Speicherdauer</h2>
                <p>Für Kontaktanfragen ist im vorliegenden Projekt keine verbindliche Löschfrist dokumentiert. Diese muss organisatorisch festgelegt und hier ergänzt werden.</p>

                <h2>8. Betroffenenrechte</h2>
                <p>Betroffene Personen haben nach Maßgabe der DSGVO insbesondere das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit sowie Widerspruch. Außerdem besteht ein Beschwerderecht bei einer Datenschutzaufsichtsbehörde.</p>
            </article>

            <aside class="subpage-sidecard legal-sidecard" aria-label="Datenschutz Status">
                <h3>Technisch abgedeckt</h3>
                <ul>
                    <li>HTTPS-Auslieferung der Live-Seite</li>
                    <li>Kontaktformular mit serverseitiger Verarbeitung</li>
                    <li>CSRF-Schutz, Rate-Limiting und Honeypot</li>
                    <li>Technisch erforderliche PHP-Session</li>
                    <li>Eigene Datenschutz-URL: /datenschutz</li>
                </ul>
                <p>Offen bleiben insbesondere Verantwortlichkeitsdaten, Auftragsverarbeiter und definierte Löschfristen.</p>
            </aside>
        </div>
    </section>
<?php elseif ($path === '/login'): ?>
    <section class="section" id="login-access">
        <div class="shell contact-layout auth-layout">
            <article class="service-card form-card auth-card">
                <p class="eyebrow">Portal-Login</p>
                <h2>Mit bestehendem Zugang einloggen</h2>
                <p>Nach dem Login landen Sie direkt im Dashboard. Dort folgen Referenzen, Postbox, Profil und weitere Module.</p>

                <?php if ($authRuntimeError): ?>
                    <div class="alert alert-error" role="alert">Die Login-Datenbank ist aktuell nicht erreichbar.</div>
                <?php endif; ?>

                <?php if (is_string($flashError)): ?>
                    <div class="alert alert-error" role="alert">
                        <p><?= $e($flashError) ?></p>
                        <?php if (is_array($flashErrors) && $flashErrors !== []): ?>
                            <ul class="alert-list">
                                <?php foreach ($flashErrors as $field => $errorItem): ?>
                                    <?php if (is_string($errorItem)): ?>
                                        <li><strong><?= $e((string) $field) ?>:</strong> <?= $e($errorItem) ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (is_string($flashSuccess)): ?>
                    <div class="alert alert-success" role="status"><?= $e($flashSuccess) ?></div>
                <?php endif; ?>

                <form method="post" action="/login" class="auth-form-stack">
                    <input type="hidden" name="_action" value="auth.login">
                    <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">

                    <label for="auth-email">E-Mail <span class="req">*</span></label>
                    <input id="auth-email" type="email" name="email" autocomplete="email" required value="<?= $e((string) ($old['auth_email'] ?? '')) ?>">

                    <label for="auth-password">Passwort <span class="req">*</span></label>
                    <input id="auth-password" type="password" name="password" autocomplete="current-password" required>

                    <button class="btn btn-primary" type="submit">Einloggen</button>
                </form>
            </article>

            <aside class="subpage-sidecard auth-sidecard" aria-label="Zugang und Ausbauphasen">
                <?php if ($authSetupAvailable && !$authRuntimeError): ?>
                    <h3>Erstzugang</h3>
                    <p>Noch kein Nutzer vorhanden. Richten Sie hier den ersten Admin-Zugang ein.</p>
                    <form method="post" action="/login" class="auth-form-stack">
                        <input type="hidden" name="_action" value="auth.setup">
                        <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">

                        <label for="setup-display-name">Name <span class="req">*</span></label>
                        <input id="setup-display-name" type="text" name="display_name" autocomplete="name" required value="<?= $e((string) ($old['setup_display_name'] ?? '')) ?>">

                        <label for="setup-email">E-Mail <span class="req">*</span></label>
                        <input id="setup-email" type="email" name="email" autocomplete="email" required value="<?= $e((string) ($old['setup_email'] ?? '')) ?>">

                        <label for="setup-password">Passwort <span class="req">*</span></label>
                        <input id="setup-password" type="password" name="password" autocomplete="new-password" required>

                        <label for="setup-password-confirmation">Passwort bestätigen <span class="req">*</span></label>
                        <input id="setup-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>

                        <button class="btn btn-ghost" type="submit">Ersten Zugang anlegen</button>
                    </form>
                <?php else: ?>
                    <h3>Zugang eingerichtet</h3>
                    <p>Der Admin-Zugang ist bereits eingerichtet. Melden Sie sich mit Ihrem bestehenden Konto an und ändern Sie das temporäre Passwort danach direkt im Profil.</p>
                    <div class="section-cta-row">
                        <a class="btn btn-ghost" href="/kontakt">Support anfragen</a>
                    </div>
                <?php endif; ?>

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
<?php elseif ($path === '/dashboard'): ?>
    <section class="section section-muted" id="dashboard-home">
        <div class="shell dashboard-layout">
            <article class="service-card dashboard-hero-card">
                <p class="eyebrow">Geschützter Bereich</p>
                <h2>Willkommen<?= is_array($authUser) && ($authUser['display_name'] ?? '') !== '' ? ', ' . $e((string) $authUser['display_name']) : '' ?></h2>
                <p>Dies ist der erste produktive Einstiegspunkt nach dem Login. Von hier aus werden die nächsten Module für Betrieb und Pflege von rddigital.de aufgebaut.</p>

                <?php if (is_string($flashSuccess)): ?>
                    <div class="alert alert-success" role="status"><?= $e($flashSuccess) ?></div>
                <?php endif; ?>

                <?php if (is_string($flashError)): ?>
                    <div class="alert alert-error" role="alert"><?= $e($flashError) ?></div>
                <?php endif; ?>

                <div class="dashboard-toolbar">
                    <span class="tag">Rolle: <?= $e((string) (($authUser['role'] ?? 'admin'))) ?></span>
                    <span class="tag">E-Mail: <?= $e((string) (($authUser['email'] ?? ''))) ?></span>
                </div>
            </article>

            <div class="dashboard-grid">
                <article class="service-card dashboard-module-card">
                    <h3>Postbox</h3>
                    <p>Kontaktanfragen im Dashboard lesen, bearbeiten und später direkt per E-Mail beantworten.</p>
                    <ul>
                        <li>Nächster Schritt: Inbox mit Status und Antwort-Workflow</li>
                        <li>Quelle: bestehendes Kontaktformular</li>
                    </ul>
                </article>

                <article class="service-card dashboard-module-card">
                    <h3>Referenzen</h3>
                    <p>Referenzkarten künftig im Dashboard anlegen, bearbeiten und auf der Landingpage ausgeben.</p>
                    <ul>
                        <li>Nächster Schritt: CRUD + Sortierung + Sichtbarkeit</li>
                        <li>Ziel: Landingpage-Pflege ohne Codeänderung</li>
                    </ul>
                </article>

                <article class="service-card dashboard-module-card">
                    <h3>Profil</h3>
                    <p>Persönliche Daten und Passwort künftig direkt im geschützten Bereich pflegen.</p>
                    <ul>
                        <li>Nächster Schritt: Profilseite + Passwortwechsel</li>
                    </ul>
                </article>

                <article class="service-card dashboard-module-card placeholder-card">
                    <h3>DMS</h3>
                    <p>Der DMS-Bereich bleibt vorerst ein geplanter Ausbaupfad und wird hier später direkt angebunden.</p>
                    <a class="btn btn-ghost" href="/dms">DMS-Platzhalter öffnen</a>
                </article>
            </div>
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
