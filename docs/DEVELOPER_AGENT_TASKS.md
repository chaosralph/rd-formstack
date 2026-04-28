# Developer-Agenten Aufgabenpakete (RDFA-7)

Stand: 2026-04-28 (UTC)

## Reihenfolge und Abhängigkeiten

1. P0-Foundation abschließen.
2. P1-Domain- und API-Aufbau starten.
3. Quality-Gates parallel ab P0 einziehen.

## Agent A - Platform/Core (P0)

### Aufgaben
- Bootstrap aus `public/index.php` extrahieren.
- Router-Mapping einführen (GET/POST sauber getrennt).
- Zentrales Exception-Handling implementieren.
- Strukturierte Logs + Request-ID hinzufügen.

### DoD
- Front Controller ist schlank und delegiert nur noch.
- Fehler werden intern geloggt; Nutzer sieht generische Fehlermeldung.
- Lint und Runtime-Check sind grün.

## Agent B - Security (P0/P1)

### Aufgaben
- Security-Header-Baseline einführen.
- Request-Validierung um Feldlängen/Formatgrenzen erweitern.
- CSRF-Flow regressionssicher prüfen.
- Security-Checks in `docs/qa-checklist.md` erweitern.

### DoD
- Header sind in allen HTML-Responses aktiv.
- Invalid Requests werden konsistent abgefangen.
- QA-Checkliste enthält reproduzierbare Security-Prüfschritte.

## Agent C - Data/Domain (P1)

### Aufgaben
- Migrationen für `forms`, `submissions`, `submission_status_events`.
- Repositories für neue Entitäten.
- Erste Application-Services für Submission-Prozess.

### DoD
- Migrationen sind idempotent und dokumentiert.
- Repositories arbeiten mit PDO + Prepared Statements.
- Controller enthalten keine Business-Logik mehr.

## Agent D - API/Integration (P1)

### Aufgaben
- JSON-Endpunkte für Submission-Create/Status.
- Einheitliches Fehlerformat (`code`, `message`, `details`).
- Basales Rate-Limiting-Konzept definieren.

### DoD
- API antwortet konsistent für Erfolg, Validierungsfehler, Serverfehler.
- API-Dokumentation mit Request/Response-Beispielen liegt vor.

## Agent E - QA/Testing (P1/P2)

### Aufgaben
- PHPUnit-Basis aufsetzen.
- Integrationstests für Repository-Layer gegen Test-DB.
- Security-Smoke-Tests (CSRF/Validation/Auth-Zugriff) definieren.

### DoD
- Lokaler Test-Run ist dokumentiert und wiederholbar.
- Kritische Pfade sind automatisiert abgedeckt.
- QA-Gate-Kriterien sind als PASS/FAIL nachvollziehbar.

## Governance

- Kein Deployment ohne Freigabe.
- Keine Hardcoded Secrets.
- Kritische Architekturentscheidungen in ADR-Form dokumentieren.
