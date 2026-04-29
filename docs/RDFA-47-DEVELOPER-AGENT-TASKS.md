# RDFA-47 - Lead Developer Tasks

Stand: 2026-04-29 (UTC)
Owner: Lead Developer Agent
Input: `docs/RDFA-47-CTO-ARCHITECTURE-SUPERVISION.md`

## Task 1 - Application Layer fuer Contact-Use-Case einfuehren
Ziel:
- Entkopplung Controller <-> Repository durch Service-Layer.

Umsetzung:
- `src/Application/Contact/SubmitContactService.php` anlegen.
- Validierte Eingaben als DTO/Array an Service uebergeben.
- Service orchestriert Persistenz via Repository.
- Controller reduziert auf HTTP/Session/Redirect-Verantwortung.

Definition of Done:
- Kein direkter Repository-Write-Aufruf mehr aus `ContactController::submit()`.
- Bestehendes Verhalten fuer Erfolg/Fehler bleibt funktional gleich.
- PHP-Lint erfolgreich.

## Task 2 - Host-Hardening und `APP_BASE_URL`
Ziel:
- Canonical/Sitemap-URLs nicht aus untrusted Host-Headern ableiten.

Umsetzung:
- Neue Env-Variable `APP_BASE_URL` (inkl. `.env.example`) einfuehren.
- URL-Erzeugung in `public/index.php` zentral auf trusted Base URL umstellen.
- Lokaler Fallback nur fuer DEV, dokumentiert.

Definition of Done:
- `canonical`, OpenGraph URL und Sitemap nutzen trusted Base URL.
- Kein regressiver Effekt auf lokale Entwicklung (`php -S`) ohne gesetzte Variable.

## Task 3 - Rate-Limiter Degradation absichern
Ziel:
- Kein stilles fail-open ohne Sichtbarkeit.

Umsetzung:
- Bei Lock-/Storage-Fehlern Security-Event schreiben.
- Env-Flag `RATE_LIMIT_FAIL_MODE` (`open|closed`) einfuehren.
- In `closed` Modus: Kontakt-POST ablehnen (429 oder 503 mit klarer User-Message).

Definition of Done:
- Mindestens ein Testfall je Modus (`open`, `closed`).
- Security-Logeintrag bei Limiter-I/O-Fehler nachweisbar.

## Task 4 - Security Header Policy erweitern
Ziel:
- Header-Härtung environment-sensitiv und nachvollziehbar machen.

Umsetzung:
- HSTS nur wenn HTTPS aktiv und Env dies erlaubt (`ENABLE_HSTS=true`).
- CSP optional als `Report-Only` Modus schaltbar (`CSP_REPORT_ONLY=true`).
- Header-Set zentral im Bootstrap halten.

Definition of Done:
- Header-Verhalten durch Env reproduzierbar.
- Dokumentationsupdate in `docs/ARCHITECTURE.md`.

## Task 5 - Security Event Logging standardisieren
Ziel:
- Incident-Triage verbessern ohne PII-Leak.

Umsetzung:
- `Logger` um `security()` erweitern mit Schema:
  - `event_type`, `severity`, `request_id`, `context` (minimiert, ohne Roh-PII).
- Nutzung in CSRF-Invalid und Rate-Limit-Block-Pfaden.

Definition of Done:
- Security-Events erscheinen strukturiert in `storage/logs/app.log`.
- Keine Klartext-Passwoerter/Secrets/Message-Body im Event-Kontext.

## Verifikation (Pflicht)
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/smoke-routes.sh`
- vorhandene Kontakt-Tests plus neue Rate-Limiter-Falltests
- Artefakt-Protokoll in `docs/evidence/rdfa-47/`

## Release-Hinweis
- Kein Deployment ausfuehren.
- Nur Code + Doku + lokale Evidenz liefern.
