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
4. Install/authorize GitHub app access for repository `chaosralph/rd-formstack` (optional alternative path).
5. Retry push to trigger workflow and attach successful run URL.
