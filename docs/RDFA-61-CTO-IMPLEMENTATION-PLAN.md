# RDFA-61 - CTO Implementation Plan

Stand: 2026-04-29 12:02 UTC  
Owner: CTO Agent (d95a4a3e-d8db-4baf-9f18-4f3b655b7cc7)

## Ziel
Technische Grundlage in drei kontrollierten Ausbaustufen stabilisieren:
- Security-Event-Coverage vervollstaendigen
- Dokumentations-Drift bereinigen
- QA-/Evidence-Hygiene standardisieren

## Architekturleitlinien (verbindlich)
1. PHP-Web-Flow nur ueber `public/index.php` + `src/Http/*` + `src/Controller/*`.
2. Datenzugriff nur per PDO mit Prepared Statements.
3. Secrets ausschliesslich via Environment/Config-Loader, nie im Code.
4. Keine Deployments ohne explizite Freigabe.

## Umsetzungsplan

### Phase A - Security Event Coverage (P1)
Scope:
- Kritische Pfade: CSRF-Fehler, Rate-Limit-Degradation, Host/Header-Anomalien, DB-Write-Fehler.
Deliverables:
- Einheitliches Event-Schema:
  - `event_type`
  - `severity`
  - `request_id`
  - `context` (minimiert, ohne PII/Secrets)
- Evidence mit Beispielereignissen fuer jeden kritischen Pfad.
Abnahme:
- Ereignisse reproduzierbar im lokalen Lauf nachweisbar.

### Phase B - Doku-Konsolidierung (P1)
Scope:
- Aktive Doku auf aktuelle Pfade fixieren:
  - `src/Controller/ContactController.php` aktiv
  - `src/Http/ContactController.php` nur historischer Verweis in Altartefakten
Deliverables:
- Index/Architekturseiten ohne aktive Verweise auf alte Pfade.
- Historische RDFA-Dokumente bleiben unveraendert, aber als historisch gekennzeichnet.
Abnahme:
- Keine inkonsistenten aktiven Pfadangaben in Kern-Doku.

### Phase C - QA Evidence Hygiene (P1)
Scope:
- Einheitlicher Evidenzstandard pro Lauf.
Deliverables:
- Fester Ordnerstandard je Zyklus mit:
  - Lint-Ergebnis
  - QA-Gate-Log (`--strict=1`)
  - Secrets-Scan-Log
  - Kurzprotokoll (Zeitpunkt, Commit-Stand, Ergebnis)
Abnahme:
- Vollstaendige und wiederholbare Evidence fuer mindestens einen aktuellen Zyklus.

## Security-Risiken (aktuell)
1. Unvollstaendige Event-Abdeckung erschwert Incident-Triage.
2. Doku-Drift erzeugt Review-/Onboarding-Fehler.
3. Uneinheitliche Evidence-Struktur erhoeht Audit-Risiko.

## No-Go Kriterien
- Neue DB-Writes ohne Prepared Statements.
- Security-Logs mit PII/Secrets.
- Deployment-Aktionen ohne Freigabe.

## Verifikation (Pflicht)
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/qa-gate.sh --strict=1`

## Governance
- Kein Deployment in diesem Plan.
