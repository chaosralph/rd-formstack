# RDFA-25 / RDFA-24 - CI Required Checks Evidence

Stand: 2026-04-28 (UTC)

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
- `Linted 10 PHP files successfully.`
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
