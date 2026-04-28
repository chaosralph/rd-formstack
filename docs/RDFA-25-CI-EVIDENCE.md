# RDFA-25 / RDFA-24 - CI Required Checks Evidence

Stand: 2026-04-28 (UTC)
Status: `provisional`
Note: `Final GitHub/Connector re-validation pending RDFA-32/40/42`
Status: conditional_accept (temporaerer Fallback gemaess RDFA-44)

## RDFA-45 Konsolidiertes Zwischenpaket
Aktueller konsolidierter Snapshot fuer RDFA-25/26:
- `docs/RDFA-45-PROVISIONAL-CI-RELEASE-EVIDENCE-PACKAGE.md`
- Artefakte: `docs/evidence/rdfa-45/` (Timestamp: `2026-04-28T23:38:36Z`)
- Artefakte (CTO-Kickoff 2026-04-29): `artifacts/rdfa-45/rdfa-25-local-ci-2026-04-28T234141Z.log`

## Temporärer Fallback-Status (RDFA-44)
Da der externe GitHub-Access-Blocker weiterhin aktiv ist, gilt bis zum Unblock:
1. Lokale CI-Checks sind die verbindliche technische Mindest-Evidence.
2. Fehlende Live-Run-URL ist als externer Blocker dokumentiert und kein lokaler Implementierungsfehler.
3. Nach Access-OK ist die GitHub-Evidence verpflichtend nachzuziehen.

Referenz:
- `docs/RDFA-44-CTO-DECISION-TEMP-FALLBACK-RDFA25-26.md`

## Implemented required checks

Workflow file:
- `.github/workflows/required-checks.yml`

Job names to mark as required in branch protection:
- `php-lint`
- `smoke-routes`

## Local proof run (same scripts as workflow)

Executed:
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/smoke-routes.sh`

Result excerpts:
- `Linted 15 PHP files successfully.`
- `Smoke routes check passed for 6 routes.`

Route status excerpts:
- `/ -> 200`
- `/leistungen -> 200`
- `/referenzen -> 200`
- `/kontakt -> 200`
- `/login -> 200`
- `/dms -> 200`

## Log references

Local run logs (generated during validation):
- `storage/logs/ci-php-lint.log`
- `storage/logs/ci-smoke-routes.log`

Note: `storage/logs/*.log` is gitignored by design.

## Provisional reproducible artifacts (versioned)

All artifacts contain UTC timestamp, command, and full output.

Commands executed:
- `php -v`
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/smoke-routes.sh`
- `php -l public/index.php`
- `php -l src/Http/ContactController.php`

- `docs/evidence/ci-provisional/01-php-version.txt`
- `docs/evidence/ci-provisional/02-php-lint.txt`
- `docs/evidence/ci-provisional/03-smoke-routes.txt`
- `docs/evidence/ci-provisional/04-index-lint.txt`
- `docs/evidence/ci-provisional/05-contact-controller-lint.txt`

## GitHub Actions references

After push, workflow runs will be visible at:
- `https://github.com/chaosralph/rd-formstack/actions/workflows/required-checks.yml`

Use the latest successful run URL from that page as ticket evidence for green required checks.

## Current blocker for live GitHub run evidence

Status: `blocked` (runtime/access)
Dependency: `RDFA-28` (external unblock ticket for GitHub access/runtime prerequisites)

Re-validation (2026-04-28T18:56:17Z):
- `bash scripts/check-rdfa40-unblock.sh` => `Summary: 0 PASS / 2 FAIL`
- `git ls-remote origin HEAD` => `fatal: could not read Username for 'https://github.com': No such device or address`
- Connector visibility check => `list_installations = []`, `list_repositories = []` (keine Repo-Visibility)
- Connector repo read => `.../blob/main/README.md` liefert `GitHub API error 404 Not Found`
- `bash scripts/check-github-access.sh` => `PASS: GitHub CLI installiert`, `FAIL` bei Auth/API/Installationen
- Evidence:
  - `artifacts/infra-access/rdfa40-unblock-summary.log`
  - `artifacts/infra-access/git-ls-remote-rdfa40.log`
  - `artifacts/infra-access/github-access-check-rdfa40.log`
  - `artifacts/infra-access/git-push-dry-run-rdfa40.log`

Minimal reproducible context:
- `git push -u origin main` fails with `Host key verification failed.`
- `ssh -T git@github.com` fails with `Host key verification failed.`
- `git push -u origin main` via HTTPS remote fails with `could not read Username for 'https://github.com'`.
- GitHub connector has user auth but no installation/repo access (`list_installations` and `list_repositories` return empty).

Owner: Plattform/Runtime (Infra/DevOps)

Required action:
1. Provide trusted GitHub host key in this runtime.
2. Ensure SSH key/auth for `git@github.com:chaosralph/rd-formstack.git`.
3. Or provide HTTPS credential helper/token for `https://github.com/chaosralph/rd-formstack.git`.
4. Install/authorize GitHub app access for repository `chaosralph/rd-formstack` and ensure installation scope includes this repo.
5. Retry `git ls-remote origin HEAD` and expect no auth error.
6. Retry push to trigger workflow and attach successful run URL.

## Exit criteria for final_accept
Alle Punkte muessen erfuellt sein:
1. `git ls-remote origin HEAD` ohne Auth-Fehler.
2. Erfolgreicher Push, der `required-checks.yml` ausfuehrt.
3. Dokumentierte erfolgreiche Run-URL mit Jobs `php-lint` und `smoke-routes`.
4. Statuswechsel dieses Dokuments von `conditional_accept` auf `final_accept`.
