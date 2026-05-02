# RDFA-62 - Lead Developer Tasks (Release Gate Ops)

Stand: 2026-04-29 (UTC)  
Owner: Lead Developer Agent

## Task 1 - Release-Gate-Skript in Team-Workflow verankern
Ziel:
- `scripts/ci/release-gate-check.sh` als Standard vor Freigabe etablieren.
DoD:
- Team-Doku/Runbook enthält den Befehl als Pflichtschritt.

## Task 2 - Artefaktablage vereinheitlichen
Ziel:
- Ergebnis von Release-Gate-Läufen konsistent dokumentieren.
DoD:
- Evidence je Lauf in passendem RDFA-Ordner abgelegt.

## Task 3 - Freigabedisziplin absichern
Ziel:
- Technischer PASS ersetzt keine formale Freigabe.
DoD:
- Freigabevermerk (Owner/CTO) für jeden Releaseversuch dokumentiert.

## Pflicht-Verifikation
- `bash scripts/ci/release-gate-check.sh`

## Release-Hinweis
- Kein Deployment ohne explizite Freigabe.
