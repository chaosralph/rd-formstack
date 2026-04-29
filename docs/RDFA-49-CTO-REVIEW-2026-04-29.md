# RDFA-49 - CTO Review (2026-04-29)

Stand: 2026-04-29 (UTC)  
Reviewer: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## CTO Urteil
- Entscheidung: **NO-GO (Nacharbeit erforderlich)**.
- Grund: ADR-49.1 und ADR-49.3 sind in der aktuell aktiven Umsetzung nicht belastbar abgeschlossen.
- Deployment: nicht durchgeführt (konform zu ADR-49.4).

## ADR Compliance Check

### ADR-49.1 - Application-Service Pflicht bei mutierenden Flows
- Nachweis vorhanden: **Teilweise / Fail für Runtime-Pfad**
- Geprüfte Dateien:
  - `public/index.php`
  - `src/Controller/ContactController.php`
  - `src/Application/Contact/ContactSubmissionService.php`
- Befund:
  - Positiv: POST-Flow `contact.submit` läuft formal über Controller -> Service -> Repository.
  - Kritisch: In `public/index.php` wird `App\Application\Contact\ContactSubmissionService` instanziiert, die Klasse liegt zwar in `src/Application/Contact/ContactSubmissionService.php`, deklariert aber `namespace App\Application;`.
  - Ergebnis: PSR-4/Runtime-Mismatch im aktiven Pfad; damit ist der Service-Pfad nicht verlässlich nachgewiesen.
- Harte Bedingung zur Freigabe:
  - Namespace/Autoload-Konsistenz herstellen und mit reproduzierbarem Kontakt-POST-Smoke-Test als grün nachweisen.

### ADR-49.2 - Absolute URLs nur über `APP_BASE_URL`
- Nachweis vorhanden: **Ja, mit Rest-Risiko**
- Geprüfte Dateien:
  - `public/index.php`
  - `src/Support/AppUrl.php`
  - `templates/layout.php`
  - `.env.example`
- Befund:
  - Canonical, Structured Data und Sitemap nutzen `AppUrl::absolute(...)` auf Basis von `AppUrl::baseUrl(...)`.
  - `APP_BASE_URL` ist in `.env.example` vorhanden.
  - Rest-Risiko: Fallback auf Host/Scheme bleibt möglich, wenn `APP_BASE_URL` fehlt/ungültig; für Staging/Prod organisatorisch abzusichern.
- Harte Bedingung zur Freigabe:
  - CI/Smoke-Guardrail ergänzen, der bei Staging/Prod ohne valide `APP_BASE_URL` fehlschlägt.

### ADR-49.3 - Security-Degradation/-Block muss Security-Event erzeugen
- Nachweis vorhanden: **Teilweise / Fail für Header-Policy-Anforderung**
- Geprüfte Dateien:
  - `src/Security/IpRateLimiter.php`
  - `src/Controller/ContactController.php`
  - `src/Support/SecurityHeaderPolicy.php`
  - `scripts/tests/contact-rate-limit-fail-mode-test.php`
- Befund:
  - Positiv: Rate-Limiter-Degradation loggt Security-Event (`rate_limiter_degrade`) und ist fail-open/closed steuerbar.
  - Kritisch: HSTS wird in `SecurityHeaderPolicy::apply()` ohne TLS-Gate gesetzt, sobald Flag aktiv ist; das widerspricht der Architekturvorgabe "HSTS nur unter TLS".
  - Kritisch: Kein nachgewiesener Regressionstest für Header-Policy-Modi (CSP Report-Only vs CSP Enforce, HSTS nur bei HTTPS).
- Harte Bedingung zur Freigabe:
  - TLS-Gate in der wirksamen Header-Policy sicherstellen und automatisiert testen.

### ADR-49.4 - Kein Deployment ohne Freigabe
- Nachweis vorhanden: **Ja**
- Befund:
  - Workflow führt nur QA-Checks aus (`.github/workflows/required-checks.yml`), keine Deploy-Jobs.
  - Projektdoku fordert weiterhin explizit "kein Produktionsdeployment".

## Sicherheitsrisiken Re-Check

### P1 - Security-Event-Abdeckung
- Status: **Teilweise**
- Befund:
  - Rate-Limiter-Degrade-Event vorhanden und testbar.
  - Header-Policy-Degradation/Fehlkonfiguration ist nicht gleichwertig robust abgedeckt.
- Rest-Risiko: Fehlkonfigurierte Security-Header können unentdeckt in nicht-TLS-Kontexten aktiv werden.

### P1 - `APP_BASE_URL`-Disziplin
- Status: **Teilweise**
- Befund:
  - Hauptpfade nutzen `AppUrl` zentral.
  - Harte Umgebungskontrolle (Staging/Prod) als Test-/Gate-Kriterium fehlt.
- Rest-Risiko: Host-Header/Fallback-Verhalten kann in Folgefeatures wieder einwandern.

### P2 - Header-Policy Regression-Tests
- Status: **Offen**
- Befund:
  - Kein dedizierter CI-Test für Header-Matrix (`CSP_REPORT_ONLY`, `ENABLE_HSTS`, HTTPS-abhängiges Verhalten).
- Rest-Risiko: Sicherheitsregressionen bleiben bis zum manuellen Review unentdeckt.

## Gate-Prüfung

### Gate A - Code
- `bash scripts/ci/php-lint.sh`: **Pass**
- Smoke-/Funktionstests: **Teilweise Pass** (`scripts/tests/contact-rate-limit-test.php`, `scripts/tests/contact-rate-limit-fail-mode-test.php` grün)
- Neue Header/Host-Checks: **Fail** (fehlend)

### Gate B - Security
- Fail-Mode-Nachweis: **Pass**
- Security-Event-Nachweis: **Teilweise Pass**
- Host-Hardening-Nachweis: **Teilweise/Fail** (ohne harte Staging/Prod-Guardrails)

### Gate C - Ops
- Doku-Updates vollständig: **Ja (dieses Review)**
- Evidence vollständig: **Teilweise** (Header-/Host-Regressionsevidence fehlt)

### Gate D - Release
- Deployment durchgeführt: **Nein (erwartet)**
- Freigabe vorhanden: **Nein**

## Harte Freigabebedingungen (GO nur wenn alle erfüllt)
1. Namespace/Autoload-Mismatch im aktiven Kontakt-POST-Pfad beheben und per reproduzierbarem End-to-End-Smoke nachweisen.
2. HSTS in der wirksamen Header-Policy strikt an TLS binden; Nachweis über automatisierten Testfall.
3. Header-Regression-Checks in CI ergänzen:
   - `CSP_REPORT_ONLY=true` -> nur `Content-Security-Policy-Report-Only`
   - `CSP_REPORT_ONLY=false` -> `Content-Security-Policy`
   - `ENABLE_HSTS=true` + HTTPS -> `Strict-Transport-Security`
   - `ENABLE_HSTS=true` + HTTP -> **kein** `Strict-Transport-Security`
4. `APP_BASE_URL`-Guardrail für Staging/Prod erzwingen (Build-/Startup- oder QA-Gate-Fail bei fehlender/ungültiger Konfiguration).

## Prüfbarkeit / Nachweise
- Ausgeführte Checks (lokal, 2026-04-29 UTC):
  - `bash scripts/ci/php-lint.sh` -> Pass
  - `php scripts/tests/contact-rate-limit-fail-mode-test.php` -> Pass
  - `php scripts/tests/contact-rate-limit-test.php` -> Pass
- Code-Nachweise:
  - Aktiver POST-Flow: `public/index.php` (POST `contact.submit` + Service-Instanziierung)
  - Service-Namespace: `src/Application/Contact/ContactSubmissionService.php`
  - Degrade-Logging: `src/Security/IpRateLimiter.php`
  - Header-Policy: `src/Support/SecurityHeaderPolicy.php`
  - URL-Building: `src/Support/AppUrl.php`
  - Template-Nutzung Canonical/OG: `templates/layout.php`

## Sign-off
- Datum/Sign-off: 2026-04-29 (UTC) - CTO Agent
