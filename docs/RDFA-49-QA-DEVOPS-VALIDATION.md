# RDFA-49 QA/DevOps Validation (Header/Host Regression)

Datum (UTC): 2026-04-29
Scope:
- `scripts/ci/header-host-regression.sh`
- `scripts/ci/qa-gate.sh`
- `.github/workflows/required-checks.yml`

## Ziel

Integration neuer Header/Host-Regression-Checks in den QA-Gate-Flow mit deterministischem, fail-fast Verhalten.

## Implementierte Aenderungen

1. Neuer Pflichtcheck: `scripts/ci/header-host-regression.sh`
- Startet lokalen PHP-Server auf `127.0.0.1:8096`.
- Prueft fuer `/` und `/kontakt` den HTTP-Status und Pflicht-Security-Header.
- Prueft fuer `/sitemap.xml` den `Content-Type` auf `application/xml`.
- Fuehrt Host-Regression-Test mit `Host: evil.example` und `X-Forwarded-Host: evil.example` aus.
- FAIL bei Reflektion von `evil.example` in `Location` oder Response-Body.

2. QA-Gate Integration: `scripts/ci/qa-gate.sh`
- Neuer required script check fuer `scripts/ci/header-host-regression.sh`.
- Neuer required run step: `Header Host Regression`.
- Verhalten bleibt fail-fast: bei erstem Fehler Exit `1`.

3. Required Workflow: `.github/workflows/required-checks.yml`
- Minimal angepasst: Step-Name praezisiert, dass Header/Host-Checks im strict QA-Gate enthalten sind.
- Keine Deployment-Logik hinzugefuegt.

## Lokale Verifikation

1. Einzelcheck
```bash
bash scripts/ci/header-host-regression.sh
```
Erwartung PASS:
- Exit `0`
- Ausgabe enthaelt `Header/Host regression checks passed.`

2. Voller QA-Gate-Run
```bash
bash scripts/ci/qa-gate.sh --strict=1 --run-a11y-smoke=1 --run-responsive=0
```
Erwartung PASS:
- Exit `0`
- Ausgabe enthaelt `QA Gate Result: PASS`
- Report: `artifacts/qa/gate/report.txt`
- Evidence Logs inkl.:
  - `artifacts/qa/gate/evidence/php-lint.log`
  - `artifacts/qa/gate/evidence/route-smoke.log`
  - `artifacts/qa/gate/evidence/header-host-regression.log`
  - `artifacts/qa/gate/evidence/accessibility-smoke.log`

## PASS/FAIL Kriterien

PASS:
- Alle required checks im QA-Gate sind erfolgreich.
- Header/Host-Regression-Check findet keine Regression.

FAIL:
- Ein required check fehlschlaegt oder liefert Exit ungleich `0`.
- Header/Host-Check findet fehlende Pflichtheader, falsche Statuscodes oder Host-Reflektion.

## Blocker

- Kein technischer Blocker bei der Implementierung.
- Initial war die Zieldatei `docs/RDFA-49-QA-DEVOPS-VALIDATION.md` im Repo noch nicht vorhanden; wurde im Rahmen dieses Auftrags erstellt.
