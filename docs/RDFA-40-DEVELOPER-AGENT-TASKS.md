# RDFA-40 Developer-Agent Tasks

Stand: 2026-04-28 (UTC)

## Task 1 - Infra Access Worker
Ziel: Runtime fuer GitHub CLI und Push-Auth verifizierbar machen.

Scope:
- `gh` in Runtime bereitstellen.
- Sicheren Auth-Flow aktivieren (keine hardcodierten Secrets).
- Dry-Run Push verifizieren.

Deliverables:
1. Lauf `bash scripts/check-rdfa40-unblock.sh` erfolgreich (`Exit 0`).
2. Log `artifacts/infra-access/github-access-check-rdfa40.log` mit CLI-Checks.
3. Log `artifacts/infra-access/git-push-dry-run-rdfa40.log` ohne Auth-Fehler.
4. Kurzprotokoll ueber verwendeten Auth-Mechanismus (ohne Secret-Werte).

## Task 2 - Connector Access Worker
Ziel: Connector-Zugriff auf Installation und Zielrepo herstellen.

Scope:
- App/Connector Installation und Repo-Scope mit Org/Admin abstimmen.
- Sichtbarkeit der Installation und des Repos sicherstellen.

Deliverables:
1. Nachweis: `list_installations` liefert mindestens einen Eintrag.
2. Nachweis: `list_repositories` enthaelt `chaosralph/rd-formstack`.
3. Dokumentierte benoetigte Minimalrechte.

## Task 3 - Validation & Evidence Worker
Ziel: RDFA-25/26 unblocken durch aktualisierte Abschluss-Evidenz.

Scope:
- CI Required Checks nach erfolgreichem Push ausloesen.
- Release-Hygiene-Evidence mit aktuellen lauffaehigen Nachweisen ergaenzen.

Deliverables:
1. Update von `docs/RDFA-25-CI-EVIDENCE.md` mit erfolgreichem Workflow-Run-Link.
2. Update von `docs/RDFA-26-RELEASE-HYGIENE-EVIDENCE.md` mit finalen Verifikationsreferenzen.
3. No-Secrets-Review in allen neu erzeugten Artefakten.

## Security Gate (fuer alle Tasks)
1. Keine Secrets in Git-Historie, Dateien oder Artefaktlogs.
2. Prepared Statements/PDO-Standards im Projekt unveraendert einhalten.
3. Keine Deployment-Aktionen ohne CTO-Freigabe.
