# DMS – Dokumentenmanagementsystem

Dokumentenmanagementsystem für **rd.timepro-solutions.de** – Fotos aufnehmen oder hochladen, automatisch als PDF speichern, verwalten und exportieren.

## Features

- **Foto-Upload**: Kamera-API (direkt im Browser) oder Datei-Upload (Drag & Drop)
- **Bildbearbeitung**: Zuschneiden und Drehen mit Cropper.js
- **Mehrseitige PDFs**: Mehrere Bilder zu einem PDF zusammenfassen
- **Seitenreihenfolge**: Per Drag & Drop umsortieren
- **Kategorien**: Dokumente in Kategorien organisieren
- **Suche**: Volltextsuche über Titel und Beschreibung
- **Export**: Einzelne PDFs oder alle als ZIP-Archiv herunterladen
- **Responsives Design**: Optimiert für Desktop und Mobile
- **Dark Theme**: Modernes Design passend zu rd.timepro-solutions.de

## Tech-Stack

- **Backend**: PHP 8.1+ mit TCPDF und GD
- **Frontend**: Vanilla JavaScript mit Cropper.js
- **Datenbank**: MySQL / MariaDB
- **Hosting**: Lima-City Starter (50 GB SSD)

## Installation

Siehe [dms/INSTALLATION.md](dms/INSTALLATION.md) für die vollständige Installationsanleitung.

## Projektstruktur

```
dms/
├── api/                    # REST-API Endpoints
│   ├── upload.php          # Bild-Upload → PDF-Konvertierung
│   ├── documents.php       # CRUD für Dokumente
│   ├── document-update.php # Dokument bearbeiten
│   ├── categories.php      # Kategorieverwaltung
│   └── export.php          # PDF/ZIP-Export
├── assets/
│   ├── css/style.css       # Stylesheet (Dark Theme)
│   └── js/
│       ├── app.js          # Hauptseite (Galerie, Modals, Suche)
│       └── upload.js       # Upload (Kamera, Cropper, Drag&Drop)
├── includes/
│   ├── Database.php        # PDO-Verbindung (Singleton)
│   ├── PdfGenerator.php    # TCPDF-Wrapper
│   └── helpers.php         # Hilfsfunktionen
├── templates/
│   ├── header.php          # HTML-Header + Navigation
│   └── footer.php          # Footer + Modals
├── uploads/                # Datei-Speicher
│   ├── originals/          # Original-Bilder
│   └── pdfs/               # Erzeugte PDFs + Thumbnails
├── config.php              # Konfiguration
├── index.php               # Dashboard / Dokumenten-Galerie
├── upload.php              # Upload-Seite
├── login.php               # Login-Seite
├── setup.sql               # Datenbank-Schema
├── composer.json            # PHP-Abhängigkeiten
├── .htaccess               # Apache-Konfiguration
└── .user.ini               # PHP-FPM-Konfiguration
```
