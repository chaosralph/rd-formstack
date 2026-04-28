# RD Formstack Solutions - Technische Grundlage

## Architektur
- `public/`: Front Controller, statische Assets.
- `src/`: PHP-Anwendungslogik (HTTP, Security, Repository, DB).
- `config/`: getrennte Konfiguration, liest Umgebungsvariablen.
- `database/migrations/`: SQL-Migrationen.
- `storage/logs/`: Laufzeit-Logs (nicht versioniert).

## Sicherheitsentscheidungen
- Keine Secrets im Repository (`.env` lokal, `.env.example` als Vorlage).
- PDO mit `ERRMODE_EXCEPTION` und deaktivierter Emulation.
- Nur Prepared Statements für Datenbankzugriffe.
- CSRF-Token bei mutierenden Formular-Requests.
- Serverseitige Validierung und HTML-Escaping.

## Erweiterbarkeit
- Controller und Repository getrennt.
- Datenbankzugriff zentral über `Connection`.
- Routing kann aus `public/index.php` in dedizierten Router ausgelagert werden.
- Zusätzliche Domänenmodule unter `src/` nach gleichem Muster.

## Lokale Inbetriebnahme
1. `.env.example` nach `.env` kopieren und DB-Werte setzen.
2. Migration `database/migrations/001_create_contacts.sql` in MySQL/MariaDB ausführen.
3. PHP Built-in Server starten: `php -S localhost:8000 -t public`.

## Kein Deployment
- Deployment ist explizit gesperrt bis Freigabe.
