# RDFA-52 - Security Scan Report

Stand: 2026-04-29 (UTC)  
Owner: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## Scope
- Schnellscan auf:
  - hardcodierte Secrets,
  - SQL-Zugriffsmuster,
  - URL-/Header-/Rate-Limit-Konfigurationspunkte.

## Findings

## F1 - SQL Zugriffspfad aktuell via Prepared Statement
Status: OK  
Beleg:
- `src/Repository/ContactRepository.php` nutzt `prepare()` + `execute()`.
Risiko:
- Niedrig, solange keine direkten `query/exec`-Writes eingeführt werden.

## F2 - Keine offensichtlichen hardcodierten Secrets im Applikationscode
Status: OK  
Beleg:
- DB-Passwort wird aus Env gelesen (`config/database.php`).
Hinweis:
- Secret-Strings im Logger betreffen Filter/Redaction und sind kein Leak.

## F3 - Parallel vorhandene Architekturpfade im Codebestand
Status: Beobachtung / P1  
Beleg:
- Neben `src/Http/*` existieren auch Pfade in `src/Controller/*` und `src/Support/*` für ähnliche Themen.
Risiko:
- Architektur-Drift und uneinheitliche Security-Policies.
Maßnahme:
- Lead Developer soll einen konsolidierten Pfad festlegen und redundante Pfade entkoppeln/deprecaten.

## F4 - `HTTP_HOST` weiterhin im Front Controller vorhanden
Status: Beobachtung / P1  
Beleg:
- `public/index.php` enthält weiterhin Host-Fallback.
Risiko:
- Fehlkonfiguration in Staging/Prod möglich, wenn `APP_BASE_URL` nicht gesetzt ist.
Maßnahme:
- Deployment-Gate: `APP_BASE_URL` in nicht-dev Umgebungen verpflichtend validieren.

## Lead-Developer Aufgaben (neu)
1. Architekturpfad-Konsolidierung (`src/Http` vs `src/Controller`/`src/Support`) mit klarer Zielstruktur.
2. Env-Guard hinzufügen: in `staging|production` harter Fehler bei fehlendem `APP_BASE_URL`.
3. QA-Check ergänzen: kein neuer direkter DB-Write ohne `prepare()`.

## Governance
- Kein Deployment ohne Freigabe.
