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
                    Diese Seite bildet die technische Grundlage für die Anbieterkennzeichnung ab. Nicht im Projekt oder auf der Website verifizierbare Pflichtangaben werden bewusst nicht geraten, sondern als offene Felder ausgewiesen.
                </div>

                <h2>Angaben gemäß § 5 TMG</h2>
                <dl class="legal-facts">
                    <div>
                        <dt>Angebotsname</dt>
                        <dd>RD Formstack Solutions</dd>
                    </div>
                    <div>
                        <dt>Website</dt>
                        <dd><a href="https://rddigital.de">https://rddigital.de</a></dd>
                    </div>
                    <div>
                        <dt>Verifizierter Kontaktweg im aktuellen Projektstand</dt>
                        <dd><a href="/kontakt">Kontaktformular unter /kontakt</a></dd>
                    </div>
                </dl>

                <h3>Offene Pflichtangaben zur Ergänzung</h3>
                <ul>
                    <li>Vollständiger Name oder Firma des Diensteanbieters</li>
                    <li>Ladungsfähige Postanschrift</li>
                    <li>Vertretungsberechtigte Person, falls einschlägig</li>
                    <li>Direkte Kontaktmöglichkeit per E-Mail und ggf. Telefon</li>
                    <li>Registergericht und Registernummer, falls vorhanden</li>
                    <li>Umsatzsteuer-ID / Wirtschafts-ID, falls vorhanden</li>
                    <li>Berufsrechtliche Angaben, falls reglementierte Tätigkeit vorliegt</li>
                </ul>

                <h3>Redaktionell verantwortlich</h3>
                <p>Offenes Feld: Verantwortliche Person / Anschrift für journalistisch-redaktionelle Inhalte ist im vorliegenden Projektstand nicht verifiziert.</p>

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
                    Diese Datenschutzerklärung basiert auf dem aktuell verifizierbaren technischen Verhalten der Website. Angaben zum Verantwortlichen, zu Aufbewahrungsfristen und zu externen Dienstleistern müssen bei Bedarf mit den realen Betriebsdaten ergänzt werden.
                </div>

                <h2>1. Verantwortlicher</h2>
                <p>Verifizierbar ist aktuell nur die Angebotsbezeichnung <strong>RD Formstack Solutions</strong> für die Website <strong>rddigital.de</strong>.</p>
                <ul>
                    <li>Offenes Feld: Vollständiger Name / Firma des Verantwortlichen</li>
                    <li>Offenes Feld: Ladungsfähige Anschrift</li>
                    <li>Offenes Feld: E-Mail-Adresse für Datenschutzanfragen</li>
                    <li>Offenes Feld: Telefon, falls bereitgestellt</li>
                    <li>Offenes Feld: Datenschutzbeauftragte Person, falls gesetzlich erforderlich</li>
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
