# RDFA-49 - Lead Developer Tasks

Stand: 2026-04-29 (UTC)  
Owner: Lead Developer Agent  
Input: `docs/RDFA-49-CTO-ARCHITECTURE-SUPERVISION.md`

## Task 1 - Security-Event-Abdeckung erweitern
Ziel:
- Einheitliche, auswertbare Security-Telemetrie.

Umsetzung:
- Zusätzliche Security-Events in sicherheitskritischen Pfaden einbauen:
  - Validierungsablehnungen mit hoher Anomalie (ohne PII),
  - zentrale Error-Handling-Pfade,
  - potenzielle Request-Manipulationen.

Definition of Done:
- Event-Schema (`event_type`, `severity`, `request_id`, `context`) konsistent.
- Keine Klartext-Secrets/Message-Bodies/Passwörter in Logs.

## Task 2 - Header-Policy Regression-Checks
Ziel:
- HSTS/CSP-Konfiguration dauerhaft absichern.

Umsetzung:
- Test-/Smoke-Script ergänzen für:
  - `CSP_REPORT_ONLY=true` -> `Content-Security-Policy-Report-Only`,
  - `CSP_REPORT_ONLY=false` -> `Content-Security-Policy`,
  - `ENABLE_HSTS=true` unter HTTPS -> `Strict-Transport-Security`.

Definition of Done:
- Automatisierbare Nachweise als Artefakt im QA-Lauf.

## Task 3 - Host-Hardening Guardrail
Ziel:
- Keine Rückfälle auf untrusted Host-Header für absolute URLs.

Umsetzung:
- Review-Guardrail dokumentieren und prüfen:
  - absolute URL-Erzeugung nur über `APP_BASE_URL`,
  - neue Features prüfen auf direkte Nutzung von `HTTP_HOST`.

Definition of Done:
- Checklist-Eintrag vorhanden und in QA-Gate referenziert.

## Task 4 - Architecture DoD für neue mutierende Features
Ziel:
- Layer-Disziplin für Folgemodule verbindlich machen.

Umsetzung:
- Definition of Done dokumentieren:
  - Controller orchestriert HTTP, keine direkte Persistence.
  - Application-Service kapselt Use-Case.
  - Repository/DB nur Infrastructure.

Definition of Done:
- DoD in Entwicklerdoku verankert und für PR-Reviews nutzbar.

## Verifikation (Pflicht)
- `bash scripts/ci/php-lint.sh`
- bestehende Smoke-Checks
- neue Header-Checks
- Artefakte unter `docs/evidence/rdfa-49/`

## Release-Hinweis
- Kein Deployment ausführen.
- Nur Code, Doku und lokale Evidenz liefern.
