# RDFA-51 - Security Threat Model

Stand: 2026-04-29 (UTC)  
Owner: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## Scope
- PHP-Anwendung mit Front Controller, Session, Formular-Submit, PDO/MySQL.
- Fokus auf Web-Risiken, Konfiguration, Logging und Release-Governance.

## Assets
- Kontaktanfragedaten (`name`, `company`, `email`, `phone`, `message`)
- Session-Integrität
- Konfigurationswerte und Secrets (nur Environment)
- Verfügbarkeit des Kontaktformulars
- Integrität der Logs

## Bedrohungen und Kontrollen

## T1 - SQL Injection
Risiko:
- Manipulierte Eingaben in DB-Queries.
Kontrolle:
- PDO + Prepared Statements verpflichtend.
Rest-Risiko:
- Niedrig, sofern keine String-Konkatenation eingeführt wird.

## T2 - CSRF bei mutierenden Requests
Risiko:
- Unerlaubte Submit-Aktionen über fremde Seiten.
Kontrolle:
- CSRF-Token-Prüfung bei POST.
Rest-Risiko:
- Niedrig bis mittel (abhängig von konsistenter Anwendung).

## T3 - Rate-Limit-Bypass/Degradation
Risiko:
- Spam oder Missbrauch bei Limiter-I/O-Problemen.
Kontrolle:
- `RATE_LIMIT_FAIL_MODE`, Security-Events bei Degradation.
Rest-Risiko:
- Mittel (operativ, wenn Fail-Mode falsch gesetzt oder nicht überwacht).

## T4 - Host Header Manipulation
Risiko:
- Falsche absolute URLs (SEO/Link-Poisoning).
Kontrolle:
- `APP_BASE_URL` als trusted Source.
Rest-Risiko:
- Mittel (wenn Folgefeatures Guardrail ignorieren).

## T5 - Sensitive Data Exposure in Logs
Risiko:
- PII/Secrets in Klartext-Logs.
Kontrolle:
- Strukturiertes Security-Logging mit minimiertem Kontext.
Rest-Risiko:
- Mittel (abhängig von Review-Disziplin).

## T6 - Header Hardening Regressions
Risiko:
- Fehlende CSP/HSTS in produktionsnahen Umgebungen.
Kontrolle:
- Env-gesteuerte Header-Policy.
Rest-Risiko:
- Mittel bis hoch ohne automatische Regression-Checks.

## T7 - Unkontrolliertes Deployment
Risiko:
- Ungeprüfte Änderungen in Produktion.
Kontrolle:
- CTO-Gate: kein Deployment ohne Freigabe.
Rest-Risiko:
- Niedrig bei Prozessdisziplin.

## Priorisierte Maßnahmen
1. P0: Automatisierte Header-Regressionstests.
2. P0: Vollständige Security-Event-Abdeckung kritischer Pfade.
3. P1: `APP_BASE_URL`-Guardrail in Review/QA.
4. P1: Architektur-DoD für neue mutierende Flows.

## Verknüpfung
- Backlog: `docs/RDFA-50-CTO-ARCHITECTURE-BACKLOG.md`
- Lead Tasks: `docs/RDFA-50-DEVELOPER-AGENT-TASKS.md`
- Review: `docs/RDFA-49-CTO-REVIEW-TEMPLATE.md`
