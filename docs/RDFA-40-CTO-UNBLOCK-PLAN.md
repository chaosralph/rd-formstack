# RDFA-40 CTO Unblock Plan - GitHub CLI + Connector Zugriff

Stand: 2026-04-28 (UTC)
Status: in_progress
Prioritaet: high

## Ziel
RDFA-25 und RDFA-26 koennen erst final abgeschlossen werden, wenn die Runtime verifizierten Zugriff auf GitHub via CLI (`gh`), Git-Remote (`git push`) und Connector (Installationen + Repository-Liste) hat.

## Ist-Stand (verifiziert)
1. GitHub CLI fehlt in der Runtime.
- Evidence: `artifacts/infra-access/github-access-check-rdfa40.log`
- Ergebnis: `FAIL: GitHub CLI installiert`

2. Git Push Auth fuer HTTPS ist nicht konfiguriert.
- Evidence: `artifacts/infra-access/git-push-dry-run-rdfa40.log`
- Ergebnis: `fatal: could not read Username for 'https://github.com': No such device or address`

3. GitHub Connector hat keinen Installations-/Repo-Scope.
- Connector-Check: `list_installations` => `[]`, `list_repositories` => `[]`

## Zielbild (technische Grundlage)
1. Zugriffspfad A: CLI
- `gh` installiert und in `PATH`.
- `gh auth status` erfolgreich.
- `gh api user` erfolgreich.

2. Zugriffspfad B: Git Remote
- `git push --dry-run` auf Test-Branch erfolgreich.
- Kein Secret im Repository; Auth nur ueber Credential Store / Device Flow / zeitlich begrenztes Token.

3. Zugriffspfad C: Connector
- Mindestens eine aktive Installation sichtbar.
- Zielrepo `chaosralph/rd-formstack` im Scope sichtbar/lesbar.

## Sicherheitsanforderungen
1. Secret Management
- Keine Tokens, Passwoerter oder Keys in Repo-Dateien, Skripten oder Logs.
- Nur `.env.example` mit Platzhaltern; reale Werte nur in Runtime-Secret-Store.

2. Least Privilege
- Connector/App-Rechte nur fuer benoetigte Scopes (`issues`, `pull_requests`, `checks`, `contents:read`; `contents:write` nur falls zwingend noetig).
- HTTPS Credential mit minimalen Rechten und kurzer Gueltigkeit.

3. Auditierbarkeit
- Alle Verifikationslaeufe als Artefakte unter `artifacts/infra-access/` dokumentieren.
- Keine Ausgabe sensitiver Daten in CI/Runtime-Logs.

## Umsetzungsplan
1. Infra/DevOps
- `gh` installieren.
- Auth-Flow aktivieren (`gh auth login` oder sicherer Credential-Helper).
- Dry-Run Push erfolgreich machen.

2. GitHub Org/App Admin
- Connector Installation auf Ziel-Account pruefen/aktivieren.
- Repository `chaosralph/rd-formstack` explizit in Installations-Scope aufnehmen.

3. Verifikation
- `bash scripts/check-rdfa40-unblock.sh`
- `bash scripts/check-github-access.sh`
- `git push --dry-run origin HEAD:refs/heads/rdfa-40-access-check`
- Connector-Checks erneut ausfuehren.

## Abnahmekriterien (Go/No-Go)
Go nur wenn alle Bedingungen erfuellt sind:
1. `scripts/check-github-access.sh` Exit-Code `0`.
2. `scripts/check-rdfa40-unblock.sh` Exit-Code `0`.
3. Push-Dry-Run ohne Auth-Fehler.
4. Connector zeigt Installation und Zielrepo.
5. RDFA-25/26 Evidence um aktuelle, erfolgreiche Run-Links/Artefakte ergaenzt.

No-Go bei fehlender Erfuellung eines Kriteriums.

## Kein Deployment ohne Freigabe
RDFA-40 umfasst ausschliesslich Zugriffsunblock und Verifikation. Deployment-Aktivitaeten bleiben bis expliziter Freigabe gesperrt.
