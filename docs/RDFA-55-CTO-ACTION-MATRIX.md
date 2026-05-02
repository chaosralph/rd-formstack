# RDFA-55 - CTO Action Matrix

Stand: 2026-04-29 (UTC)  
Owner: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## Ziel
Verbindliche Zuordnung von Sicherheits-/Architekturrisiken zu konkreten
Umsetzungen, Verantwortlichkeiten und Nachweisen.

| Risiko | Priorität | Maßnahme | Owner | Nachweis |
|---|---|---|---|---|
| Architektur-Drift durch Parallelpfade (`src/Http` vs `src/Controller`/`src/Support`) | P1 | Pfad-Konsolidierungsplan + Deprecation | Lead Developer | `docs/evidence/rdfa-53/` + PR-Diff |
| Fehlkonfiguration `APP_BASE_URL` in staging/prod | P1 | Env-Guard im Bootstrap (bereits eingeführt) + Regression-Nachweis | Lead Developer | QA-Gate Artefakt + Laufprotokoll |
| Direkte DB-Write-Muster ohne `prepare()` | P1 | QA-Guard-Skript (bereits eingeführt) + laufende CI-Auswertung | Lead Developer | `artifacts/qa/gate/evidence/db-write-prepare-guard.log` |
| Unvollständige Security-Event-Coverage | P1 | Event-Coverage auf kritischen Pfaden vervollständigen | Lead Developer | `storage/logs/app.log` Stichprobe + Testevidence |
| Header-Hardening Regression | P1 | Header/Host-Regressionstests regelmäßig ausführen | Lead Developer | `artifacts/qa/gate/evidence/header-host-regression.log` |
| Ungesteuertes Release | P0 | Manuelles Freigabe-Gate (kein Auto-Deploy) | CTO/Owner | Freigabeprotokoll |

## Governance
- Kein Deployment ohne explizite Freigabe.
