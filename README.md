# RD Formstack Solutions

Professionelle Webentwicklung und Belegverwaltungssystem mit intelligenter Belegerkennung.

## Features

- 🏠 **Moderne Landingpage** mit Responsive Design
- 🔐 **Sicheres Login-System** mit JWT-Authentifizierung
- 📤 **Beleg-Upload** per Kamera oder Datei
- 🤖 **Intelligente Belegerkennung** (OCR) mit automatischer Kategorisierung
- 💰 **Buchungsvorschläge** für SKR 03 (Einnahmen/Ausgaben)
- 📊 **Dashboard** zur Verwaltung aller Belege
- 🔗 **Referenzseite** mit Links zu TimePro Solutions und RM CargoTec

## Installation

### Voraussetzungen

- PHP 7.4 oder höher
- MySQL 5.7 oder höher
- Apache mit mod_rewrite
- Optional: Tesseract OCR für erweiterte Belegerkennung

### Schritt 1: Datenbank konfigurieren

Bearbeiten Sie `config/config.php` und passen Sie die Datenbankverbindung an:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'rd_formstack');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Schritt 2: Installation ausführen

Öffnen Sie im Browser:
```
http://localhost/rd-formstack/install.php
```

Die Installation erstellt:
- Die Datenbank und alle Tabellen
- Das Upload-Verzeichnis
- Einen Standard-Admin-Benutzer

### Schritt 3: Standard-Login

Nach der Installation können Sie sich mit folgenden Daten anmelden:

- **E-Mail:** admin@rd-formstack.de
- **Passwort:** admin123

**WICHTIG:** Bitte ändern Sie das Passwort nach dem ersten Login!

## Verzeichnisstruktur

```
rd-formstack/
├── api/              # API-Endpunkte
│   ├── auth.php      # Authentifizierung
│   └── receipts.php  # Beleg-Verwaltung
├── assets/           # Frontend-Ressourcen
│   ├── css/
│   └── js/
├── classes/          # PHP-Klassen
│   ├── Auth.php
│   ├── JWT.php
│   └── ReceiptProcessor.php
├── config/           # Konfiguration
├── database/          # Datenbank-Schema
├── uploads/          # Hochgeladene Belege
├── index.php         # Landingpage
├── login.php         # Login-Seite
├── dashboard.php     # Dashboard
├── references.php    # Referenzseite
└── install.php       # Installations-Script
```

## Belegerkennung

Das System unterstützt verschiedene Methoden zur Belegerkennung:

1. **Tesseract OCR** (falls installiert)
2. **Basis-Erkennung** durch Dateinamen/Metadaten
3. **Manuelle Eingabe** (Fallback)

### Tesseract OCR installieren

**Windows:**
```bash
# Download von: https://github.com/UB-Mannheim/tesseract/wiki
# Nach Installation zu PATH hinzufügen
```

**Linux:**
```bash
sudo apt-get install tesseract-ocr tesseract-ocr-deu
```

**macOS:**
```bash
brew install tesseract tesseract-lang
```

## API-Endpunkte

### Authentifizierung

**POST /api/auth.php**
```json
{
  "email": "admin@rd-formstack.de",
  "password": "admin123"
}
```

**GET /api/auth.php?action=me**
- Header: `X-Auth-Token: <token>`

### Belege

**POST /api/receipts.php**
- Multipart-Form-Data mit `file`
- Header: `X-Auth-Token: <token>`

**GET /api/receipts.php?category=einnahmen&status=pending**
- Header: `X-Auth-Token: <token>`

## SKR 03 Buchungsvorschläge

Das System generiert automatisch Buchungsvorschläge basierend auf:

- **Kategorie** (Einnahmen/Ausgaben)
- **Steuersatz** (19%, 7%, 0%)
- **Textinhalt** (für Ausgaben-Konten)

### Einnahmen-Konten
- 8400: Erlöse 19% USt
- 8401: Erlöse 7% USt
- 8402: Erlöse 0% USt
- 8403: Erlöse ohne USt

### Ausgaben-Konten
- 3400: Wareneingang 19% USt
- 6000-6800: Verschiedene Betriebsausgaben

## Sicherheit

- Passwörter werden mit `password_hash()` gehasht
- JWT-Token für API-Authentifizierung
- Session-basierte Authentifizierung
- SQL-Injection-Schutz durch Prepared Statements
- XSS-Schutz durch HTML-Escaping
- File-Upload-Validierung

## Entwicklung

### Lokale Entwicklung

1. XAMPP/WAMP/MAMP installieren
2. Projekt in `htdocs` kopieren
3. Datenbank konfigurieren
4. `install.php` ausführen

### Produktion

1. Datenbankverbindung anpassen
2. `DEBUG` auf `false` setzen
3. Sichere JWT_SECRET generieren
4. Upload-Verzeichnis-Berechtigungen prüfen
5. SSL/TLS aktivieren

## Technologien

- **Backend:** PHP 7.4+
- **Frontend:** Vanilla JavaScript, CSS3
- **Datenbank:** MySQL 5.7+
- **OCR:** Tesseract (optional)
- **Authentifizierung:** JWT, Sessions

## Support

Bei Fragen oder Problemen kontaktieren Sie:
- **E-Mail:** info@rd-formstack.de

## Lizenz

Proprietär - RD Formstack Solutions
