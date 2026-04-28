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
