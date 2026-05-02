# RDFA-57 - Lead Developer Tasks (Consolidation)

Stand: 2026-04-29 (UTC)  
Owner: Lead Developer Agent  
Input: `docs/RDFA-57-ARCHITECTURE-CONSOLIDATION-PLAN.md`

## Task 1 - Controller-Pfad vereinheitlichen (P1)
Ziel:
- Nur `src/Controller/ContactController.php` als aktiven Kontakt-Controller verwenden.
DoD:
- Keine aktiven Referenzen mehr auf `src/Http/ContactController.php`.

## Task 2 - Deprecation/Removal Doppelpfade (P1)
Ziel:
- Redundante, ungenutzte Pfade entfernen oder klar als deprecated markieren.
DoD:
- Bereinigung dokumentiert und via QA verifiziert.

## Task 3 - Support-Layer entrümpeln (P1)
Ziel:
- `src/Support/*` enthält nur echte Querschnittsfunktionen.
DoD:
- Keine Web-Flow-Duplikate außerhalb `src/Http`/`src/Bootstrap`.

## Pflicht-Verifikation
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/qa-gate.sh --strict=1`
- Evidence unter `docs/evidence/rdfa-57/`

## Release-Hinweis
- Kein Deployment durchführen.
