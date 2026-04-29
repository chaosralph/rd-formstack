# RD Formstack Solutions - Technische Grundlage

## Architektur
- `public/`: Front Controller und statische Assets.
- `src/Bootstrap/`: Laufzeitinitialisierung (Session, Security-Header, Env-Load).
- `src/Http/Routing/`: zentraler Route-Katalog.
- `src/Http/`: Controller, Request/Response, Error-Handling.
- `src/Support/`: technische Querschnittsfunktionen (z. B. Logging).
- `src/`: PHP-Anwendungslogik (Security, Repository, DB, View).
- `config/`: getrennte Konfiguration, liest Umgebungsvariablen.
- `database/migrations/`: SQL-Migrationen.
- `storage/logs/`: Laufzeit-Logs (nicht versioniert).

## Sicherheitsentscheidungen
- Keine Secrets im Repository (`.env` lokal, `.env.example` als Vorlage).
- PDO mit `ERRMODE_EXCEPTION` und deaktivierter Emulation.
- Nur Prepared Statements für Datenbankzugriffe.
- CSRF-Token bei mutierenden Formular-Requests.
- Serverseitige Validierung und HTML-Escaping.
- Zentrales Exception-Handling mit generischer Fehlerantwort für Endnutzer.

## Erweiterbarkeit
- Controller und Repository getrennt.
- Datenbankzugriff zentral über `Connection`.
- Kontaktdaten normalisiert (`name`, `company`, `email`, `phone`, `message`) fuer bessere Integrationen.
- Bootstrapping und Routing als separate Module.
- Strukturierte Logs über `Logger` als Basis für Monitoring.
- Zusätzliche Domänenmodule unter `src/` nach gleichem Muster.

## Lokale Inbetriebnahme
1. `.env.example` nach `.env` kopieren und DB-Werte setzen.
2. Migration `database/migrations/001_create_contacts.sql` in MySQL/MariaDB ausführen.
3. Runtime-Check: `bash scripts/check-runtime.sh`.
4. PHP Built-in Server starten: `php -S localhost:8000 -t public`.

## Kein Deployment
- Deployment ist explizit gesperrt bis Freigabe.

## Zielarchitektur V2 (Ergänzung RDFA-46)

### Layer-Modell
- Presentation: `public/`, Routing, Controller, View-Rendering.
- Application: Use-Case-Services für Geschäftsabläufe (z. B. Kontaktprozess, Content-Orchestrierung).
- Domain: Fachregeln, Validierung, Domänenobjekte.
- Infrastructure: Repository-Implementierungen, PDO/MySQL, Logging, externe Adapter.

### Sicherheit V2
- Secrets ausschließlich über Environment-Variablen; keine Credentials im Repository.
- Mutierende Requests nur mit CSRF-Schutz und serverseitiger Validierung.
- Prepared Statements und zentralisiertes Escaping bleiben verpflichtend.
- Security-Header werden zentral verwaltet; CSP schrittweise in Richtung Enforce.
- Audit-Logging für administrative und sicherheitskritische Aktionen.

### Ziel-Datenmodell V2 (fachliche Kernobjekte)
- `contacts` fuer Kontaktanfragen inkl. Bearbeitungsstatus.
- `pages` und `content_blocks` fuer flexible Inhaltsstruktur.
- `media_assets` fuer referenzierte Dateien/Assets.
- `users`, `roles`, `user_role_map` fuer Zugriffssteuerung.
- `audit_log` fuer Nachvollziehbarkeit.

### Deployment-Umgebungen
- Lokal: Entwicklung mit lokaler `.env` und lokaler Datenbank.
- Staging: produktionsnahe Validierung mit CI-gesteuerten Abläufen.
- Produktion: nur nach expliziter Freigabe mit definiertem Rollback.

### Migrationsansatz
- Keine 1:1-HTML-Kopie der Alt-Seite.
- Ziel ist funktionale Parität: identische Business-Ergebnisse bei moderner Umsetzung.
- Legacy wird schrittweise durch V2-Capabilities ersetzt (inkrementelle Migration).

### Phasenplan
1. Phase 0: Scope/Baseline und Sicherheits-/Architekturentscheidungen fixieren.
2. Phase 1: Foundation (Bootstrap, Routing, Fehlerhandling, Konfiguration, Migrationen).
3. Phase 2: Core-Capabilities (Kontakt, Content, Admin-Basis).
4. Phase 3: Legacy-Migration je Capability mit Regressionstests.
5. Phase 4: Staging-Härtung, Rollback-Proben, Betriebsdokumentation.
6. Phase 5: Go-Live-Readiness (ohne Deployment in diesem Arbeitsauftrag).
