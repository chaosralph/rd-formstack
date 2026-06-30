# DMS – Installationsanleitung

## Voraussetzungen

- **PHP** >= 8.1 (Backend SSH) / 8.4 (Frontend)
- **MySQL** / MariaDB
- **GD-Extension** (für Thumbnails) – auf lima-city standardmäßig aktiv
- **ZipArchive-Extension** (für ZIP-Export) – auf lima-city standardmäßig aktiv
- **Composer** (für TCPDF-Installation)
- **HTTPS** (erforderlich für Kamera-API im Browser)

## Schritt 1: Dateien hochladen

Lade den kompletten `dms/`-Ordner in dein Webspace-Verzeichnis hoch, sodass er unter `https://rd.timepro-solutions.de/dms/` erreichbar ist.

```
public_html/
├── index.php          (bestehende Startseite)
├── login.php          (bestehender Login)
├── dms/               ← HIER
│   ├── api/
│   ├── assets/
│   ├── includes/
│   ├── templates/
│   ├── uploads/
│   ├── config.php
│   ├── index.php
│   ├── upload.php
│   ├── login.php
│   ├── composer.json
│   ├── .htaccess
│   └── .user.ini
```

## Schritt 2: Composer-Abhängigkeiten installieren

Per SSH auf dem Server:

```bash
cd ~/public_html/dms
composer install --no-dev --optimize-autoloader
```

Dies installiert **TCPDF** in den `vendor/`-Ordner.

## Schritt 3: Datenbank einrichten

1. Erstelle eine neue MySQL-Datenbank über das lima-city Panel (oder nutze die bestehende)
2. Führe die SQL-Datei aus:

```bash
mysql -u DEIN_USER -p DEINE_DATENBANK < setup.sql
```

Oder kopiere den Inhalt von `setup.sql` in phpMyAdmin.

## Schritt 4: Konfiguration anpassen

Bearbeite `config.php`:

```php
define('DB_HOST', 'localhost');       // oder dein DB-Host
define('DB_NAME', 'deine_datenbank');
define('DB_USER', 'dein_db_user');
define('DB_PASS', 'dein_db_passwort');

define('SITE_URL', 'https://rd.timepro-solutions.de/dms');
```

## Schritt 5: Verzeichnis-Berechtigungen

```bash
chmod 755 uploads/
chmod 755 uploads/originals/
chmod 755 uploads/pdfs/
```

## Schritt 6: Testen

1. Öffne `https://rd.timepro-solutions.de/dms/` im Browser
2. Melde dich an
3. Lade ein Testbild hoch
4. Prüfe, ob die PDF korrekt erzeugt wird

## Integration mit bestehendem Login

Um das DMS mit dem bestehenden Login-System zu verknüpfen, gibt es zwei Möglichkeiten:

### Option A: Gemeinsame Session
Wenn das bestehende Login-System auch PHP-Sessions verwendet, kann die `checkAuth()`-Funktion in `includes/helpers.php` angepasst werden:

```php
function checkAuth(): void
{
    session_start();
    // Prüfe die Session-Variable des bestehenden Logins
    if (empty($_SESSION['user_logged_in'])) {  // anpassen!
        header('Location: https://rd.timepro-solutions.de/login.php');
        exit;
    }
}
```

### Option B: Eigener Login
Das DMS bringt ein eigenes Login-System mit (`dms/login.php`). Die Authentifizierung sollte in der Produktivumgebung gegen die bestehende Benutzerdatenbank geprüft werden.

## Fehlerbehebung

| Problem | Lösung |
|---------|--------|
| PDF wird nicht erzeugt | Prüfe ob `composer install` ausgeführt wurde |
| Upload schlägt fehl | Prüfe `.user.ini` (upload_max_filesize) |
| Kamera funktioniert nicht | HTTPS erforderlich, Browser-Berechtigung prüfen |
| Thumbnails fehlen | GD-Extension prüfen: `php -m \| grep gd` |
| ZIP-Export fehlerhaft | ZipArchive-Extension prüfen: `php -m \| grep zip` |

## Dateigrößen-Limits (Lima-City Starter)

- **Webspace**: 50 GB SSD
- **MySQL**: 10 GB
- **PHP Memory Limit**: 256 MB
- **Empfehlung**: Upload-Limit auf 20-25 MB pro Datei belassen
