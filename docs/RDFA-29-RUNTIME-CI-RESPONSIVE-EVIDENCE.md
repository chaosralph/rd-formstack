# RDFA-29 - CTO: Infra/GitHub Runtime-Unblock für CI- und Responsive-Evidence

Stand: 2026-04-28 (UTC)
Status: in_progress
Priorität: high

## Ziel
Technische Grundlage so absichern, dass:
- CI-Checks reproduzierbar lokal und in GitHub Actions ausführbar sind.
- Responsive-Evidence nicht an Runtime/DB-Problemen scheitert.
- Fehlerpfade ohne Secret-Leak und mit stabiler Nutzerführung behandelt werden.

## Umgesetzte Maßnahmen
1. Front-Controller entkoppelt:
- Initialisierung nach `src/Bootstrap/AppBootstrap.php` ausgelagert.
- Route-Metadaten nach `src/Http/Routing/RouteCatalog.php` ausgelagert.

2. Zentrales Fehlerhandling ergänzt:
- `set_exception_handler` in `public/index.php`.
- `src/Http/ErrorHandler.php` für generische 500-Response.
- `src/Support/Logger.php` für strukturierte Fehlerlogs (`storage/logs/app.log`).

3. Runtime-Verhalten abgesichert:
- GET-Routen rendern weiterhin ohne DB-Zugriff.
- DB-Verbindung wird nur im Kontakt-POST-Pfad aufgebaut.
- Bei DB/Runtime-Fehlern im POST: kontrollierter Redirect mit Flash-Fehler.

## Validierung (lokal)
Ausgeführt am 2026-04-28 (UTC):

1. `bash scripts/ci/php-lint.sh`
- Ergebnis: PASS
- Auszug: `Linted 14 PHP files successfully.`

2. `bash scripts/ci/smoke-routes.sh`
- Ergebnis: PASS
- Auszug: `Smoke routes check passed for 6 routes.`
- Routen: `/`, `/leistungen`, `/referenzen`, `/kontakt`, `/login`, `/dms` jeweils HTTP 200.

3. `bash scripts/ci/responsive-evidence.sh`
- Ergebnis: PASS
- Artefakte:
  - `artifacts/qa/responsive/home-360x800.png`
  - `artifacts/qa/responsive/home-768x1024.png`
  - `artifacts/qa/responsive/home-1280x800.png`
  - `artifacts/qa/responsive/kontakt-360x800.png`
  - `artifacts/qa/responsive/report.txt`

## Sicherheitsprüfung
1. Secrets
- Keine hardcodierten Secrets neu eingeführt.
- Konfiguration verbleibt über `.env` / `config/*`.

2. Input/DB
- PDO + Prepared Statements unverändert aktiv.
- CSRF-Flow unverändert aktiv.

3. Error Handling
- Keine Stacktraces oder internen Details an Endnutzer.
- Interne Fehler inkl. `request_id` werden serverseitig geloggt.

## Offene externe Blocker
1. Live-GitHub-Evidence (Workflow-Run-URL) bleibt extern abhängig von Runtime/GitHub-Zugang:
- Host-Key/SSH oder HTTPS-Credentials im Runtime-Umfeld.
- Optional: GitHub-App-Installation mit Repo-Zugriff.

2. Ohne externen Access-Unblock kann kein neuer Actions-Run aus dieser Runtime getriggert werden.

## Aufgaben für Developer-Agenten (nächste Schritte)
1. Platform-Agent:
- Request-ID in alle kontrollierten Redirect-Fehlerpfade übernehmen.
- Optional: `APP_DEBUG`-gesteuertes Verhalten im ErrorHandler ergänzen (nur intern, keine Leak-Ausgabe).

2. QA-Agent:
- Accessibility-Smoke (Keyboard/Fokus) ergänzend zu den erzeugten Responsive-Screenshots dokumentieren.

3. Security-Agent:
- CSP auf externe Font-Abhängigkeit prüfen und ggf. self-hosted Fonts vorbereiten.
- Log-Retention und PII-Minimierung für `app.log` definieren.

## Deployment-Hinweis
Kein Deployment durchgeführt oder vorbereitet. Deployment bleibt bis explizite Freigabe gesperrt.
