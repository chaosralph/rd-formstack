# RDFA-33 Runbook: GitHub Auth + App Access

Stand: 2026-04-28 (UTC)

## Zweck
Reproduzierbare Verifikation für:
1) Git-Push aus Runtime
2) GitHub-App/Connector-Repozugriff
3) Headless Runtime Readiness für Evidence-Pfad

## Schritte

1. Runtime- und Tool-Baseline
```bash
bash scripts/check-runtime.sh
bash scripts/check-github-access.sh
```

2. Git Push Nachweis (ohne echten Schreib-Impact)
```bash
git push --dry-run origin HEAD:refs/heads/rdfa-33-access-check
```

3. Connector-/App-Zugriff verifizieren
- Erwartung: GitHub-Connector zeigt User, Installations-Accounts und Repository-Zugriff.
- Minimalchecks:
  - User-Login abrufbar
  - Installierte Accounts sichtbar
  - Ziel-Repository lesbar

4. Headless Evidence-Readiness
```bash
bash scripts/ci/responsive-evidence.sh
```
Erwartung: Artefakte unter `artifacts/qa/responsive/`.

## Relevante Evidenzpfade
- Git Push Dry Run Log: `artifacts/infra-access/git-push-dry-run.log`
- Headless Evidence Log: `artifacts/infra-access/headless-responsive.log`
- Responsive Artefakte: `artifacts/qa/responsive/`

## Blocker-Matrix
- `gh` fehlt in Runtime -> Owner: Plattform/Runtime (Infra/DevOps)
- Git over HTTPS ohne Credentials -> Owner: Plattform/Runtime (Infra/DevOps)
- GitHub App/Connector ohne installierte Accounts/Repos -> Owner: GitHub Org/App Admin
