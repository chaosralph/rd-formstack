# RDFA-26 / RDFA-25 Release-Hygiene Evidence

Stand: 2026-04-28 (UTC)

## Ziel
Nachvollziehbare Nachweise für sauberen Release-Stand: Commit-Historie, QA-Prüfung, Tag-Markierung.

## Commit-Evidenz
1. `c8c226c` - first RD Formstack website version
2. `dc7dff7` - homepage content structure + nav responsiveness
3. `15ce7ed` - contact data capture + homepage polish
4. `3a77c2d` - multi-page website shell with responsive sections
5. `114d4f4` - planning/QA/release evidence docs (dieser Commit)

## QA-/Technik-Evidenz
1. PHP-Lint: alle relevanten Dateien ohne Syntaxfehler.
2. Runtime-Readiness: `bash scripts/check-runtime.sh` -> `READY`.
3. Security-/QA-Checkliste dokumentiert in `docs/qa-checklist.md`.

## Review-/PR-Evidenz
- Lokale Umsetzung und Commit-Historie sind vorhanden.
- PR-/Review-Nachweis erfolgt im Git-Hosting-System nach Push (außerhalb dieses lokalen Workspaces).

## Tag-Evidenz
- Geplanter Hygiene-Tag: `rdfa-26-release-hygiene-2026-04-28`
- Zweck: Markierung des dokumentierten Hygiene-Zwischenstands.
