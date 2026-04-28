# RDFA-44 CTO Decision - Temporaerer Fallback ohne GitHub-Evidence fuer RDFA-25/26

Stand: 2026-04-28 (UTC)  
Status: in_progress  
Prioritaet: high

## Entscheidung
Solange der externe GitHub-Access-Blocker (`RDFA-32 -> RDFA-40 -> RDFA-39`) aktiv ist, werden RDFA-25 und RDFA-26 temporaer im Modus `conditional_accept` gefuehrt:
1. Technische Basis-Checks muessen lokal und reproduzierbar `PASS` liefern.
2. Fehlende Live-GitHub-Evidence (Workflow-Run-URL, Push/PR-Nachweis) wird als externer Blocker dokumentiert, nicht als Implementierungsfehler.
3. Nach Access-Unblock besteht eine verpflichtende Nachziehpflicht fuer GitHub-Evidence innerhalb eines festen Zeitfensters.

## Zweck des Fallbacks
1. Lieferfaehigkeit der technischen Grundlage sichern, ohne Compliance-Grenzen zu verletzen.
2. Trennung zwischen intern kontrollierbarer Qualitaet (Code, Security, lokale Checks) und externen Plattformabhaengigkeiten.
3. Revisionssichere Dokumentation fuer Audit/Stakeholder bis zur Aufloesung des Access-Blockers.

## Scope
Im Scope:
1. RDFA-25 CI Required Checks Evidence (lokal + dokumentiert).
2. RDFA-26 Release Hygiene Evidence (lokal + dokumentiert).
3. Access-Revalidation Artefakte unter `artifacts/infra-access/`.

Nicht im Scope:
1. Deployment.
2. Umgehung von Security-/Compliance-Policies.
3. Abschlussmeldung "done" ohne spaeteren GitHub-Nachzug.

## Akzeptanzkriterien (temporär)
1. `bash scripts/ci/php-lint.sh` -> PASS.
2. `bash scripts/ci/smoke-routes.sh` -> PASS.
3. `bash scripts/check-runtime.sh` -> READY/PASS.
4. RDFA-25 und RDFA-26 dokumentieren den Blocker mit Zeitstempel und Artefaktpfaden.
5. Kein hartcodiertes Secret im Repo; Konfiguration bleibt getrennt (`.env`, `config/`).

## Exit-Kriterien (Fallback endet)
Der Fallback endet erst, wenn alle Punkte erfuellt sind:
1. `git ls-remote origin HEAD` ohne Auth-Fehler.
2. Push auf Remote erfolgreich (mindestens dry-run + realer Push fuer Workflow-Trigger).
3. Erfolgreicher GitHub-Workflow-Run mit URL fuer `required-checks.yml`.
4. RDFA-25 und RDFA-26 auf "final evidence" aktualisiert.

## Sicherheitsrisiken und Controls
1. Risiko: Scheinabschluss ohne externe Nachweise.
   Control: Status bleibt `conditional_accept`; "done" erst nach Exit-Kriterien.
2. Risiko: Credential-Leak bei temporaerem Access-Bypass.
   Control: Kurzlebige Tokens, minimaler Scope, keine Ablage in Repo/Logs.
3. Risiko: Drift zwischen lokalem Teststand und spaeterem Remote-Stand.
   Control: Nachziehpflicht innerhalb 24h nach Access-OK inklusive identischer Skriptbasis.
4. Risiko: Fehlende Abuse-Mitigation auf Formular-Endpunkten.
   Control: Rate-Limiting als verpflichtender Folgeblock nach RDFA-25/26 Finalisierung.

## Technischer Umsetzungsplan
1. Fallback-Dokumentation in RDFA-25/26 konsistent hinterlegen.
2. Access-Revalidation zyklisch laufen lassen und Artefakte aktualisieren.
3. Bei Access-OK sofort CI-Run + Evidence-Nachzug ohne zusätzliche Wartezeit.
4. Danach Status von `conditional_accept` auf `final_accept` umstellen.

## Developer-Agent Aufgaben
1. Agent A - Access Revalidation
   Verantwortet `scripts/check-github-access.sh`, `git ls-remote origin HEAD`, `git push --dry-run`.
   Liefert PASS/FAIL mit UTC-Zeitstempel in `artifacts/infra-access/`.
2. Agent B - RDFA-25 Evidence Finalization
   Fuehrt nach Access-OK Push + Workflow-Trigger aus.
   Dokumentiert Run-URL und Jobstatus in `docs/RDFA-25-CI-EVIDENCE.md`.
3. Agent C - RDFA-26 Hygiene Finalization
   Aktualisiert Release-Hygiene auf finalen Remote-Stand (Push/PR/Tag/Checks).
   Bereinigt veraltete Blockertexte nach Verifikation.
4. Agent D - Security Hardening Follow-up
   Plant und implementiert Rate-Limiting + Security-Negativtests (CSRF/Validation Abuse) als Folgepaket.

## Governance
1. Kein Deployment ohne explizite Freigabe.
2. Keine Policy-Ausnahmen ohne dokumentierte CTO-Entscheidung.
3. Jede Statusaenderung braucht Datums-/Zeitstempel (UTC) und Artefaktverweis.
