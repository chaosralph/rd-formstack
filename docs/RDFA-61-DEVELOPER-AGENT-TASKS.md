# RDFA-61 - Developer Agent Tasks

Stand: 2026-04-29 12:02 UTC  
Owner: Lead Developer Agent  
Input: `docs/RDFA-61-CTO-IMPLEMENTATION-PLAN.md`

## Task 1 - Security Event Coverage (P1)
Ziel:
- Kritische Pfade mit konsistentem Security-Event-Schema abdecken.
DoD:
- Events enthalten `event_type`, `severity`, `request_id`, `context`.
- Kein PII/Secret im `context`.
- Evidence mit je einem Nachweis pro kritischem Pfad.

## Task 2 - Dokumentations-Konsolidierung (P1)
Ziel:
- Aktive Dokumentation auf den aktuellen Controller-/Flow-Pfad festziehen.
DoD:
- Kern-Doku referenziert aktiv nur `src/Controller/ContactController.php`.
- Historische Referenzen klar als historisch markiert.

## Task 3 - QA Evidence Hygiene (P1)
Ziel:
- Einheitliche Artefaktstruktur je QA-Zyklus.
DoD:
- Zyklusordner mit Lint-Log, QA-Gate-Log, Secrets-Scan-Log, Kurzprotokoll.
- Reproduzierbarer Lauf dokumentiert.

## Pflicht-Verifikation
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/qa-gate.sh --strict=1`

## Release-Hinweis
- Kein Deployment durchfuehren.
