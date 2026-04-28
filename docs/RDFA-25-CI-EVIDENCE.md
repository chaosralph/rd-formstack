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
