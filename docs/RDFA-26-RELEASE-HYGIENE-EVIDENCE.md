# RDFA-26 / RDFA-25 Release-Hygiene Evidence

Stand: 2026-04-28 (UTC)
Status: provisional
Note: Final GitHub/Connector re-validation pending RDFA-32/40/42.

## Temporärer Fallback-Status (RDFA-44)
Bis zur Aufloesung des GitHub-Access-Blockers gilt:
1. Lokale Release-Hygiene-Nachweise (Commitstand, QA-Checks, Runtime-Checks) bleiben verbindlich.
2. Remote-/PR-/Workflow-Nachweise sind nachziehpflichtig, sobald Access verfuegbar ist.
3. Abschluss als `done` ist ohne Nachzug nicht zulaessig.

Referenz:
- `docs/RDFA-44-CTO-DECISION-TEMP-FALLBACK-RDFA25-26.md`

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
1. `rdfa-26-release-hygiene-2026-04-28` - initialer Hygiene-Zwischenstand
2. `rdfa-26-release-hygiene-final-2026-04-28` - finalisierter lokaler Hygiene-Stand
3. `rdfa-26-release-hygiene-provisional-2026-04-28` - aktueller CTO-provisional Snapshot (RDFA-44)

## Access Re-Validation (RDFA-40 Bezug)
Stand: 2026-04-28T18:56:17Z

Vorher/Nachher-Kriterien:
1. Git Auth Check: `git ls-remote origin HEAD` ohne Auth-Fehler.
2. Connector Check: Repo-Visibility fuer `chaosralph/rd-formstack` vorhanden (kein 404/keine leere Repo-Liste).
3. Sammelcheck: `bash scripts/check-rdfa40-unblock.sh` Exit `0`.

Aktuelles Ergebnis:
1. `gh` lokal verfuegbar (`PASS: GitHub CLI installiert`), aber `gh` Auth/API weiterhin fehlend.
2. `git ls-remote origin HEAD` fehlgeschlagen mit `could not read Username for 'https://github.com'`.
3. Connector weiterhin ohne Sichtbarkeit (`list_installations = []`, `list_repositories = []`).
4. Connector Repo-Read auf `.../blob/main/README.md` liefert `GitHub API error 404 Not Found`.
5. Sammelcheck weiterhin `0 PASS / 2 FAIL`.

Evidence:
- `artifacts/infra-access/git-ls-remote-rdfa40.log`
- `artifacts/infra-access/rdfa40-unblock-summary.log`
- `artifacts/infra-access/github-access-check-rdfa40.log`
- `artifacts/infra-access/git-push-dry-run-rdfa40.log`

Fehlende Admin-Schritte:
1. Infra/DevOps: `gh` Auth/API in Runtime aktivieren.
2. Infra/DevOps: HTTPS Credential Flow fuer Git (`git ls-remote`/`git push`) aktivieren.
3. GitHub Org/App Admin: Connector-Installation inkl. Repo-Scope fuer `chaosralph/rd-formstack` freischalten.

## Exit criteria for final_accept
1. Access-Revalidation ohne FAIL (`scripts/check-rdfa40-unblock.sh` Exit `0`).
2. Push/PR/Workflow-Evidence fuer den finalen Hygiene-Stand dokumentiert.
3. Tag-Strategie auf tatsaechlich erstellten Tag aktualisiert.
4. Statuswechsel dieses Dokuments von `provisional` auf `final_accept`.

## Provisional local evidence snapshot
Timestamp (UTC): 2026-04-28T19:55:08Z

Executed commands:
1. `git status --short`
2. `git rev-parse --short HEAD`
3. `git tag --list 'rdfa-26-release-hygiene*' --sort=-creatordate`
4. `bash scripts/check-runtime.sh`
5. `command -v gh || true`

Artifact path:
- `docs/evidence/rdfa-26/provisional-local-evidence-2026-04-28T1955Z.log`
