# RDFA-40 Admin Handover - CLI, HTTPS Credentials, Connector Scope

Stand: 2026-04-28 (UTC)
Prioritaet: high

## Zweck
Dieses Handover beschreibt die minimalen Admin-Aktionen, um RDFA-25/26 unblocken zu koennen.

## Vorher-Signaturen (aktueller Zustand)
1. `gh` ist lokal verfuegbar (`PASS: GitHub CLI installiert`), aber Auth fehlt (`FAIL: GitHub CLI Auth aktiv`).
2. `git ls-remote origin HEAD` scheitert mit `could not read Username for 'https://github.com'`.
3. Connector hat keine Repo-Visibility (`list_installations = []`, `list_repositories = []`).
4. Connector Repo-Read liefert 404 (`.../blob/main/README.md` => `GitHub API error 404 Not Found`).

## Admin-Track A - Infra/DevOps (Runtime)
1. CLI Auth aktivieren.
- Empfohlen: `gh auth login` (Device Flow / Enterprise Policy-konform).
- Soll: `gh auth status` erfolgreich.
- Soll: `gh api user` erfolgreich.

2. HTTPS Credential Flow fuer Git aktivieren.
- Zulassig: Credential Helper, zeitlich begrenzter Token, oder runtime-gebundene Auth.
- Nicht zulassig: hardcodierte Credentials in Repo/Script/Logs.
- Soll: `git ls-remote origin HEAD` erfolgreich.
- Soll: `git push --dry-run origin HEAD:refs/heads/rdfa-40-access-check` erfolgreich.

## Admin-Track B - GitHub Org/App Admin (Connector)
1. Connector/App Installation fuer Ziel-Account aktivieren.
2. Repository `chaosralph/rd-formstack` explizit in Installations-Scope aufnehmen.
3. Mindestrechte pruefen (`issues`, `pull_requests`, `checks`, `contents:read`; write nur falls noetig).

Soll nach Freigabe:
- `list_installations` liefert mindestens 1 Installation.
- `list_repositories` enthaelt `chaosralph/rd-formstack`.
- Connector Repo-Read auf `.../blob/main/README.md` liefert keinen 404 mehr.

## CTO-Re-Validation (nach Admin-Freigabe)
1. `bash scripts/check-rdfa40-unblock.sh`
2. `git ls-remote origin HEAD`
3. Connector-Checks (`list_installations`, `list_repositories`)

Go-Kriterium:
- Alle drei Access-Pfade erfolgreich (`gh`, HTTPS-Creds, Connector-Scope).

## Evidence-Pfade
- `artifacts/infra-access/rdfa40-unblock-summary.log`
- `artifacts/infra-access/github-access-check-rdfa40.log`
- `artifacts/infra-access/git-push-dry-run-rdfa40.log`
- `artifacts/infra-access/git-ls-remote-rdfa40.log`
