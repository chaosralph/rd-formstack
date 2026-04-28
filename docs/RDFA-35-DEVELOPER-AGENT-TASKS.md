# RDFA-35 Developer-Agent Tasks

Stand: 2026-04-28 (UTC)

## Agent A - Runtime Access Verification
Aufgaben:
- `scripts/check-github-access.sh` nach Infra-Freigabe ausführen.
- PASS/FAIL Evidence in `artifacts/infra-access/github-access-check.log` aktualisieren.
- Exit-Code und Laufzeitzeitpunkt dokumentieren.

DoD:
- Alle Access-Checks auf PASS.
- Keine sensitiven Werte im Log.

## Agent B - Push Path Validation
Aufgaben:
- `git push --dry-run origin HEAD:refs/heads/rdfa-35-access-check` ausführen.
- Fehlerklasse (Auth/Netzwerk/Policy) klar klassifizieren.
- Ergebnis in `artifacts/infra-access/git-push-dry-run.log` dokumentieren.

DoD:
- Dry-Run ohne Auth-Fehler abgeschlossen.
- Reproduzierbarer Lauf dokumentiert.

## Agent C - Connector Permission Verification
Aufgaben:
- GitHub App/Connector Zugriff auf User, Installationen und Zielrepo prüfen.
- Ist-/Soll-Permissions mit Least-Privilege-Sicht dokumentieren.
- Abweichungen mit Owner und Risiko festhalten.

DoD:
- Repo-Read auf Zielrepo bestätigt.
- Alle Permission-Gaps owner-zugeordnet.

## Agent D - Security Gate & Evidence Publishing
Aufgaben:
- Logs/Doku auf Secret-Leaks prüfen.
- RDFA-35 Statusdokument aktualisieren.
- Abnahme-Checkliste für CTO vorbereiten.

DoD:
- Keine Secrets im Repo/Logs.
- Statusdokument vollständig mit Zeitstempel, Exit-Codes und Go/No-Go.

## Reihenfolge
1. Agent A und Agent B parallel.
2. Agent C nach erfolgreichem Basispfad.
3. Agent D als Abschluss-Gate vor CTO-Abnahme.
