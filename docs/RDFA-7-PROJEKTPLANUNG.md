# RDFA-7 Projektplanung RD Formstack Solutions

Stand: 2026-04-28 (UTC)  
Owner: CTO Agent

## 1) Ist-Analyse

### 1.1 Vorhandene technische Basis
- Runtime-Stack ist korrekt gesetzt: PHP 8.2+, MySQL/MariaDB, HTML/CSS/JS.
- Grundstruktur vorhanden:
  - `public/` Front Controller + Assets
  - `src/` HTTP, Security, Repository, DB
  - `config/` Environment + DB-Konfiguration
  - `database/migrations/` SQL-Migrationen
- DB-Zugriffe erfolgen via PDO; Prepared Statements werden verwendet.
- `CSRF`-Schutz und serverseitige Basisvalidierung im Kontaktformular sind implementiert.
- Secrets sind nicht hardcodiert; `.env.example` ist vorhanden.

### 1.2 Identifizierte Lücken
- Front Controller enthält Routing, Bootstrapping und Rendering in einer Datei (`public/index.php`), geringe Modularität.
- Keine zentrale Error-Handling-/Exception-Strategie für produktionsnahe Fehlerszenarien.
- Kein strukturiertes Application-Logging mit Request-Korrelation.
- Kein dediziertes Konfigurationsschema mit Validierung zwingender Variablen.
- Datenmodell ist auf Kontaktformular beschränkt; keine Kern-Domainmodelle für Formstack-Workflows.
- Keine Test-Baseline (Unit/Integration/Security-Smoke automatisiert).

### 1.3 Statusbewertung
- **Reifegrad:** Foundation vorhanden, aber noch nicht operationsfähig für produktive Workflow-Features.
- **Nächster Fokus:** Architekturhärtung + Erweiterungsfähige Domänenbasis + Qualitätsgates.

## 2) Zielarchitektur (v1)

### 2.1 Architekturrichtung
- Modulare, monolithische PHP-Anwendung mit klaren Schichten:
  1. `Http` (Request/Response/Controller)
  2. `Application` (Use Cases/Services)
  3. `Domain` (Entitäten/Regeln)
  4. `Infrastructure` (PDO, Repositories, Logging, Config)

### 2.2 Geplante Ordnerstruktur
- `src/Bootstrap/` App-Init (Env, Config, Session, Error-Handling)
- `src/Http/` Router, Controller, Middleware-nahe Komponenten
- `src/Application/` Use-Case-Services (z. B. FormSubmissionService)
- `src/Domain/` Value Objects, Entitäten, Policy-Regeln
- `src/Infrastructure/Persistence/` PDO-Repositories
- `src/Infrastructure/Logging/` Dateilogging + Request-ID

### 2.3 Technische Standards
- PDO mit `ERRMODE_EXCEPTION`, `ATTR_EMULATE_PREPARES=false`.
- Ausschließlich Prepared Statements.
- Konfiguration strikt über Environment, keine Secrets im Code.
- Einheitliche Input-Validierung (Whitelist-basierte Feldvalidierung).
- Output Escaping für alle dynamischen HTML-Ausgaben.

## 3) Security-Risikoanalyse (Top-Risiken)

1. **Fehleroffenlegung bei Runtime-Fehlern**
- Risiko: Stacktraces/DB-Fehler in Response.
- Maßnahme: Zentrales Exception-Handling, generische Fehlermeldungen, Logging intern.

2. **Fehlende Security-Header**
- Risiko: Clickjacking/MIME-Sniffing/Referrer-Leakage.
- Maßnahme: Standard-Header (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, baseline CSP).

3. **Unvollständige Request-Härtung**
- Risiko: Unbegrenzte Payloads, fehlende method/route guards.
- Maßnahme: Input-Limits, strict route-method mapping, zentrale Request-Validation.

4. **Mangelnde Nachvollziehbarkeit**
- Risiko: Incident-Analyse kaum möglich.
- Maßnahme: strukturiertes Logging mit `request_id`, Security-/Audit-Events.

5. **Kein automatisiertes Quality Gate**
- Risiko: Regressionen bei Security/DB-Logik.
- Maßnahme: PHPUnit + Integrationstests + verpflichtender QA-Workflow.

## 4) Team-Execution-Plan (Developer-Agenten)

## Phase A - Foundation Hardening (P0)
1. Bootstrap & Router entkoppeln
- Output: `Bootstrap`-Klasse, Router-Mapping, schlanker `public/index.php`.
- Abhängigkeit: keine.
- DoD: Requests laufen funktional unverändert, lint + smoke grün.

2. Error-Handling & Logging
- Output: Global Exception Handler, `storage/logs/app.log`, Request-ID.
- Abhängigkeit: Task A1.
- DoD: 500-Fehler werden intern geloggt, User erhält generische Fehlermeldung.

3. Security Header Baseline
- Output: zentrale Header-Policy in HTTP-Layer.
- Abhängigkeit: Task A1.
- DoD: Header in Response nachvollziehbar gesetzt.

## Phase B - Domain Enablement (P1)
1. Formstack-Domänenmodell
- Output: Migrationen für `forms`, `submissions`, `submission_status_events`.
- Abhängigkeit: Phase A abgeschlossen.
- DoD: Schema + Repositories + minimale CRUD-Use-Cases vorhanden.

2. Application Service Layer
- Output: `Application`-Services für Submission-Verarbeitung.
- Abhängigkeit: B1.
- DoD: Controller delegieren Geschäftslogik vollständig an Services.

3. API-Grundlagen
- Output: JSON-Endpunkte mit einheitlichem Fehlerformat.
- Abhängigkeit: B2.
- DoD: Validierungsfehler, Domänenfehler und Serverfehler konsistent.

## Phase C - Quality & Governance (P1/P2)
1. Test-Baseline
- Output: PHPUnit-Konfiguration, Unit + Integrations-Smoke.
- Abhängigkeit: Phase A.
- DoD: CI-lauffähige Testausführung lokal dokumentiert.

2. Security-QA-Checkliste operationalisieren
- Output: reproduzierbare QA-Checks mit PASS/FAIL-Entscheidung.
- Abhängigkeit: C1.
- DoD: Releases ohne QA-PASS werden blockiert.

3. Architektur-Governance
- Output: ADR-Template + Entscheidungslog.
- Abhängigkeit: keine.
- DoD: Kritische Architekturentscheidungen sind nachvollziehbar dokumentiert.

## 5) Konkrete Aufgaben pro Developer-Agent

1. **Agent A (Platform/Core)**
- Verantwortet Bootstrap, Router, Error-Handling, Logging.
- Dateien: `public/index.php`, `src/Bootstrap/*`, `src/Http/*`, `storage/logs/.gitkeep`.

2. **Agent B (Security)**
- Verantwortet Security-Header, Request-Validation-Härtung, CSRF-Regressionstests.
- Dateien: `src/Security/*`, `src/Http/*`, `docs/qa-checklist.md`.

3. **Agent C (Data/Domain)**
- Verantwortet Migrationen + Repositories für Formstack-Domain.
- Dateien: `database/migrations/*`, `src/Repository/*`, `src/Application/*`.

4. **Agent D (QA/Testing)**
- Verantwortet PHPUnit-Setup, Testdatenstrategie, Smoke-Test-Skripte.
- Dateien: `composer.json`, `phpunit.xml*`, `tests/*`, `scripts/*`.

## 6) Governance- und Deployment-Regel

- **Kein Deployment ohne explizite Freigabe.**
- Vor jeder produktionsnahen Änderung:
  1. Architektur-Auswirkung dokumentieren
  2. Security-Risiko prüfen
  3. QA-Gate (mind. P0) bestehen

## 7) Nächster unmittelbarer Umsetzungsauftrag

1. Phase-A Task A1+A2 priorisieren (Bootstrap/Router + Error/Logging).
2. Parallel Security-Baseline vorbereiten (A3).
3. Nach A-Abschluss erst Domain-Schema (B1) starten.
