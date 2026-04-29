# RDFA-48 - CTO Architecture Review gegen RDFA-47

Stand: 2026-04-29 (UTC)
Owner: CTO
Scope: Architektur-Review ohne Deployment, ohne Secrets
Baseline: `docs/RDFA-47-CTO-ARCHITECTURE-SUPERVISION.md`

## 1) Ergebnis
Status: NO-GO (Architektur-Freigabe nicht erteilt)

Begruendung:
- Mehrere P1/P2-Abweichungen zu RDFA-47 sind offen.
- Harte Freigabebedingungen (Gate B/Gate C) sind aktuell nicht nachgewiesen.

## 2) Soll-Ist-Abweichungen (RDFA-47 -> Ist-Code)

### A1 - Fehlender Application-Service im Contact-Flow (P1)
Soll (ADR-47.1):
- Controller darf nicht direkt in Repository schreiben; Service-Layer dazwischen.

Ist:
- Direkter Write im Controller: `ContactController::submit()` ruft `repository->create(...)` direkt auf.
- Fundstelle: `src/Http/ContactController.php:78`

Risiko:
- Verletzte Layer-Grenzen, erschwerte Testbarkeit, hohes Regressionsrisiko bei Fachlogik-Aenderungen.

### A2 - Kein `APP_BASE_URL`-Hardening fuer Canonical/Sitemap/Structured Data (P1)
Soll (ADR-47.2):
- Trusted Base URL via `APP_BASE_URL` fuer Staging/Produktion verpflichtend.

Ist:
- URL-Bildung basiert auf `HTTP_HOST` und Scheme aus Request.
- Fundstellen:
  - `public/index.php:44`
  - `public/index.php:47`
  - `public/index.php:112`
  - `public/index.php:124`
  - `public/index.php:130`
- `.env.example` enthaelt kein `APP_BASE_URL`.
- Fundstelle: `.env.example:1`

Risiko:
- Host-Header-Poisoning, inkonsistente SEO-Metadaten, untrusted Canonical/OG/Sitemap-Links.

### A3 - Rate-Limiter fail-open ohne Security-Event und ohne Fail-Mode (P1)
Soll (ADR-47.3):
- Kein stilles fail-open; Security-Logging verpflichtend, steuerbares Verhalten (`open|closed`).

Ist:
- Bei `fopen`/`flock`-Fehler `allowed=true` ohne Logging.
- Fundstellen:
  - `src/Security/IpRateLimiter.php:31-33`
  - `src/Security/IpRateLimiter.php:35-38`
- Kein `RATE_LIMIT_FAIL_MODE` im Code/.env.

Risiko:
- Vollstaendiger Ausfall des Throttlings bei I/O-Problemen ohne operative Sichtbarkeit.

### A4 - Header-Policy nicht env-gesteuert (P2)
Soll:
- HSTS nur bei TLS und explizitem Env-Flag; CSP optional `Report-Only`.

Ist:
- Feste CSP als Enforce, kein HSTS, keine Env-Flags fuer Header-Policy.
- Fundstelle: `src/Bootstrap/AppBootstrap.php:23-27`

Risiko:
- Zu geringe Haertung in produktionsnahen Umgebungen und fehlender stufenweiser CSP-Rollout.

### A5 - Kein standardisiertes Security-Event-Logging (P2)
Soll:
- Strukturierte Security-Events (`event_type`, `severity`, `request_id`, minimierter Kontext).

Ist:
- Nur `Logger::error(...)`, keine `security(...)` API und keine Eventtypen.
- Fundstelle: `src/Support/Logger.php:9-24`
- CSRF/Rate-Limit-Pfade schreiben keine Security-Events.
- Fundstelle: `src/Http/ContactController.php:86-103`

Risiko:
- Incident-Triage und forensische Nachvollziehbarkeit unzureichend.

## 3) Harte Freigabebedingungen (muss vollstaendig erfuellt sein)

1. ADR-47.1 umgesetzt:
- Contact-Write nur ueber Application-Service.
- Kein direkter Repository-Write in `ContactController::submit()`.

2. ADR-47.2 umgesetzt:
- `APP_BASE_URL` in `.env.example` vorhanden.
- Canonical, OpenGraph/Structured Data URL und Sitemap nutzen trusted Base URL.
- Fallback auf Host nur fuer lokale Entwicklung dokumentiert.

3. ADR-47.3 umgesetzt:
- `RATE_LIMIT_FAIL_MODE=open|closed` implementiert.
- Bei I/O-/Lock-Fehlern verpflichtender Security-Log mit Request-ID.
- Testnachweise fuer beide Modi (`open`, `closed`) vorhanden.

4. Header-Policy umgesetzt:
- `ENABLE_HSTS` und `CSP_REPORT_ONLY` env-gesteuert.
- HSTS nur unter TLS aktiv.

5. Security-Event-Schema umgesetzt:
- `Logger::security(...)` mit standardisiertem Feldschema.
- Nutzung mindestens in CSRF-invalid und Rate-Limit-Block/Fehler-Pfaden.

6. Gate-Evidence vorhanden:
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/smoke-routes.sh`
- Kontakt- und neue Rate-Limiter-Tests erfolgreich.
- Evidence-Paket unter `docs/evidence/rdfa-47/`.

7. Governance:
- Kein Deployment.
- Keine Secrets im Repo.

## 4) Freigabeentscheidung
- Architektur-Freigabe: NO-GO bis alle harten Bedingungen aus Abschnitt 3 nachweislich erfuellt sind.
- Re-Review unmittelbar nach Vorlage der Evidence-Artefakte.
