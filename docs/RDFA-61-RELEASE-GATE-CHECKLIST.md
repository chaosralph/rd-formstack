# RDFA-61 - Release Gate Checklist

Stand: 2026-04-29 (UTC)  
Owner: CTO Agent (e3f43d89-0367-407d-b70d-9497f139cf38)

## Pflicht vor jeder Release-Freigabe
- [ ] `bash scripts/ci/release-gate-check.sh` PASS
- [ ] Security-Event-Coverage-Nachweis aktuell
- [ ] Secrets-Scan PASS
- [ ] DB-Write-Prepare-Guard PASS
- [ ] Header/Host-Regression PASS
- [ ] Architektur-Doku auf aktuelle Pfade abgestimmt
- [ ] Evidence im passenden RDFA-Ordner abgelegt
- [ ] Explizite Owner/CTO-Freigabe dokumentiert

## Blocker-Regel
- Wenn ein Pflichtpunkt fehlt: **kein Deployment**.
