# RDFA-52 - Lead Developer Tasks (Security Scan Follow-up)

Stand: 2026-04-29 (UTC)  
Owner: Lead Developer Agent  
Input: `docs/RDFA-52-SECURITY-SCAN-REPORT.md`

## Task 1 - Architekturpfad-Konsolidierung (P1)
Ziel:
- Architektur-Drift reduzieren und Security-Policy zentral halten.
Umsetzung:
- Zielpfad festlegen (`src/Http` als primärer Web-Entry-Layer).
- Überlappende Logik in `src/Controller/*` und `src/Support/*` prüfen und deprecaten/zusammenführen.
Definition of Done:
- Konsolidierungsplan dokumentiert.
- Redundante Pfade markiert oder bereinigt.

## Task 2 - `APP_BASE_URL` Env-Guard für Staging/Prod (P1)
Ziel:
- Fehlkonfigurationen in nicht-dev Umgebungen verhindern.
Umsetzung:
- Bootstrap-/Startup-Guard einbauen:
  - wenn `APP_ENV` in `staging|production` und `APP_BASE_URL` fehlt -> harter Startfehler.
Definition of Done:
- Guard implementiert.
- Positiv/Negativ-Testfall dokumentiert.

## Task 3 - QA-Guard gegen direkte DB-Writes ohne `prepare()` (P1)
Ziel:
- Prepared-Statement-Disziplin dauerhaft sichern.
Umsetzung:
- QA-Check hinzufügen, der neue riskante Muster (`->query`, `->exec` für Writes) meldet.
Definition of Done:
- QA-Check läuft im lokalen QA-Gate.
- False-Positive-Regel dokumentiert.

## Pflicht-Verifikation
- `bash scripts/ci/php-lint.sh`
- relevante Smoke-/QA-Checks
- Evidence unter `docs/evidence/rdfa-52/`

## Release-Hinweis
- Kein Deployment durchführen.
