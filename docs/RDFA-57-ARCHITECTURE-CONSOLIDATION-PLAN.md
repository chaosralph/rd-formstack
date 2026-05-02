# RDFA-57 - Architecture Consolidation Plan

Stand: 2026-04-29 (UTC)  
Owner: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## Ziel
Architektur-Drift abbauen und einen eindeutigen Web-Entry-Layer herstellen.

## Befund
- Doppelter Controller-Pfad war vorhanden:
  - `src/Http/ContactController.php` (ungenutzte Duplikatdatei)
  - `src/Controller/ContactController.php` (aktiver Pfad)
- Überlappende Security-Utilities in `src/Support/*` neben bestehender Bootstrap-/Http-Logik.

## Zielstruktur
- `src/Http/*`: Request/Response/ErrorHandling/Routing
- `src/Controller/*`: Web-Controller (aktiver Entry-Controller-Pfad)
- `src/Application/*`: Use-Case-Orchestrierung
- `src/Repository/*`: DB-nahe Persistence
- `src/Support/*`: nur generische Cross-Cutting Utilities ohne Web-Flow-Duplikate

## Migrationsreihenfolge
1. Nicht genutzte Duplikatdatei `src/Http/ContactController.php` entfernen (abgeschlossen).
2. Aktiven Controller-Pfad auf `src/Controller/ContactController.php` festschreiben.
3. `src/Support/*` auf reine Querschnittsfunktion prüfen; Web-spezifische Logik in `src/Controller`/`src/Http`/`src/Bootstrap` halten.
4. QA-Gate + Smoke-Routes + Header/Host-Regression laufen lassen.

## Sicherheitsbezug
- Vermeidet uneinheitliche Guard-Anwendung in parallelen Pfaden.
- Stärkt Nachvollziehbarkeit für Incident-Triage und Review.

## Governance
- Kein Deployment ohne explizite Freigabe.
