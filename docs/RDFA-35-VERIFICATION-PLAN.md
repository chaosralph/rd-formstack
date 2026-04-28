# RDFA-35 Verifikationsplan - GitHub Access nach Infra-Freigabe

Stand: 2026-04-28 (UTC)

## Ziel
Nach bestätigter Infra-Freigabe den GitHub-Zugriff aus der Runtime reproduzierbar verifizieren und belastbare Evidence publizieren.

## Scope
- Runtime-seitiger GitHub CLI Zugriff (`gh`).
- Runtime-seitige Authentifizierung für Git Push.
- Repozugriff über GitHub App/Connector.
- Dokumentierte Evidence unter `artifacts/infra-access/` und `docs/`.

## Nicht im Scope
- Deployment in Staging/Produktion.
- Erweiterung von App-Permissions ohne Owner-Freigabe.

## Technische Voraussetzungen (Go-Bedingungen)
1. `gh` ist in der Runtime installiert und ausführbar.
2. Git Credential Flow für HTTPS Push ist verfügbar.
3. Ziel-Repository ist für App/Connector freigeschaltet.
4. Keine Secrets im Klartext in Repo, Logs oder Doku.

## Ablauf
1. Access-Checks ausführen:
- `bash scripts/check-github-access.sh`

2. Push-Probe ohne Side Effects:
- `git push --dry-run origin HEAD:refs/heads/rdfa-35-access-check`

3. Connector-Zugriff validieren:
- API-/App-Read auf User, Installationen und Ziel-Repo.

4. Evidence publizieren:
- Logs in `artifacts/infra-access/` aktualisieren.
- Statusdokument in `docs/` mit Zeitstempel und Ergebnissen aktualisieren.

## Abnahmekriterien (Done)
- `check-github-access.sh` meldet nur PASS.
- `git push --dry-run` endet ohne Auth- oder Host-Key-Fehler.
- Repozugriff via App/Connector bestätigt.
- Evidence vollständig, reproduzierbar und ohne Secret-Leak.

## Risiken und Gegenmaßnahmen
- Risiko: Token/Secrets in Logs.
  - Gegenmaßnahme: Nur Statuscodes und Fehlertypen loggen, keine Token-Werte.
- Risiko: Teilfreigabe (CLI ok, App blockiert).
  - Gegenmaßnahme: Separate Gates je Zugriffspfad, keine Sammelabnahme.
- Risiko: False Positive durch lokale Credentials außerhalb Runtime.
  - Gegenmaßnahme: Verifikation ausschließlich in der zu freigebenden Runtime.

## Owner-Zuordnung
- Infra/DevOps: Runtime-Pakete, Credential-Helper, Netzwerkpfad.
- GitHub Org/App Admin: Installation/Freigabe der App auf Zielaccount/-repo.
- CTO/Engineering: Abnahme der Evidence und Go/No-Go Entscheidung.
