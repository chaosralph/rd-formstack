# RD Formstack Solutions

Initiale technische Grundlage auf Basis von PHP, MySQL/MariaDB, HTML, CSS, JavaScript.

## Kernpunkte
- PDO-Datenzugriffe mit Prepared Statements.
- Getrennte Konfiguration via `.env`.
- Keine hardcodierten Secrets.
- Basis-Sicherheitsmechanismen (CSRF, Validierung, Escaping).

## Start
1. `cp .env.example .env`
2. Runtime-Readiness prüfen: `bash scripts/check-runtime.sh`
3. DB-Zugang in `.env` setzen
4. Migration `database/migrations/001_create_contacts.sql` ausführen
5. `php -S localhost:8000 -t public`

## Runtime-Unblock (RDFA-22)
- `git safe.directory` muss den Repo-Pfad enthalten.
- PHP und Composer müssen installiert sein.
- Bei externer Runtime-Blockade ist der Owner `Plattform/Runtime (Infra/DevOps)`.
