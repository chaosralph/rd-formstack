# RDFA-42 Developer-Agent Tasks

Stand: 2026-04-28 (UTC)
Abhaengigkeit: RDFA-32 Access-Enablement teilweise offen (GitHub Auth/Scope)

## Ausfuehrungsreihenfolge
1. Agent A (Platform Verification)
2. Agent B (Security Hardening)
3. Agent C (Data/Domain Baseline)
4. Agent D (QA/Automation)

## Agent A - Platform Verification (P0)
Verantwortung: Verifikationspfad stabil und reproduzierbar halten.

Aufgaben:
1. `scripts/check-rdfa42-revalidation.sh` in CI-kompatiblen Ablauf integrieren.
2. Artefaktpfade unter `artifacts/infra-access/` konsistent dokumentieren.
3. Fehlerbilder fuer GitHub-Zugriff in operativen Runbook-Hinweisen pflegen.

DoD:
1. Revalidation-Skript lokal reproduzierbar.
2. PASS/FAIL-Summary eindeutig und maschinenlesbar.
3. Kein Secret-Leak in Artefakten.

## Agent B - Security Hardening (P0/P1)
Verantwortung: Risiken aus der Re-Validation reduzieren.

Aufgaben:
1. Rate-Limiting-Konzept fuer Kontaktformular als RFC + Minimal-Implementierung vorbereiten.
2. Negative Security-Tests fuer CSRF und Validierungsgrenzen ergaenzen.
3. Security-Header-Revalidation gegen `docs/qa-checklist.md` ausbauen.

DoD:
1. Abuse-Schutzkonzept dokumentiert und implementierbar.
2. Security-Negativtests reproduzierbar.
3. Checkliste mit klaren PASS/FAIL-Kriterien ergaenzt.

## Agent C - Data/Domain Baseline (P1)
Verantwortung: Erweiterbarkeit fuer Formstack-nahe Workflows.

Aufgaben:
1. Migrationsplan fuer `forms`, `submissions`, `submission_events` erstellen.
2. Repository-Interfaces fuer neue Entitaeten entwerfen (PDO/Prepared-Statement-only).
3. Eingabedaten-Schema inkl. Feldlimits zentralisieren.

DoD:
1. SQL-Migrationen reviewfaehig.
2. Repository-Schnittstellen konsistent mit bestehender Architektur.
3. Keine Business-Logik im Controller.

## Agent D - QA/Automation (P1)
Verantwortung: technische Grundlage regressionssicher machen.

Aufgaben:
1. Smoke + Lint + Security-Negativtests als einheitliches QA-Gate zusammenfuehren.
2. Revalidation-Lauf als Evidence-Check in QA-Dokumentation aufnehmen.
3. Fehlerfall-Matrix (DB down, CSRF fail, Validation fail, GitHub access fail) dokumentieren.

DoD:
1. QA-Gate kann lokal und in CI reproduziert werden.
2. Jeder kritische Fehlerpfad hat einen Test/Nachweis.
3. Dokumentation zeigt klare Verantwortlichkeit und Eskalationsweg.

## Governance
1. Kein Deployment ohne explizite Freigabe.
2. Keine hardcodierten Secrets.
3. PDO + Prepared Statements als verbindlicher Datenbankstandard.
