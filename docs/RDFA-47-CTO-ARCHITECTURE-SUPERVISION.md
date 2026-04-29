# RDFA-47 - CTO Architecture Supervision

Stand: 2026-04-29 (UTC)
Owner: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## 1) Ziel
Technische Architektur von RD Formstack Solutions aktiv steuern und absichern, mit Fokus auf:
- saubere Layer-Grenzen,
- sichere Request-Verarbeitung,
- stabile Datenpersistenz via PDO + Prepared Statements,
- getrennte Konfiguration ohne hardcodierte Secrets,
- kontrollierte Release-Gates ohne Deployment-Automatismus.

## 2) Aktueller Architekturstatus
Erfuellt:
- Front Controller (`public/index.php`) plus getrennte Module (`src/`, `config/`, `database/`).
- DB-Zugriffe via PDO mit `ERRMODE_EXCEPTION` und `ATTR_EMULATE_PREPARES=false`.
- Prepared Statements im Contact-Write-Path (`ContactRepository`).
- CSRF-Schutz und serverseitige Validierung im Kontaktprozess.
- Basis Security Headers zentral im Bootstrap.
- Keine hardcodierten Secrets; `.env` + `.env.example` vorhanden.

Offene Architekturthemen:
- V2-Layer (`Application`, `Domain`, `Infrastructure`) sind dokumentiert, aber noch nicht konsequent im Code umgesetzt.
- Contact-Flow ist derzeit direkt Controller -> Repository gekoppelt (fehlender Application-Service).

## 3) Sicherheitsrisiken (priorisiert)

### P1 - Fail-open Verhalten beim Rate Limiter
Befund:
- In `IpRateLimiter::consume()` wird bei I/O-/Lock-Fehlern `allowed=true` zurueckgegeben.
Risiko:
- Bei Storage-Problemen kann Throttling vollstaendig ausfallen.
Entscheidung:
- Auf kontrolliertes Degradationsverhalten umstellen: Fallback-Limiter in Memory pro Request ist nicht ausreichend; stattdessen explizites Incident-Logging + optional fail-closed fuer Kontakt-POSTs in Staging/Prod via Env-Flag.

### P1 - Keine Origin/Host-Hardening-Regel fuer Canonical/URL-Bildung
Befund:
- URL-Bildung basiert auf `HTTP_HOST`.
Risiko:
- SEO/Link-Poisoning und inkonsistente Metadaten bei manipuliertem Host-Header.
Entscheidung:
- `APP_BASE_URL` als kanonische Source of Truth einfuehren; `HTTP_HOST` nur fuer lokale Entwicklung/Fallback.

### P2 - Security Header Baseline unvollstaendig
Befund:
- `Strict-Transport-Security` fehlt; CSP ohne Reporting/Nonce-Strategie.
Risiko:
- Reduzierte Härtung in produktionsnahen Umgebungen.
Entscheidung:
- Environment-gesteuerte Header-Policy erweitern (HSTS nur unter TLS, CSP Report-Only Stufe fuer Einfuehrung).

### P2 - Keine strukturierte Security-Events im Logger
Befund:
- Es gibt `Logger::error`, aber keine standardisierten Security-Eventtypen.
Risiko:
- Schwache Incident-Triage (CSRF-Verstoss, Rate-Limit-Block, Input-Anomalien).
Entscheidung:
- Security-Event-Schema mit Event-ID und minimiertem Kontext einfuehren.

## 4) Architekturentscheidungen (wirksam ab RDFA-47)
- ADR-47.1: Kontaktprozess migriert auf `Application`-Service zwischen Controller und Repository.
- ADR-47.2: `APP_BASE_URL` wird Pflichtvariable fuer Staging/Produktion.
- ADR-47.3: Rate-Limiter darf nicht stillschweigend fail-open ohne Security-Log.
- ADR-47.4: Deployment bleibt gesperrt bis explizite Freigabe durch Owner/CTO.

## 5) Delivery-Gates
- Gate A (Code): PHP-Lint, Smoke-Routes, Contact-Flow-Tests, Security-Unit-Tests.
- Gate B (Security): Nachweis fuer Rate-Limiter-Degradation, Host-Hardening, Header-Policy.
- Gate C (Ops): Update Runbook + Rollback-Hinweis + Evidence-Artefakte.
- Gate D (Release): Kein Deployment ohne schriftliche Freigabe.

## 6) Nächste operative Schritte
- Umsetzungspaket an Lead Developer uebergeben (`docs/RDFA-47-DEVELOPER-AGENT-TASKS.md`).
- Nach Umsetzung CTO-Review gegen obige ADRs und Gates.
