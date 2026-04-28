# RDFA-34 CTO Unblock: QA-Blocker fuer RD Website Integration

Stand: 2026-04-28 (UTC)

## 1) Problemdefinition

Der QA-Blocker liegt nicht primar in fehlender Implementierung, sondern in fehlender Orchestrierung:
- Pflichtpruefungen existieren verteilt (Runtime, Lint, Smoke, Responsive, Security).
- Es fehlt ein einziger reproduzierbarer Einstiegspunkt mit klarer PASS/FAIL-Entscheidung.
- Accessibility-Nachweise sind teilweise manuell und dadurch als Integrations-Gate schwer operationalisierbar.

Folge:
- Hohe Reibung bei Freigaben.
- Erhoehtes Risiko fuer inkonsistente QA-Entscheidungen zwischen Agents/Umgebungen.

## 2) Technische Entscheidung

Ein zentrales QA-Gate-Skript `scripts/ci/qa-gate.sh` wird als Standard-Gate eingefuehrt.

Gate-Umfang:
1. Runtime-Check
2. PHP-Lint
3. Route-Smoke
4. Security-Smoke (CSRF/Prepared Statements/Escaping/No dangerous calls)
5. Responsive-Evidence
6. Accessibility-Smoke (serverseitig + Markup/CSS-Baseline)

Designprinzipien:
- Fail-fast bei harten Fehlern.
- Konfigurierbar ueber ENV-Flags (`RD_QA_*`).
- Artefaktbericht in `artifacts/qa/gate/report.txt`.
- Keine Secrets-Ausgabe.

## 3) Security-Risiken und Gegenmassnahmen

Risiko A: False Green durch optionale Checks
- Gegenmassnahme: Strict Mode (`RD_QA_STRICT=1`) markiert deaktivierte Accessibility-Smoke-Pruefung als FAIL.

Risiko B: Unsichere Ausfuehrung externer Tools
- Gegenmassnahme: Nur read-only QA-Checks; keine Deployments, keine Credentials im Skript.

Risiko C: Divergierende Gate-Definition zwischen Doku und Praxis
- Gegenmassnahme: `docs/qa-checklist.md` referenziert das zentrale Skript als First Path.

## 4) Developer-Agent-Aufgaben (klar getrennt)

Agent A (Platform/Core)
- Verantwortung: Stabilitaet und Wartbarkeit von `scripts/ci/qa-gate.sh`.
- DoD: Exit-Codes und Logs sind deterministisch; Gate ist lokal reproduzierbar.

Agent B (Security)
- Verantwortung: Security-Smoke-Regeln erweitern und False Positives minimieren.
- DoD: Security-Checks decken OWASP-Basics fuer Kontaktformularfluss ab.

Agent C (Data/Domain)
- Verantwortung: Testdaten- und DB-Szenarien fuer Integrations-Checks vorbereiten.
- DoD: DB-abhaengige QA-Pfade sind mit Testdaten reproduzierbar.

Agent D (API/Integration)
- Verantwortung: Gate-Erweiterung fuer kommende JSON-Endpunkte (Statuscodes, Fehlerformat).
- DoD: API-Smoke in Gate integrierbar ohne Bruch bestehender Checks.

Agent E (QA/Testing)
- Verantwortung: Accessibility-Manuallauf inkl. Evidence-Protokoll operationalisieren.
- DoD: Manual-Smoke mit Datum/Uhrzeit/Browser dokumentiert; GO/NO-GO nachvollziehbar.

## 5) Betriebsregel

- Kein Deployment ohne explizite Freigabe.
- QA-Gate muss vor Integrationsfreigaben erfolgreich laufen.
