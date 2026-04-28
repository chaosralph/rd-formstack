# Developer-Agenten Aufgabenpakete

1. Auth & Rollenmodell
- Tabellen für Benutzer, Rollen, Rechte.
- Login-Flow mit Passwort-Hashing und Session-Hardening.

2. Formstack-Workflow-Module
- Tabellen und Repositories für Formularvorlagen, Submissions, Status.
- Service-Schicht für Workflow-Schritte und Validierungsregeln.

3. API-Schicht
- JSON-Endpunkte für Formularverarbeitung.
- Einheitliche Fehlerstruktur, Request-Validierung, Rate-Limiting.

4. Observability
- Strukturierte Logs in `storage/logs`.
- Fehlerkorrelation (Request-ID) und Audit-Logging für kritische Aktionen.

5. Teststrategie
- PHPUnit-Setup.
- Integrationstests für Repository-Layer gegen Test-DB.
- Security-Tests für CSRF, Input-Validierung und Zugriffsschutz.
