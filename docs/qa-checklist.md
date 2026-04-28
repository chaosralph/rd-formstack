# QA Gate P0 - Neue Website (RD Formstack Solutions)

Stand: 2026-04-28 (UTC)
Scope: Landingpage + Kontaktformular (`public/index.php`, `public/assets/*`, `src/Http/ContactController.php`)

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
