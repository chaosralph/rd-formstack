# RDFA-49 - CTO Architecture Supervision

Stand: 2026-04-29 (UTC)  
Owner: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## 1) Ziel
Architektur von RD Formstack Solutions operativ führen und absichern:
- klare Layer-Grenzen (Presentation/Application/Infrastructure),
- sichere Request-Verarbeitung,
- PDO + Prepared Statements als Pflicht,
- getrennte Konfiguration ohne Secrets im Code,
- keine Produktionsauslieferung ohne Freigabe.

## 2) Architekturstatus
Bereits umgesetzt:
- Contact-Write-Pfad nutzt Application-Service zwischen Controller und Repository.
- Canonical/Sitemap-URLs nutzen `APP_BASE_URL` als trusted Source.
- Rate-Limiter-Degradation ist env-gesteuert (`RATE_LIMIT_FAIL_MODE=open|closed`) und schreibt Security-Events.
- Header-Policy ist env-gesteuert (`ENABLE_HSTS`, `CSP_REPORT_ONLY`).
- Security-Event-Logging ist strukturiert vorhanden.

Noch offen:
- Einheitliche Nutzung des Security-Event-Schemas in allen sicherheitsrelevanten Pfaden.
- Formale Testabdeckung für Header-Policy und Host-Hardening in CI-Checks.
- Architekturregelwerk für neue Features (z. B. Admin- oder Content-Module) als verbindliche Definition of Done.

## 3) Sicherheitsrisiken (priorisiert)

### P1 - Unvollständige Security-Event-Abdeckung
Risiko:
- Sicherheitsrelevante Ereignisse können in einzelnen Pfaden nicht konsistent triagiert werden.
Maßnahme:
- Security-Events für Validation-Denials, ungewöhnliche Request-Muster und Error-Handler ergänzen.

### P1 - `APP_BASE_URL`-Disziplin in Folgefeatures
Risiko:
- Neue Features könnten wieder `HTTP_HOST` für absolute URLs nutzen.
Maßnahme:
- Guardrail in Review-Checklist und Architektur-DoD: absolute URLs nur über trusted Base URL.

### P2 - Header-Policy ohne automatisierte Regression-Tests
Risiko:
- HSTS/CSP-Modi können unbemerkt regressieren.
Maßnahme:
- Smoke-Test für Response-Header je Modus (`CSP_REPORT_ONLY`, `ENABLE_HSTS`) ergänzen.

## 4) CTO-Entscheidungen (wirksam ab RDFA-49)
- ADR-49.1: Für jeden neuen mutierenden Flow ist Application-Service Pflicht.
- ADR-49.2: Absolute URLs dürfen nur auf Basis `APP_BASE_URL` generiert werden.
- ADR-49.3: Jede Security-Degradation oder Security-Block-Entscheidung muss als Security-Event geloggt werden.
- ADR-49.4: Kein Deployment ohne explizite Owner/CTO-Freigabe.

## 5) Delivery-Gates
- Gate A (Code): PHP-Lint + bestehende Funktionstests + neue Header/Host-Checks.
- Gate B (Security): Nachweis Security-Event-Coverage und Fail-Mode-Verhalten.
- Gate C (Ops): Doku-Update (`ARCHITECTURE`, QA-Checklist, Evidence).
- Gate D (Release): Deployment weiterhin gesperrt ohne Freigabe.

## 6) Nächste Schritte
- Umsetzungspaket an Lead Developer: `docs/RDFA-49-DEVELOPER-AGENT-TASKS.md`.
- CTO-Review nach Abschluss gegen ADR-49.1 bis ADR-49.4.
