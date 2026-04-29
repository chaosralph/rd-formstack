# RD Formstack Solutions - V1 Handoff

Stand: 2026-04-29 (UTC)

## Umfang V1
- Startseite mit Hero, Leistungsüberblick, Referenzen, FAQ, nächste Schritte
- Leistungsseite
- Referenzseite
- Kontaktbereich mit Formular, CSRF, Honeypot, Rate-Limit und validierter Eingabe
- Login-Platzhalter mit geplanter Feature- und Phasenansicht
- DMS-Platzhalter mit Roadmap- und Phasenansicht
- Responsive Navigation inkl. Mobile-Fokus-Handling

## Technische Punkte
- Routing über `public/index.php` + `src/Http/Routing/RouteCatalog.php`
- Content-Modelle zentral in `src/View/HomepageContent.php`
- Security im Kontaktpfad:
  - CSRF-Validierung
  - Honeypot-Feld gegen Bot-Submits
  - IP-Rate-Limiter (`src/Security/IpRateLimiter.php`)
- SEO:
  - `robots.txt` mit Disallow für Platzhalterseiten
  - dynamischer `GET /sitemap.xml`
  - JSON-LD (Organization + WebSite)
  - 404 mit noindex statt Home-Fallback

## QA-Checks (lokal)
- `bash scripts/ci/php-lint.sh`
- `bash scripts/ci/smoke-routes.sh`
- optional: `php scripts/tests/contact-rate-limit-test.php`

## Hinweise für nächste Iteration
- Optionales E2E-Testing für Form-Fehlerszenarien erweitern
- Login/DMS von Platzhalter auf funktionale MVP-Features anheben
- Deployment-Freigabe und Remote-CI-Evidence separat nachziehen (externer Access-Pfad)
