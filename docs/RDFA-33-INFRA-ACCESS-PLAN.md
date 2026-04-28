# RDFA-33 Infra Access Enablement Plan

Stand: 2026-04-28 (UTC)
Scope: Umsetzung von RDFA-32 "Infra Access Enablement" mit Fokus auf GitHub Authentifizierung und GitHub-App-Zugriff.

## Zielbild

- Zugriff auf das Repository und Pull-Request-Workflows ist für autorisierte Teammitglieder reproduzierbar verfügbar.
- GitHub App ist auf Ziel-Organisation/Repository installiert und mit minimalen Rechten konfiguriert.
- Alle Nachweise sind dokumentiert, ohne Secrets im Repository zu speichern.

## Technischer Plan

1. Access-Baseline definieren
- Owner/Team-Liste und erforderliche Rollen dokumentieren (Admin, Maintainer, Developer, CI-Bot).
- Ziel-Repositories und Ziel-Organisationen festlegen.

2. GitHub Auth enablement
- Bevorzugt `gh auth login` (Web oder Device Flow).
- Alternative: `GITHUB_TOKEN` in lokaler Shell-Session für CI-nahe Skripte.
- Zugriff über `gh auth status` und API-Read-Test verifizieren.

3. GitHub App Access enablement
- Installation der App auf Ziel-Org/Repo prüfen.
- Erforderliche Berechtigungen gegen Least-Privilege-Matrix validieren.
- Zugriff auf PR/Issue/Checks-Endpunkte funktional prüfen.

4. Nachweisführung
- Verifikationsoutput in lokaler Doku ablegen.
- Risiken, offene Punkte und Owner klar markieren.

5. Governance
- Kein Deployment-Schritt in RDFA-33.
- Keine hardcodierten Secrets.
- Freigabe durch CTO/Owner vor produktionsrelevanten Berechtigungsänderungen.

## Sicherheitsrisiken und Gegenmaßnahmen

- Risiko: Zu breite App-Permissions.
  Gegenmaßnahme: Minimalrechte-Matrix, jährliche Revalidierung, 4-Augen-Freigabe.

- Risiko: Token-Leak in Shell-Historie oder Logs.
  Gegenmaßnahme: Keine Tokens in Dateien, keine Ausgabe von Tokenwerten, nur Status-Checks loggen.

- Risiko: Unklare Verantwortlichkeiten bei Access-Problemen.
  Gegenmaßnahme: Explizite Owner pro Phase und Eskalationspfad.

## Abnahmekriterien (Definition of Done)

- `scripts/check-github-access.sh` läuft lokal und liefert PASS/FAIL pro Check.
- Auth-Status und Zugriffstests sind reproduzierbar dokumentiert.
- App-Installationsstatus und Permissions sind nachvollziehbar dokumentiert.
- Keine Secrets im Repository und kein Deployment ausgelöst.
