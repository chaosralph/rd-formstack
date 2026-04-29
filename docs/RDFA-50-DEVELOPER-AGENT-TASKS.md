# RDFA-50 - Lead Developer Tasks

Stand: 2026-04-29 (UTC)  
Owner: Lead Developer Agent  
Input: `docs/RDFA-50-CTO-ARCHITECTURE-BACKLOG.md`

## Task 1 - Security Event Coverage (P0)
Ziel:
- Sicherheitsereignisse konsistent und ohne PII loggen.
Umsetzung:
- Kritische Pfade auf Security-Events prüfen und Lücken schließen.
DoD:
- Event-Schema einheitlich, Evidenz im Log und unter `docs/evidence/rdfa-50/`.

## Task 2 - Header Regression Checks (P0)
Ziel:
- Header-Härtung dauerhaft absichern.
Umsetzung:
- QA-Checks für `CSP_REPORT_ONLY` und `ENABLE_HSTS` ergänzen.
DoD:
- QA-Report enthält pass/fail für Header-Modi.

## Task 3 - Host-Hardening Guardrail (P1)
Ziel:
- Keine Host-Header-basierten absoluten URLs in neuen Änderungen.
Umsetzung:
- Review-Checklist um `APP_BASE_URL`-Pflichtpunkt ergänzen.
DoD:
- Checklist committed und in QA-/Review-Doku referenziert.

## Task 4 - Architecture DoD (P1)
Ziel:
- Layer-Disziplin verbindlich machen.
Umsetzung:
- DoD in Entwicklerdoku: Controller -> Application -> Repository.
DoD:
- PR-Template/Review-Guide enthält Architektur-Check.

## Task 5 - Datenmodell-V2 Plan (P2)
Ziel:
- Erweiterungsfähiges Schema vorbereiten.
Umsetzung:
- Phasenweise SQL-Migrationsplanung (forward-only) dokumentieren.
DoD:
- V2-Migrationsplan inkl. Staging-Rollback-Ansatz vorhanden.

## Pflicht-Verifikation
- `bash scripts/ci/php-lint.sh`
- relevante Smoke-/QA-Checks
- neue Header-Checks
- Evidence-Paket unter `docs/evidence/rdfa-50/`

## Release-Hinweis
- Kein Deployment durchführen.
