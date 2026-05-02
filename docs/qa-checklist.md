## RDFA-48 QA/DevOps Validation Update (2026-04-29 UTC)

## RDFA-49 Header/Host Regression Gate Update (2026-04-29 UTC)

Neuer deterministischer Pflichtcheck im QA-Gate:

```bash
bash scripts/ci/header-host-regression.sh
```

Lokal kompletter Gate-Run (fail-fast):

```bash
bash scripts/ci/qa-gate.sh --strict=1 --run-a11y-smoke=1 --run-responsive=0
```

PASS-Kriterien fuer `header-host-regression.sh`:
- `GET /` und `GET /kontakt` liefern `200`.
- Security Header vorhanden: `X-Content-Type-Options=nosniff`, `X-Frame-Options=DENY`, `Referrer-Policy=strict-origin-when-cross-origin`.
- `Content-Security-Policy` enthaelt mindestens `default-src 'self'`, `frame-ancestors 'none'`, `form-action 'self'`.
- `GET /sitemap.xml` liefert `200` mit `Content-Type` inkl. `application/xml`.
- Bei Requests mit `Host: evil.example` und `X-Forwarded-Host: evil.example` darf `evil.example` weder in `Location` noch im Response-Body auftauchen.

FAIL-Kriterien:
- Abweichender HTTP-Status, fehlender/abweichender Pflichtheader oder CSP-Regression.
- Unerwarteter `Content-Type` fuer `/sitemap.xml`.
- Reflektion von `evil.example` in `Location` oder Body.

Deterministischer Gate-Run (fail-fast) erfolgt ueber:

```bash
bash scripts/ci/qa-gate.sh --strict=1 --run-a11y-smoke=1 --run-responsive=0
```

Gueltige Flags:
- `--strict=0|1`
- `--run-a11y-smoke=0|1`
- `--run-responsive=0|1`
- `-h|--help`

Fail-Fast-Regeln:
- Bei erstem Pflichtcheck-Fehler beendet das Script sofort mit Exit `1`.
- Ungueltige oder unbekannte Flags beenden mit klarer Fehlermeldung (Exit `1` bzw. `2`).
- Report wird immer geschrieben: `artifacts/qa/gate/report.txt`.

CI-Gate-Fokus:
- Required Workflow ist auf den Job `qa-gate` fokussiert.
- Keine Deployment-Jobs im Required-Workflow.

# QA Gate P0 - Neue Website (RD Formstack Solutions)

Stand: 2026-04-29 (UTC)
Scope: Landingpage + Kontaktformular (`public/index.php`, `public/assets/*`, `src/Controller/ContactController.php`)

## 0) Standard-Entry (RDFA-34)

Primarer QA-Entry fuer Integrationsfreigaben:

```bash
composer run check:qa-gate
```

Hinweise:
- Standard ist `Strict Mode` (`RD_QA_STRICT=1`).
- Optional konfigurierbar:
  - `RD_QA_RUN_RESPONSIVE=0` (Responsive-Screenshots ueberspringen)
  - `RD_QA_RUN_A11Y_SMOKE=0` (Accessibility-Smoke ueberspringen)
- Ergebnisbericht: `artifacts/qa/gate/report.txt`

## 1) Gate-Definition (Freigabe)

P0 gilt als **PASS**, wenn alle folgenden Blöcke grün sind:

1. **Runtime & Syntax**
- `check:runtime` erfolgreich
- PHP-Lint ohne Fehler auf allen `.php`-Dateien

2. **Responsive Smoke (manuell, Pflicht)**
- Seite ist auf 360px, 768px und 1280px ohne horizontales Scrollen benutzbar
- Mobile-Navigation (`#nav-toggle`) öffnet/schließt korrekt
- Kontaktformular bleibt vollständig sichtbar und bedienbar

3. **Accessibility Smoke (manuell + statisch, Pflicht)**
- Tastaturbedienung: Skip-Link fokussierbar, Navigation und Formular erreichbar
- Fokus sichtbar auf interaktiven Elementen
- Labels sind mit Formularfeldern verknüpft (`for`/`id`)
- Semantische Hauptstruktur (`header`, `main`, `nav`, `footer`) vorhanden

4. **Basic Security Kontaktformular (Pflicht)**
- CSRF-Prüfung serverseitig aktiv
- Eingabevalidierung für Pflichtfelder aktiv
- DB-Write via Prepared Statements
- Output-Escaping aktiv (XSS-Basisschutz)
- Keine offensichtlichen gefährlichen PHP-Aufrufe (`eval/exec/system/...`) im Repo
- Kein offensichtlicher PII-/Secret-Leak in `storage/logs`

5. **Infra Access Readiness (RDFA-33, Pflicht für Access-Themen)**
- GitHub CLI Auth ist aktiv
- API-Basiszugriff via GitHub CLI funktioniert
- GitHub App Installationen sind lesbar

Wenn **ein** Pflichtpunkt fehlschlägt oder nicht nachgewiesen ist: **FAIL / NO-GO**.

## 2) Ausführbare Checks (Kommandos)

### A. Runtime & PHP-Lint
```bash
composer run check:runtime
find . -type f -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

### B. Responsive Smoke (manuell)
```bash
php -S localhost:8000 -t public
```
Dann im Browser prüfen:
- Viewports: 360x800, 768x1024, 1280x800
- Navigation mobil (`#nav-toggle`) und Desktop-Menü
- Kontaktformular-Eingabe + Submit-Button sichtbar

### C. Accessibility Smoke
```bash
bash scripts/ci/accessibility-smoke.sh
```
Manuelle Pflichtprüfungen:
- `Tab` ab Seitenanfang: Skip-Link sichtbar und funktioniert
- Fokusindikatoren auf Links/Buttons/Inputs sichtbar
- Formular über Tastatur vollständig ausfüllbar und submitbar

### D. Basic Security Kontaktformular (statische Smoke-Checks)
```bash
grep -RIn --exclude-dir=.git "Csrf::validate" src public
grep -RIn --exclude-dir=.git -E "prepare\(|execute\(" src
grep -RIn --exclude-dir=.git -E "htmlspecialchars\(|function e\(" public/index.php
grep -RIn --exclude-dir=.git -E "(eval\(|shell_exec\(|exec\(|system\(|passthru\(|proc_open\()" .
bash scripts/ci/pii-log-review.sh
```

### E. Infra Access Readiness (RDFA-33)
```bash
bash scripts/check-github-access.sh
```

## 3) Ergebnisprotokoll (heutiger Stand)

### Automatisiert ausgeführt
1. `composer run check:runtime` -> **PASS**
2. PHP-Lint auf allen `.php`-Dateien -> **PASS**
3. Statische Security-Smoke-Checks (CSRF/Prepared Statements/Escaping) -> **PASS**
4. Suche nach gefährlichen PHP-Aufrufen -> **PASS** (keine Treffer)
5. PII-Log-Smoke in `storage/logs` -> **PASS**

### Noch offen (manuell im Zielbrowser)
1. Responsive Smoke auf 360/768/1280
2. Accessibility Keyboard/Fokus-Smoke

## 4) Smoke-Ausführung 2026-04-28 (Pflichtpunkte)

### 4.1 Responsive Smoke 360/768/1280
- Status: **PASS**
- Evidenz:
  1. Script: `bash scripts/ci/responsive-evidence.sh`
  2. Ergebnis: `Responsive evidence created in .../artifacts/qa/responsive`
  3. Artefakte:
     - `artifacts/qa/responsive/home-360x800.png`
     - `artifacts/qa/responsive/home-768x1024.png`
     - `artifacts/qa/responsive/home-1280x800.png`
     - `artifacts/qa/responsive/kontakt-360x800.png`
     - `artifacts/qa/responsive/report.txt`
- Bewertung: Viewport-basierte Browser-Evidence ist reproduzierbar erzeugt.

### 4.2 Accessibility Smoke (Keyboard + sichtbare Fokuszustände)
- Status: **PASS**
- Evidenz:
  1. Keyboard-E2E erfolgreich: `bash scripts/ci/accessibility-keyboard-e2e.sh`
  2. Nachweisdatei: `artifacts/qa/a11y-keyboard/report.json`
  3. Screenshots: `artifacts/qa/a11y-keyboard/01-skip-link-focus.png` bis `05-contact-fields-focus.png`
- Bewertung: Skip-Link, Tastaturfokus und Label/for-Bindings automatisiert verifiziert.

## 5) Freigabeentscheidung P0

Aktueller Status: **PASS (GO)**

Begründung:
- Runtime-, Lint-, Security-, Responsive- und Accessibility-Pflichtprüfungen sind bestanden.

Freigabe erteilt: **PASS/GO**.

## 6) UX/A11y Erweiterung (RDFA-46, additiv)

Diese Sektion ergaenzt die bestehende QA-Checklist um verbindliche UI/UX- und Accessibility-Pruefungen.
Bestehende Gate-Kriterien bleiben unveraendert gueltig.

### 6.1 UX-Visual-Consistency Checks (Desktop + Mobile)

Statusregel:
- **PASS**, wenn alle folgenden Punkte auf 360x800, 768x1024 und 1280x800 erfuellt sind.
- **FAIL**, wenn ein Punkt verletzt ist.

Pruefpunkte:
1. `UX-RESP-01` Keine horizontale Scrollbar:
- Messung: `document.documentElement.scrollWidth <= window.innerWidth`.

2. `UX-RESP-02` Navigation:
- Desktop-Navigation sofort sichtbar.
- Mobile-Navigation (`#nav-toggle`) oeffnet/schliesst korrekt.

3. `UX-RESP-03` Kontaktformular erreichbar:
- Kontaktformular inkl. Submit auf allen Ziel-Viewports vollstaendig erreichbar.

4. `UX-FORM-01` Komponenten-Groessen:
- Interaktive Controls im Formular mind. 44px Hoehe; Touch-Ziele auf Mobile mind. 44x44px.

5. Motion-Verhalten:
- Transitionen sind orientierend, ohne stoerende Spruenge.
- Bei `prefers-reduced-motion` sind Animationen reduziert/deaktiviert.

### 6.2 Formular-Usability Checks

Statusregel:
- **PASS**, wenn alle folgenden Punkte fuer das Kontaktformular erfuellt sind.
- **FAIL**, wenn ein Punkt verletzt ist.

Pruefpunkte:
1. `UX-FORM-02` Pflichtfeld-Transparenz:
- Erforderliche Felder sind sichtbar markiert und im DOM als `required` erkennbar.

2. `UX-FORM-03` Inline-Validierung:
- Fehlermeldungen erscheinen feldnah, konkret und nicht nur farbbasiert.

3. `UX-FORM-04` Fehlerfokus:
- Nach invalidem Submit springt Fokus zur Fehlerzusammenfassung oder zum ersten invaliden Feld.

4. `UX-FORM-05` Werterhalt:
- Korrekt ausgefuellte Felder bleiben nach invalidem Submit erhalten.

5. `UX-FORM-06` Erfolgsverhalten:
- Erfolgreicher Submit zeigt klare Bestaetigung und naechsten sinnvollen Schritt.

6. Keyboard-Flow:
- Tab-Reihenfolge ist logisch, Submit per Tastatur moeglich.

### 6.3 A11y-Akzeptanzkriterien (verbindlich, WCAG 2.1 AA-orientiert)

Statusregel:
- **PASS**, wenn alle folgenden Kriterien erfuellt und nachweisbar sind.
- **FAIL / NO-GO**, wenn ein Kriterium nicht erfuellt ist.

1. `A11Y-KB-01` Tastatur:
- Alle interaktiven Elemente per `Tab` erreichbar, kein Keyboard-Trap.

2. `A11Y-KB-02` Skip-Link:
- Erster Fokuspunkt und funktionaler Sprung in den Hauptinhalt.

3. `A11Y-KB-03` Mobile-Menue:
- Per Tastatur oeffn-/schliessbar, Fokusfluss korrekt.

4. `A11Y-FOCUS-01` Fokus sichtbar:
- Sichtbarer Fokus auf Links, Buttons, Inputs, Selects, Textareas.

5. `A11Y-FOCUS-02` Fokus-Kontrast:
- Fokusindikator min. 3:1 gegen angrenzende Farben und nicht verdeckt.

6. `A11Y-CONTRAST-01` Text-Kontrast normal:
- Mindestens 4.5:1.

7. `A11Y-CONTRAST-02` Text-Kontrast gross:
- Mindestens 3:1.

8. `A11Y-CONTRAST-03` UI/Fokus-Kontrast:
- Mindestens 3:1 fuer Komponentenränder/Fokusindikatoren.

9. `A11Y-SEM-01` Labels:
- Sichtbares Label je Feld, sauber verknuepft via `for`/`id`.

10. `A11Y-SEM-02` Formularstatus:
- Fehler-/Hilfetexte ueber `aria-describedby` verknuepft, Invalid-Status via `aria-invalid`.

11. `A11Y-SEM-03` Struktur:
- Genau eine `h1`, Landmarken `header/main/nav/footer` vorhanden.

12. `A11Y-SEM-04` Statusmeldungen:
- Erfolg/Fehler fuer Assistive Tech erkennbar (z. B. `aria-live`).

### 6.4 Traceability-Mapping Brief <-> QA

| Brief-ID (RDFA-46) | QA-Pruefschritt | Evidenz (Mindestnachweis) |
|---|---|---|
| UX-RESP-01 | 6.1.1 + 2.B | 3 Viewportscreenshots + ScrollWidth-Pruefung |
| UX-RESP-02 | 6.1.2 + 2.B | Mobile-Nav auf/zu Nachweis |
| UX-RESP-03 | 6.1.3 + 2.B | Kontaktbereich auf 360/768/1280 sichtbar |
| UX-FORM-01 | 6.1.4 | Groessenpruefung Formular-Controls |
| UX-FORM-02 | 6.2.1 | DOM-Nachweis `required` + sichtbare Pflichtmarkierung |
| UX-FORM-03 | 6.2.2 | Invalid-Submit mit feldnahen Fehlern |
| UX-FORM-04 | 6.2.3 | Fokusnachweis nach invalidem Submit |
| UX-FORM-05 | 6.2.4 | Werterhalt-Nachweis nach invalidem Submit |
| UX-FORM-06 | 6.2.5 | Erfolgsnachricht inkl. naechster Schritt |
| A11Y-KB-01 | 6.3.1 + 2.C | Keyboard-Smoke/Tab-Durchlauf |
| A11Y-KB-02 | 6.3.2 + 2.C | Skip-Link Fokus und Ziel |
| A11Y-KB-03 | 6.3.3 | Mobile-Menu per Tastatur |
| A11Y-FOCUS-01 | 6.3.4 + 2.C | Fokus-Screenshots |
| A11Y-FOCUS-02 | 6.3.5 | Kontrastmessung Fokusring |
| A11Y-CONTRAST-01 | 6.3.6 | Kontrastmessung normaler Text |
| A11Y-CONTRAST-02 | 6.3.7 | Kontrastmessung grosser Text |
| A11Y-CONTRAST-03 | 6.3.8 | Kontrastmessung UI/Fokus |
| A11Y-SEM-01 | 6.3.9 + 2.C | DOM-Pruefung `for/id` |
| A11Y-SEM-02 | 6.3.10 | DOM-Pruefung `aria-describedby/aria-invalid` |
| A11Y-SEM-03 | 6.3.11 + 2.C | Heading/Landmark-Check |
| A11Y-SEM-04 | 6.3.12 | Statusmeldung via `aria-live` |

### 6.5 Empfohlene Nachweisfuehrung (manuell + halbautomatisch)

Beispielhafte Kommandos fuer reproduzierbare Nachweise:

```bash
# A11y-Smoke (bereits vorhanden)
bash scripts/ci/accessibility-smoke.sh

# Keyboard-E2E (falls vorhanden)
bash scripts/ci/accessibility-keyboard-e2e.sh
```

Manuelle Pflichtdokumentation pro Release:
1. Screenshots 360/768/1280 fuer Start und Kontaktbereich.
2. Kurzes Protokoll fuer Tastaturdurchlauf (Skip-Link bis Submit).
3. Kontrastnachweis fuer Primartext, CTA-Button, Fokusindikator, Fehlermeldung.
