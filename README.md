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

## QA / Evidence
- CI-Smoke-Routen: `bash scripts/ci/smoke-routes.sh`
- Responsive-Screenshots (360/768/1280): `bash scripts/ci/responsive-evidence.sh`
- GitHub Auth/App Access Check (RDFA-33): `bash scripts/check-github-access.sh`

## Runtime-Unblock (RDFA-22)
- `git safe.directory` muss den Repo-Pfad enthalten.
- PHP und Composer müssen installiert sein.
- Bei externer Runtime-Blockade ist der Owner `Plattform/Runtime (Infra/DevOps)`.

## Infra Access Enablement (RDFA-33)
- Plan: `docs/RDFA-33-INFRA-ACCESS-PLAN.md`
- Developer-Aufgaben: `docs/RDFA-33-DEVELOPER-AGENT-TASKS.md`

## CTO Architecture Governance
- Zentrale Übersicht: `docs/CTO-ARCHITECTURE-INDEX.md`
- Kernarchitektur: `docs/ARCHITECTURE.md`
- Security Threat Model: `docs/RDFA-51-SECURITY-THREAT-MODEL.md`
- Action Matrix: `docs/RDFA-55-CTO-ACTION-MATRIX.md`
- Governance: Kein Deployment ohne explizite Freigabe.
