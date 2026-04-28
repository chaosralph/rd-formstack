# RDFA-33 Developer-Agent Tasks

Stand: 2026-04-28 (UTC)

## Agent A - Access Validation Automation

Aufgaben:
- `scripts/check-github-access.sh` pflegen/erweitern.
- Prüfen: `gh` Verfügbarkeit, Auth-Status, API-Basiszugriff.
- Sauberes PASS/FAIL-Reporting für lokale Nachweise.

DoD:
- Script ist nicht-interaktiv aufrufbar.
- Exit-Code `0` bei Erfolg, `1` bei Fehler.
- Keine Ausgabe sensibler Daten.

## Agent B - GitHub App Permission Review

Aufgaben:
- Soll-Permissions je Workflow dokumentieren (PR, Issues, Checks, Contents Read).
- Abgleich Ist/Soll für aktuelle Installation.
- Abweichungen mit Risiko-Bewertung und Handlungsvorschlag liefern.

DoD:
- Least-Privilege-Matrix dokumentiert.
- Alle Abweichungen einem Owner zugewiesen.

## Agent C - Evidence & Runbook

Aufgaben:
- Runbook für "GitHub Auth + App Access" erstellen/aktualisieren.
- Reproduzierbare Troubleshooting-Schritte ergänzen.
- Nachweisdokumentation für RDFA-33 im `docs/`-Ordner pflegen.

DoD:
- Onboarding in <= 15 Minuten möglich (bei vorhandenen Rechten).
- Troubleshooting deckt Top-5 Fehlerfälle ab.

## Agent D - Security Review

Aufgaben:
- Sicherstellen, dass keine Credentials ins Repo gelangen.
- `.gitignore` und Doku auf Secret-Leaks prüfen.
- Empfehlungen für Rotation/Expiry von Tokens dokumentieren.

DoD:
- Keine Secrets im Repo.
- Security-Checkliste um Access-spezifische Punkte erweitert.

## Reihenfolge

1. Agent A und B parallel starten.
2. Agent C auf Basis der Ergebnisse nachziehen.
3. Agent D als Gate vor Abschluss der Issue.
