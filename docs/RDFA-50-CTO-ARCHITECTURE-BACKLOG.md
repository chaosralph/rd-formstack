# RDFA-50 - CTO Architecture Backlog

Stand: 2026-04-29 (UTC)  
Owner: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## Ziel
Verbindliche Priorisierung der nächsten Architekturarbeiten für RD Formstack Solutions
inklusive Sicherheitsrisiko-Bezug und Abnahmekriterien.

## Priorisiertes Backlog

## P0 - Security Event Coverage abschließen
Beschreibung:
- Einheitliches Security-Event-Schema in allen kritischen Pfaden durchziehen
  (CSRF, Rate-Limit, Validation-Denials, zentrale Error-Handler).
Risiko:
- Unvollständige Incident-Triage.
Lead-Developer Task:
- Events ergänzen und auf PII-freien Kontext prüfen.
DoD:
- Nachweis in Logs + Test/Evidence unter `docs/evidence/rdfa-50/`.

## P0 - Header-Policy automatisiert testen
Beschreibung:
- Regressionstests für `CSP_REPORT_ONLY` und `ENABLE_HSTS`.
Risiko:
- Sicherheitsheader regressieren unbemerkt.
Lead-Developer Task:
- Smoke-Checks für Header-Modi in QA-Skripte integrieren.
DoD:
- QA-Lauf zeigt Header-Checks als eigenen Abschnitt.

## P1 - Host-Hardening Guardrail
Beschreibung:
- Keine absolute URL mehr aus `HTTP_HOST`; nur `APP_BASE_URL`.
Risiko:
- SEO-/Link-Poisoning durch Host-Header-Manipulation.
Lead-Developer Task:
- Review-Guardrail dokumentieren und im QA-Gate verankern.
DoD:
- Checklist aktiv genutzt, keine neuen direkten `HTTP_HOST`-Abhängigkeiten.

## P1 - Architektur-DoD für neue mutierende Features
Beschreibung:
- Controller -> Application-Service -> Repository Pflicht für neue Write-Flows.
Risiko:
- Rückfall in enge Kopplung und schlecht testbare Pfade.
Lead-Developer Task:
- DoD in Entwicklerdoku und PR-Review-Template ergänzen.
DoD:
- PR-Review enthält expliziten Architektur-Checkpunkt.

## P2 - Datenmodell-V2 Vorplanung
Beschreibung:
- Zieltabellen (`pages`, `content_blocks`, `media_assets`, `users`, `roles`, `audit_log`) als Phasenplan.
Risiko:
- Spätere Erweiterungen ohne konsistente Struktur.
Lead-Developer Task:
- Sequenzierte Migrationsplanung (forward-only) entwerfen.
DoD:
- Technischer Migrationsplan inkl. Rollback-Strategie für Staging.

## Sicherheitsrisiko-Register
- SR-50-01 (P0): Security-Event-Lücken in kritischen Pfaden.
- SR-50-02 (P0): Fehlende Header-Regressionstests.
- SR-50-03 (P1): Potenzielle Re-Introduktion von Host-Header-Abhängigkeit.
- SR-50-04 (P1): Layer-Drift bei neuen mutierenden Features.
- SR-50-05 (P2): Ungesteuerte Datenmodell-Erweiterung.

## Governance
- Kein Deployment ohne explizite Freigabe.
- Änderungen nur mit lokaler Evidenz und aktualisierter Doku.
