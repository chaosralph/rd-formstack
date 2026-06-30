# Produktions-Backup (Live-Stand)

Dieser Branch (`production/live`) enthält den **Legacy-PHP-Stand** von `rd.timepro-solutions.de` als Code-Sicherung.

## Branches im Repo

| Branch | Inhalt |
|--------|--------|
| `main` | Neue Architektur (`public/`, `src/`, `templates/`) |
| `production/live` | Aktueller Produktions-Code (dieser Stand) |

## Wiederherstellung auf dem Server

1. Branch auschecken: `git checkout production/live`
2. `config/config.example.php` → `config/config.php` (Zugangsdaten eintragen)
3. `dms/dms/config.example.php` → `dms/dms/config.php`
4. Im Ordner `dms/dms/`: `composer install`
5. Upload-Verzeichnisse anlegen: `uploads/`, `dms/dms/uploads/`
6. Datenbank-Schema aus `database/` importieren (falls nötig)

## Nicht im Git enthalten

- `uploads/` und `dms/dms/uploads/` (Benutzerdaten)
- `dms/dms/vendor/` (per Composer)
- `config/config.php` mit echten Secrets

## Snapshot

- Quelle: Live-Download per SSH
- Datum: 2026-06-30
