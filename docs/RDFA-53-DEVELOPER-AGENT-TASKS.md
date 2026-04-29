# RDFA-53 - Lead Developer Tasks

Stand: 2026-04-29 (UTC)  
Owner: Lead Developer Agent  
Input: `docs/RDFA-53-CTO-SPRINT-BRIEFING.md`

## Task 1 - Architekturpfad-Konsolidierung (P1)
Ziel:
- Eindeutige Zielstruktur für Web-Layer etablieren.
Umsetzung:
- Überlappungen zwischen `src/Http`, `src/Controller`, `src/Support` inventarisieren.
- Zielpfad dokumentieren und Deprecation-Plan erstellen.
DoD:
- Konsolidierungsdokument + konkrete Migrationsschritte.

## Task 2 - Regression Pack erweitern (P1)
Ziel:
- CTO-Gates reproduzierbar machen.
Umsetzung:
- Existing QA-Checks um Env-Guard-Nachweise ergänzen.
- Artefaktablage unter `docs/evidence/rdfa-53/`.
DoD:
- QA-Lauf zeigt alle Security-Guards mit pass/fail.

## Task 3 - Security Event Coverage Completion (P1)
Ziel:
- Vollständige, PII-minimierte Security-Telemetrie.
Umsetzung:
- Kritische Pfade auf fehlende Security-Events prüfen und ergänzen.
DoD:
- Einheitliches Event-Schema + Stichproben-Nachweis in Logs.

## Pflicht-Verifikation
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/qa-gate.sh --strict=1`
- Evidence unter `docs/evidence/rdfa-53/`

## Release-Hinweis
- Kein Deployment durchführen.
