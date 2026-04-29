# RDFA-46 QA/DevOps Gate

## Ziel
Der QA-Gate stellt sicher, dass Pull Requests und Main-Builds ohne Production-Deployment nur bei stabilen Basischecks weiterlaufen.

Pflichtumfang des Gates:
- Lint: `bash scripts/ci/php-lint.sh`
- Smoke: `bash scripts/ci/smoke-routes.sh`
- A11y-Smoke: `bash scripts/ci/accessibility-smoke.sh`

Ausfuehrung ueber:
- `bash scripts/ci/qa-gate.sh`
- oder `composer run check:qa-gate`

## Gate-Verhalten
- Der Gate laeuft im `set -euo pipefail`-Modus.
- Pflichtchecks werden konsistent in fester Reihenfolge ausgefuehrt.
- Fehler in einem Pflichtcheck fuehren zu sofortigem Abbruch (Fail-Fast).
- Bei Fail wird `exit 1` gesetzt, bei erfolgreichem Lauf `exit 0`.
- Der Gate erzeugt immer einen Abschlussreport via `trap` (auch bei Fehlern).
- Konfigurationsflags werden strikt validiert; erlaubt sind nur `0` oder `1`.

## Konfigurierbare Flags
- `RD_QA_STRICT` (Default `1`): Strikter Modus.
- `RD_QA_RUN_A11Y_SMOKE` (Default `1`): A11y-Smoke aktivieren/deaktivieren.
- `RD_QA_RUN_RESPONSIVE` (Default `0`): Optionales Responsive-Evidence-Skript.

Hinweise:
- Wenn `RD_QA_RUN_A11Y_SMOKE=0` und `RD_QA_STRICT=1`, schlaegt der Gate fehl.
- Ungueltige Flag-Werte (nicht `0`/`1`) fuehren deterministisch zu Fail.

## Evidence-Artefakte
Alle Gate-Artefakte liegen unter `artifacts/qa/gate/`.

Pflicht-Evidence:
- `artifacts/qa/gate/evidence/php-lint.log`
- `artifacts/qa/gate/evidence/route-smoke.log`
- `artifacts/qa/gate/evidence/accessibility-smoke.log` (bei aktivem A11y-Smoke)

Gesamtreport:
- `artifacts/qa/gate/report.txt`

Report-Inhalte:
- Start-/Endzeit (UTC)
- Gesamtstatus (`PASS`/`FAIL`)
- Konfigurationsflags
- Warnungszaehler
- Fehlgeschlagener Check (falls vorhanden)
- Liste der Pflicht- und optionalen Checks inkl. Artefaktpfade
- `check_results` mit Status je Check (`PASS`/`FAIL`/`WARN`/`SKIP`)

Optionales Evidence bei Responsive-Run:
- `artifacts/qa/gate/evidence/responsive-evidence.log`
- sowie Artefakte aus `artifacts/qa/responsive/`

## CI-Integration
Der Workflow `.github/workflows/required-checks.yml` enthaelt einen dedizierten Job `qa_gate`, der den Gate zentral ausfuehrt.

Wichtig:
- Keine Secrets fuer den Gate erforderlich.
- Keine Deployment-Jobs enthalten.
