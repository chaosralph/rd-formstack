# RDFA-46 CTO Implementation Plan

Stand: 2026-04-29 (UTC)

## Scope und Leitplanken
- Ziel: Neuaufbau der Alt-Website als moderne PHP/MySQL-Plattform mit klarer Domänentrennung.
- Nicht-Ziel: Pixel- oder HTML-1:1-Kopie der Legacy-Seite.
- Harte Leitplanken: Keine Secrets committen, keine produktiven Deployments ohne formale Freigabe.

## Zielbild V2

### 1) Architektur-Layer
- Presentation Layer: `public/` + View-Rendering + Routing, zuständig für HTTP-Ein-/Ausgabe.
- Application Layer: Use-Case-orientierte Services (z. B. Contact Intake, Content-Ausspielung).
- Domain Layer: Fachregeln, Validierung, Entitäten/Value Objects.
- Infrastructure Layer: PDO/MySQL, Repositories, Logging, Mail-/Webhook-Adapter (hinter Interfaces).

### 2) Sicherheitsmodell
- Secret-Management ausschließlich via Environment-Variablen (`.env` lokal, Secret Store in Staging/Prod).
- Default-deny für mutierende Endpunkte ohne CSRF-Token.
- Prepared Statements, serverseitige Validierung, Output-Escaping als Baseline.
- Security Header zentral im Bootstrap (CSP schrittweise von Report-Only zu Enforce).
- Rollenmodell für Admin-Funktionen (Least Privilege), Auditierbarkeit kritischer Änderungen.

### 3) Datenmodell (fachlich)
- `contacts`: eingehende Kontaktanfragen inkl. Status und Zeitstempel.
- `pages`/`content_blocks`: CMS-ähnliche Inhaltsstruktur für flexible Seitenausspielung.
- `media_assets`: Metadaten für Datei-Referenzen (kein Binary im DB-Core).
- `users` + `roles` + `user_role_map`: Admin-Zugriff und Berechtigungen.
- `audit_log`: revisionsrelevante Aktionen (wer, wann, was).

### 4) Umgebungen und Delivery
- Lokal: Entwickler-Workstation, lokale `.env`, lokale DB, keine externen Secrets.
- Staging: produktionsnah, Testdaten, CI-gesteuerte Deploy-Pipeline, Smoke/Regression vor Freigabe.
- Produktion: nur nach Gate-Freigaben, Change-Fenster und Rollback-Plan.
- Artefaktprinzip: Build once, promote same artifact lokal/staging/prod.

## Migrationsstrategie (Funktionalitäts-Parität)
- Discovery: Legacy-Funktionen in Capability-Matrix erfassen (Inhalt, Formulare, SEO, Integrationen).
- Priorisierung: Business-kritische Flows zuerst (Kontaktaufnahme, Kerninhalte, Navigation).
- Re-Design statt Replikation: UX/Markup modernisieren, aber Verhalten/Ergebnis erhalten.
- Strangler-Fig-Ansatz: Funktionen inkrementell auf V2 übernehmen; Legacy nur als Referenzsystem.
- Datenmigration: Mapping je Entität, idempotente Import-Skripte, Dry-Run auf Staging.
- Abnahme: Paritäts-Check anhand Akzeptanzkriterien pro Capability, nicht anhand HTML-Diff.

## Phasenplan
1. Phase 0 - Baseline und Architektur-Freeze
- Scope-Freeze der MVP-Capabilities, Ziel-Datenmodell und Sicherheitsbaseline beschließen.

2. Phase 1 - Foundation
- Schichtenstruktur, Routing, zentrale Fehlerbehandlung, Konfig/Secrets-Handling, Migrations-Framework stabilisieren.

3. Phase 2 - Core Capabilities
- Kontakt-Flow, Content-Ausspielung, Admin-Basisfunktionen, Logging/Audit.

4. Phase 3 - Migration und Härtung
- Legacy-Inhalte/Funktionen sukzessive übernehmen, Regressionstests, Security-Hardening.

5. Phase 4 - Staging Readiness
- End-to-End-Tests, Last-/Fehlerszenarien, Rollback-Proben, Betriebsdokumentation.

6. Phase 5 - Go-Live Readiness (ohne Deployment in diesem Auftrag)
- Finales CTO/QA-Go, Release-Checklist, formale Produktionsfreigabe vorbereiten.

## Risiko-Register
1. Legacy-Verhalten unvollständig verstanden
- Auswirkung: Fehlende Funktionsparität in kritischen Flows.
- Gegenmaßnahme: Capability-Matrix, Stakeholder-Walkthroughs, frühe Abnahme pro Flow.

2. Schema-Drift zwischen Migrationen und Code
- Auswirkung: Laufzeitfehler und inkonsistente Daten.
- Gegenmaßnahme: Versionierte SQL-Migrationen, Staging-Dry-Runs, CI-DB-Checks.

3. Sicherheitslücken durch inkonsistente Eingabe-/Ausgabevalidierung
- Auswirkung: XSS/CSRF/Injection-Risiko.
- Gegenmaßnahme: zentrale Security-Guards, Pflicht-Checks im Review, Security-Testfälle pro Release.

4. Parallelentwicklung erzeugt Integrationskonflikte
- Auswirkung: Verzögerungen, Regressionen.
- Gegenmaßnahme: kurze Branch-Lebensdauer, tägliches Rebase/Merge-Fenster, klare Ownership je Modul.

5. Unklare Betriebsvoraussetzungen für Staging/Prod
- Auswirkung: Release-Blocker spät im Prozess.
- Gegenmaßnahme: frühzeitiger Infra-Check, Runbook, Definition of Ready für Umgebungen.

## Entscheidungslog (ADR-ähnlich)
1. Entscheidung: Funktionalitäts-Parität statt HTML-Parität
- Status: Accepted (2026-04-29)
- Begründung: reduziert technische Altlasten, erlaubt modernes UX/Markup bei identischem Business-Outcome.

2. Entscheidung: Schichtenarchitektur mit separater Domain-Logik
- Status: Accepted (2026-04-29)
- Begründung: testbarer, wartbarer Code; klare Trennung zwischen Fachlogik und Infrastruktur.

3. Entscheidung: SQL-first Migrationen mit versionierten Skripten
- Status: Accepted (2026-04-29)
- Begründung: transparente DB-Änderungen, reproduzierbare Deployments, einfache Audits.

4. Entscheidung: Security-by-default im Bootstrap und in mutierenden Flows
- Status: Accepted (2026-04-29)
- Begründung: zentrale Durchsetzung minimiert Streuverluste und inkonsistente Implementierungen.

5. Entscheidung: Staging als Pflicht-Gate vor jeder Produktionsfreigabe
- Status: Accepted (2026-04-29)
- Begründung: reduziert Betriebsrisiko durch realitätsnahe Validierung inkl. Rollback-Probe.

## Abschlusskriterien für RDFA-46
- Zielbild V2 dokumentiert (Layer, Security, Datenmodell, Umgebungen).
- Migrationsstrategie auf Funktionsparität dokumentiert.
- Risiko-Register mit konkreten Gegenmaßnahmen dokumentiert.
- Entscheidungslog mit priorisierten Architekturentscheidungen dokumentiert.
