# RDFA-36 Developer-Agent Tasks

Stand: 2026-04-28 (UTC)

## Agent A - DB Migration Rollout
Aufgaben:
- `database/migrations/002_add_company_phone_to_contacts.sql` in der Ziel-DB ausfuehren.
- Schema mit `SHOW CREATE TABLE contacts;` verifizieren.
- Ergebnis und Zeitstempel dokumentieren.

DoD:
- Spalten `company` und `phone` vorhanden.
- Migration laeuft ohne Fehler.

## Agent B - Data Backfill
Aufgaben:
- Bestehende Datensaetze pruefen, ob `company`/`phone` aus historischen Nachrichten extrahierbar sind.
- Optionales Backfill-Skript vorbereiten und Dry-Run dokumentieren.

DoD:
- Backfill-Strategie dokumentiert (inkl. Grenzen und Fehlerquote).

## Agent C - QA & Security Regression
Aufgaben:
- `composer run check:runtime` und PHP-Lint erneut ausfuehren.
- Kontaktformular-Ende-zu-Ende pruefen (inkl. CSRF/Validation).
- Regression gegen `docs/qa-checklist.md` dokumentieren.

DoD:
- Alle Pflichtpruefungen PASS.
- Keine neuen Security-Befunde.

## Agent D - Release Governance
Aufgaben:
- CTO-Auflagen gegen RDFA-36 reviewen.
- Deployment-Freigabe explizit einholen und dokumentieren.

DoD:
- Freigabezustand eindeutig (GO/NO-GO) mit Owner und Datum.
