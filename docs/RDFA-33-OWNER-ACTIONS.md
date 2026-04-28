# RDFA-33 Owner Actions (Unblock)

Stand: 2026-04-28 (UTC)

## Owner: Plattform/Runtime (Infra/DevOps)

1. GitHub CLI bereitstellen
```bash
which gh
```
Erwartung: Pfad vorhanden.

2. Runtime Auth für Git Push aktivieren
- Option A: `gh auth login` (empfohlen)
- Option B: sicherer Credential Helper + PAT/Device Flow

3. Push-Test aus Runtime
```bash
git push --dry-run origin HEAD:refs/heads/rdfa-33-access-check
```
Erwartung: kein Username/Auth-Fehler.

4. Access-Skript
```bash
bash scripts/check-github-access.sh
```
Erwartung: nur `PASS`, Exit-Code `0`.

## Owner: GitHub Org/App Admin

1. GitHub App/Connector auf Ziel-Account installieren/freigeben.
2. Ziel-Repository explizit in App-Installationsscope aufnehmen.
3. Minimalrechte für RDFA-Workflows setzen (Issues/PRs/Checks/Contents Read).
4. Verifikation: Connector zeigt installierte Accounts und Ziel-Repo.

## Abnahmekriterien

- `git push --dry-run` ohne Auth-Blocker.
- `scripts/check-github-access.sh` -> Exit `0`.
- Connector: installierte Accounts sichtbar, Ziel-Repo lesbar.
