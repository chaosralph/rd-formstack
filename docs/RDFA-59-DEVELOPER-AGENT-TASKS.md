# RDFA-59 - Lead Developer Tasks

Stand: 2026-04-29 (UTC)  
Owner: Lead Developer Agent  
Input: `docs/RDFA-59-CTO-CHECKPOINT.md`

## Task 1 - Security Event Coverage schließen (P1)
Ziel:
- Kritische Pfade vollständig mit strukturierten Security-Events abdecken.
DoD:
- Event-Schema konsistent (`event_type`, `severity`, `request_id`, `context`).
- Nachweis in Logs + Evidence.

## Task 2 - Dokumentations-Konsolidierung (P1)
Ziel:
- Aktive Pfade vs. historische Artefakte klar trennen.
DoD:
- Aktive Doku verweist nur auf aktuelle Controller-/Flow-Pfade.
- Historische RDFA-Evidence bleibt unverändert, aber als historisch markiert.

## Task 3 - QA Evidence Hygiene (P1)
Ziel:
- Einheitliche, reproduzierbare Gate-Nachweise je Zyklus.
DoD:
- Evidence-Struktur je RDFA aktualisiert und vollständig.
- `qa-gate --strict=1` Artefakte abgelegt.

## Pflicht-Verifikation
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/qa-gate.sh --strict=1`

## Release-Hinweis
- Kein Deployment durchführen.
