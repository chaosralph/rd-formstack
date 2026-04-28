# RDFA-43 CTO ETA Follow-up - Unblock-Zeitpunkt fuer RDFA-25/26

Stand: 2026-04-28 (UTC)
Status: in_progress
Prioritaet: high

## Zweck
Belastbare Zeitabschaetzung fuer den Unblock von RDFA-25 (CI Required Checks Evidence) und RDFA-26 (Release Hygiene Evidence) auf Basis der verifizierten Rest-Blocker aus RDFA-40 und RDFA-42.

## Aktueller Ist-Stand (verifiziert)
1. Lokale technische Grundlage fuer RD Formstack Solutions ist funktionsfaehig:
- PHP Runtime: PASS
- PDO/Prepared-Statement-Basis: vorhanden
- Smoke Routes: PASS
- PHP Lint: PASS

2. Externer Blocker bleibt aktiv (GitHub Access-Pfad):
- `git ls-remote origin HEAD` FAIL (HTTPS Credential fehlt)
- `git push --dry-run ...` FAIL (Auth fehlt)
- GitHub Connector: keine Installationen/Repos sichtbar (`list_installations = []`, `list_repositories = []`)
- Sammelcheck `scripts/check-rdfa40-unblock.sh`: `0 PASS / 2 FAIL`

3. Konsequenz:
- RDFA-25 kann keinen Live-GitHub-Actions-Nachweis liefern.
- RDFA-26 kann keinen finalen Release-Hygiene-Endnachweis mit Push/PR/Run-URL liefern.

## Vollstaendige Blocker-Kette
1. `RDFA-25` blocked durch fehlenden CI-Live-Nachweis.
2. `RDFA-39` blocked als Vorbedingung fuer den operativen Abschluss von RDFA-25.
3. `RDFA-40` blocked durch fehlende GitHub-Auth/Repo-Access-Pfade.
4. `RDFA-32` blocked als uebergeordnete Access-Enablement-Abhaengigkeit.
5. Zusatz: `gh` in Runtime weiterhin nicht verfuegbar, daher kein CLI-basierter Fast-Path.

## Fast-Track Entscheidungspfad (CTO)
Ziel: RDFA-25/26 sofort abschliessbar machen, ohne auf den kompletten `gh`-Pfad zu warten.

Option A (bevorzugt): Temporaerer HTTPS Repo-Token Bypass
- Freigabe: kurzlebiger Repo-Token mit minimalem Scope (nur erforderliche Repo-Operationen).
- Nutzen: Push und Workflow-Trigger sofort moeglich, auch ohne `gh`.
- ETA nach Freigabe: 60-120 Minuten bis RDFA-25/26 Abschlussnachweise.

Option B: Connector-Freigabe Bypass
- Freigabe: App-Installation und Repo-Scope fuer `chaosralph/rd-formstack` direkt aktivieren.
- Nutzen: Evidence kann ueber Connector + Git erfolgen, ohne lokale `gh`-Abhaengigkeit.
- ETA nach Freigabe: 90-180 Minuten bis RDFA-25/26 Abschlussnachweise.

Option C: Warten auf vollstaendige RDFA-32/40 Normalisierung
- Freigabe: keine Sonderfreigabe, regulaerer Access-Fix.
- Risiko: hoehere Wartezeit durch mehrfache Admin-Iteration.
- ETA: siehe Realistic/Worst Case unten.

CTO-Entscheidungsregel:
1. Wenn bis 2026-04-29 10:00 UTC keine Vollfreigabe fuer `gh` vorliegt, Option A sofort freigeben.
2. Falls Option A policy-seitig nicht zulaessig ist, Option B als verpflichtenden Fallback aktivieren.
3. Option C nur bei expliziter Security/Compliance-Auflage ohne Bypass-Moeglichkeit.

## Verbindliche CTO-Entscheidung (bestaetigt)
1. Bypass bestaetigt: Option A (temporaerer HTTPS Repo-Token) ist freigegeben als primarer Entblockungsweg.
2. Fallback fixiert: Wenn Option A technisch/policy-seitig scheitert, Option B ohne weitere Wartezeit aktivieren.
3. Verbindliche ETA:
- Zielabschluss RDFA-25/26: spaetestens 2026-04-29, 15:00 UTC (inkl. Option-B-Fallback).
- Stretch-Ziel bei Option A ohne Rework: 2026-04-29, 12:00 UTC.
4. Eskalationsgrenze:
- Wenn bis 2026-04-29 11:00 UTC kein funktionierender Access-Pfad (A oder B) PASS liefert, sofortige Infra-/Org-Admin-Eskalation auf P1.

## ETA fuer Unblock RDFA-25/26
Die ETA haengt vollstaendig von externen Infra-/Org-Admin-Aktionen ab.

1. Best Case
- Voraussetzungen: GitHub CLI Auth + HTTPS Credential + App-Installation mit Repo-Scope werden ohne Iteration korrekt gesetzt.
- Unblock-Fenster: 2-4 Stunden nach Start der Admin-Arbeiten.
- Datum/Zeit (UTC): 2026-04-28, 23:00 bis 2026-04-29, 01:00.

2. Realistic Case
- Voraussetzungen: 1-2 Korrekturschleifen bei Auth/App-Scope noetig.
- Unblock-Fenster: 1 Arbeitstag nach Start der Admin-Arbeiten.
- Datum/Zeit (UTC): 2026-04-29, 18:00 bis 2026-04-29, 22:00.

3. Worst Case
- Voraussetzungen: fehlende Org-Admin-Verfuegbarkeit, Scope-Freigaben oder Credential-Policy-Rueckfragen.
- Unblock-Fenster: 2-3 Arbeitstage nach Start der Admin-Arbeiten.
- Datum/Zeit (UTC): 2026-04-30, 18:00 bis 2026-05-01, 22:00.

## Aktualisierte CTO-ETA (mit Fast-Track)
1. Fast-Track via Option A: Abschluss RDFA-25/26 bis 2026-04-29, 12:00 UTC realistisch.
2. Fast-Track via Option B: Abschluss RDFA-25/26 bis 2026-04-29, 15:00 UTC realistisch.
3. Ohne Fast-Track (Option C): Abschluss unveraendert im Fenster 2026-04-29 bis 2026-05-01 UTC.

## Operative 24h-Checkliste (ausfuehrbar)
1. Bis 2026-04-29 09:30 UTC: Infra/Admin meldet `gh`-Vollfreigabe oder Ablehnung.
2. 2026-04-29 10:00 UTC: CTO-Go/No-Go nach Entscheidungsregel (Option A/B/C).
3. Direkt nach Go:
- Agent A startet Access Re-Validation.
- Agent B bereitet CI-Evidence-Refresh fuer RDFA-25 vor.
- Agent C bereitet Release-Hygiene-Refresh fuer RDFA-26 vor.
4. Innerhalb 30 Minuten nach Access-OK:
- Push/Workflow ausfuehren.
- Run-URL und Jobstatus dokumentieren.
5. Abschluss:
- RDFA-25 und RDFA-26 Evidence-Dateien mit Zeitstempel aktualisieren.
- Offene Restrisiken als Follow-up-Tickets markieren.

## Technischer Plan nach Unblock (RDFA-25/26 Abschluss)
1. Zugriffspfad revalidieren
- `bash scripts/check-github-access.sh`
- `git ls-remote origin HEAD`
- `git push --dry-run origin HEAD:refs/heads/rdfa-43-access-check`

2. CI-Nachweis erzeugen (RDFA-25)
- Branch pushen
- Workflow `required-checks.yml` laufen lassen
- Erfolgreiche Run-URL + Jobnamen (`php-lint`, `smoke-routes`) dokumentieren

3. Release-Hygiene finalisieren (RDFA-26)
- Evidenz auf finalen Push/PR/Checks-Stand aktualisieren
- Tag-Strategie verifizieren (`rdfa-26-release-hygiene-2026-04-28` oder Folge-Tag mit aktuellem Datum)

4. Abschlusskriterien
- Keine FAILs in Access-Checks
- Mindestens ein erfolgreicher Workflow-Run mit nachvollziehbarer URL
- Aktualisierte Evidence-Dokumente mit Zeitstempel

## Developer-Agent Aufgaben (klar geschnitten)
1. Agent A - Access Re-Validation
- Fuehrt die drei Access-Pruefungen aus.
- Schreibt Artefakte nach `artifacts/infra-access/`.
- Ergebnisformat: PASS/FAIL je Pfad inkl. Zeitstempel.

2. Agent B - CI Evidence Refresh (RDFA-25)
- Fuehrt Push/Run nach freigegebenem Access aus.
- Dokumentiert Run-URL, Jobstatus und Re-Run-Hinweise in `docs/RDFA-25-CI-EVIDENCE.md`.

3. Agent C - Release Hygiene Refresh (RDFA-26)
- Aktualisiert Commit-/Tag-/QA-Evidenz auf den finalen Stand.
- Dokumentiert Rest-Risiken und entfernt veraltete Blocker-Texte nach Verifikation.

## Sicherheitsrisiken und Controls
1. Hoch
- Fehlender GitHub Access verhindert kontrollierte CI- und Release-Nachweisfuehrung.
- Control: Kein Go fuer RDFA-25/26 bei einem einzelnen Auth/Scope-FAIL.

2. Mittel
- Form-POST ohne dediziertes Rate-Limiting.
- Control: Rate-Limit-Task als naechster Hardening-Block nach RDFA-25/26 Abschluss einplanen.

3. Mittel
- Fehlende automatisierte Negativtests fuer CSRF/Validation-Missbrauch.
- Control: Security-Regression-Suite in CI nachziehen.

## No-Deployment Hinweis
Kein Deployment ohne explizite Freigabe. RDFA-43 liefert nur ETA, Plan und Task-Schnitt fuer den Unblock von RDFA-25/26.

## Referenzen
- `docs/RDFA-40-STATUS-UPDATE.md`
- `docs/RDFA-42-CTO-REVALIDATION.md`
- `docs/RDFA-25-CI-EVIDENCE.md`
- `docs/RDFA-26-RELEASE-HYGIENE-EVIDENCE.md`
- `artifacts/infra-access/rdfa40-unblock-summary.log`
