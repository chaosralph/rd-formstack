# RDFA-48 QA/DevOps Validation

Stand: 2026-04-29 (UTC)
Rolle: QA/DevOps
Scope: Gate-Haertung, Required-Checks-Fokus, Nachweisdokumentation

## 1) Umgesetzte Aenderungen

1. `scripts/ci/qa-gate.sh`
- Deterministische CLI-Flag-Verarbeitung hinzugefuegt (`--strict`, `--run-a11y-smoke`, `--run-responsive`, `--help`).
- Harte Validierung auf binaere Werte `0|1`.
- Unbekannte Flags erzeugen sofortigen Abbruch mit klarer Fehlermeldung.
- Fail-fast fuer Pflichtchecks bleibt aktiv; Report wird ueber `trap` auch bei Fehlern geschrieben.
- Fehlermeldungen und Hinweise sprachlich vereinheitlicht.

2. `.github/workflows/required-checks.yml`
- Required-Workflow auf einen Gate-zentrierten Job reduziert: `qa-gate`.
- Keine Deployment-Jobs hinzugefuegt.
- Strict-Run via Env-Konfiguration erzwungen.

3. `docs/qa-checklist.md`
- RDFA-48 Update mit deterministischem Runbook, gueltigen Flags und Fail-fast-Regeln ergaenzt.

## 2) Lokale Ausfuehrungsbefehle (Nachweis)

```bash
# Hilfe / gueltige Flags
bash scripts/ci/qa-gate.sh --help

# Standard-Strict-Run (CI-aehnlich)
bash scripts/ci/qa-gate.sh --strict=1 --run-a11y-smoke=1 --run-responsive=0

# Optional: Responsive Evidence zusaetzlich laufen lassen
bash scripts/ci/qa-gate.sh --strict=1 --run-a11y-smoke=1 --run-responsive=1

# Negativtest: ungueltiger Flag-Wert (muss fehlschlagen)
bash scripts/ci/qa-gate.sh --strict=2

# Negativtest: unbekanntes Flag (muss fehlschlagen)
bash scripts/ci/qa-gate.sh --unknown
```

Artefakte:
- Report: `artifacts/qa/gate/report.txt`
- Evidenzlogs: `artifacts/qa/gate/evidence/*.log`

## 3) Verbleibende Risiken

1. Browser-/Display-Abhaengigkeit fuer Responsive Evidence
- Risiko: `RUN_RESPONSIVE=1` haengt an Playwright/Browser-Verfuegbarkeit in der Umgebung.
- Auswirkung: Im Strict-Standardlauf kein Gate-Blocker (optional), aber fehlende visuelle Nachweise.

2. Accessibility-Smoke deckt nur Basisszenarien ab
- Risiko: Script prueft Smoke-Level, nicht vollstaendige WCAG-AA-Tiefe.
- Auswirkung: Rest-Risiko fuer Edge-Cases in AT-/Keyboard-Flows bleibt.

3. Gate-Single-Job verdichtet Fehlersignale
- Risiko: Weniger granulare Required-Checks im PR-UI.
- Auswirkung: Dafuer eindeutiges Go/No-Go-Signal; Detaildiagnose erfolgt ueber Gate-Logfiles.

## 4) Freigabeaussage (Scope RDFA-48)

- Ziel 1 (deterministische Gate-Haertung): erreicht.
- Ziel 2 (Required-Checks auf Gate-Fokus, ohne Deployment-Jobs): erreicht.
- Ziel 3 (Risiken, Nachweise, lokale Befehle): dokumentiert.
