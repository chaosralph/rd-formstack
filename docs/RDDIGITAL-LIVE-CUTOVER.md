# rddigital Live Cutover

## Ziel
Die neue PHP/MariaDB-Version von rd-formstack soll die aktuelle Node-Vorschauseite unter `/opt/rddigital` ersetzen und weiter hinter Traefik auf `rddigital.de`/`www.rddigital.de` laufen.

## Vorbereitete Dateien
- `Dockerfile`
- `docker-compose.live.yml`
- `.env.production.example`
- `deploy/update.sh`

## Wichtige Unterschiede zur bisherigen Live-Seite
- alt: Node-Container auf internem Port 3000
- neu: PHP/Apache-Container auf internem Port 80
- neu zusätzlich: MariaDB-Container
- Traefik bleibt auf Netzwerk `traefik-proxy`

## Erforderliche Live-.env
Aus `.env.production.example` eine `.env` erzeugen mit echten Passwörtern.
Mindestens prüfen:
- `APP_ENV=production`
- `APP_BASE_URL=https://rddigital.de`
- `APP_TRUSTED_HOSTS=rddigital.de,www.rddigital.de`
- `DB_HOST=rddigital-db`
- `DB_NAME=rd_formstack`
- `DB_USER=rd_user`
- `DB_PASS=<echt>`
- `DB_ROOT_PASSWORD=<echt>`

## Cutover-Schritte auf dem Host
1. Backup ist bereits vorhanden: `/root/backups/rddigital-pre-migration-20260630-151122.tgz`
2. Neues Repo nach `/opt/rddigital` legen
3. `.env.production.example` nach `.env` kopieren und Passwörter setzen
4. `bash deploy/update.sh`
5. Healthchecks:
   - `docker ps | grep rddigital`
   - `curl -I https://rddigital.de`
   - `curl -I https://www.rddigital.de`
   - `curl -I https://rddigital.de/kontakt`
6. Optional echten Testkontakt absenden und in `rddigital-db` prüfen

## Verifiziert vor Cutover
- lokaler Docker-Parallelstack gebaut
- lokale Routen 200/404 geprüft
- lokaler Testkontakt erfolgreich gespeichert
- keine harten Verweise auf `rd.timepro-solutions.de` im neuen Repo gefunden

## Noch offen vor echtem Go-Live
- echte Produktionspasswörter setzen
- neues Repo nach `/opt/rddigital` übernehmen
- öffentliches HTTPS nach Traefik prüfen
