# RDFA-27 / RDFA-26 - Staging Deploy & Rollback Evidence

Stand: 2026-04-28 (UTC)  
Scope: Staging-Fähigkeit, Rollback-Drill, Responsive-Smoke-Evidence

## Ticketstatus

- RDFA-27 ist formal blockiert durch `RDFA-28`.
- Blockgrund: GitHub-Zugang und Runtime-Voraussetzungen für vollständige visuelle Responsive-Evidence sind aktuell nicht verfügbar.
- Weiterführung nach Entblockung in `RDFA-28`.

## 1) Integrationsnotiz (fremde Änderungen)

- Fremde Änderungen an Routing/UX wurden berücksichtigt:
  - `public/index.php` (Pfadrouting inkl. `/kontakt`)
  - `public/assets/css/app.css` (UX/scroll polish)
  - `public/assets/js/app.js` (Header/nav Verhalten)
- Integration erfolgte ohne Revert fremder Edits.
- Konsistenzprüfung Formularfluss: Redirect/Action auf `/kontakt` ausgerichtet.

## 2) Staging-Deploy-Evidence (ohne Prod-Deployment)

### 2.1 Runtime Readiness
Ausführung:

```bash
bash scripts/check-runtime.sh
```

Ergebnis:
- `Result: READY`
- Git safe.directory ok
- PHP vorhanden
- Composer vorhanden
- Schreibrechte vorhanden

### 2.2 PHP Syntax Gate
Ausführung:

```bash
find . -type f -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

Ergebnis:
- Alle PHP-Dateien: `No syntax errors detected`

### 2.3 HTTP Reachability Smoke (Staging-Simulation lokal)
Ausführung:

```bash
php -S 127.0.0.1:8081 -t public
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8081/
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8081/kontakt
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8081/leistungen
```

Ergebnis:
- `/` -> `200`
- `/kontakt` -> `200`
- `/leistungen` -> `200`

### 2.4 Security Header Presence
Nachweis per `curl -D -` auf `/` und `/kontakt`:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy: ...`

## 3) Rollback-Drill-Evidence

Ziel: Nicht-destruktiver Rückfalltest gegen `HEAD~1` in isolierter Worktree.

### 3.1 Drill
Ausführung (vereinfacht):

```bash
git worktree add --detach /tmp/rdfa27-rollback-worktree HEAD~1
git config --global --add safe.directory /tmp/rdfa27-rollback-worktree
bash /tmp/rdfa27-rollback-worktree/scripts/check-runtime.sh
find /tmp/rdfa27-rollback-worktree -type f -name '*.php' -print0 | xargs -0 -n1 php -l
git worktree remove /tmp/rdfa27-rollback-worktree --force
```

Ergebnis:
- Rollback-Target-Commit: `888e95c`
- Runtime im Rollback-Stand: `READY`
- PHP-Lint im Rollback-Stand: ohne Syntaxfehler
- Drill-Status: `PASS`

## 4) Responsive-Smoke-Evidence

### 4.1 Nachgewiesen
- Responsive Breakpoints im CSS vorhanden (`max-width: 1000px`, `max-width: 760px`).
- Mobile Nav/Form-Client-Checks im JS vorhanden (`#nav-toggle`, `checkValidity`).
- Seitenrendering für Kernpfade technisch erreichbar (HTTP 200, siehe oben).

### 4.2 Visueller Nachweis (Screenshots) - Runtime-Blocker
Ausführung:

```bash
npx --yes playwright screenshot --viewport-size='360,800' http://127.0.0.1:8082/ /tmp/rdfa27-360x800.png
```

Fehler:
- `error while loading shared libraries: libglib-2.0.so.0`

### 4.3 Blocker-Ticketdaten
- Status: `blocked`
- Owner: `Plattform/Runtime (Infra/DevOps)`
- Action: Systembibliothek für Headless-Browser bereitstellen (`libglib-2.0.so.0`, typischerweise Paket `libglib2.0-0`).
- Repro:
  1. `php -S 127.0.0.1:8082 -t public`
  2. Playwright Screenshot-Kommando (oben)
  3. Runtime Error bzgl. fehlender `libglib-2.0.so.0`
