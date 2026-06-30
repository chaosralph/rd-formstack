## Beleg-Upload als PDF (Fotos & Dateien)

### Ziel

- **Ist-Zustand (vorher)**:
  - Nutzer:innen können Belege über das Dashboard **fotografieren** (Kamera) oder als **Datei hochladen**.
  - Das System arbeitet intern primär mit **Bilddateien** (JPG/PNG).
  - PDFs werden zwar teilweise angenommen, aber die OCR ist für PDFs unzuverlässig (bzw. ohne spezielle Behandlung).
- **Zielzustand**:
  - **Fotos** (Kamera / Bild-Upload) werden nach Möglichkeit **automatisch als PDF gespeichert**.
  - **PDF-Dateien** werden zuverlässig angenommen und für die **Belegerkennung (OCR) vorbereitet**.
  - Nachgelagerte Systeme/Prozesse können sich darauf verlassen, dass **Belege immer als PDF vorliegen** (wo technisch möglich).

---

### Übersicht – Architektur

- **Frontend** (`dashboard.php`, `assets/js/dashboard.js`)
  - Bietet zwei Upload-Wege:
    - Kamera (`#cameraInput`, `accept="image/*"`).
    - Datei (`#fileInput`, `accept="image/*,application/pdf"`).
  - Sendet die ausgewählte Datei als `multipart/form-data` an `POST /api/receipts.php` (Feldname `file`).

- **API** (`api/receipts.php`)
  - Prüft Authentifizierung.
  - Leitet den Upload an `ReceiptProcessor::processReceipt()` weiter.

- **Backend / Verarbeitung** (`classes/ReceiptProcessor.php`)
  - Validiert Datei (Größe, Endung).
  - Speichert Datei im User-spezifischen Upload-Verzeichnis.
  - Führt OCR-Pipeline aus (OpenAI Vision → Tesseract → Fallback).
  - Speichert Belegdaten in `receipts` + erzeugt `booking_suggestions`.

- **Konfiguration** (`config/config.php`)
  - Steuert u. a. erlaubte Dateiendungen und Upload-Verzeichnis.

---

### Frontend-Verhalten

- **Kamera-Upload (`cameraInput`)**
  - `accept="image/*"` → Browser/Kamera liefert Bilder (z. B. JPG, HEIC).
  - Diese Bilder werden 1:1 an die API gesendet.

- **Datei-Upload (`fileInput`)**
  - `accept="image/*,application/pdf"` → erlaubt **Bilder & PDF**.
  - PDFs können direkt aus dem Dateisystem hochgeladen werden.

> Wichtig: Die eigentliche Entscheidung "was wird gespeichert / wie wird OCR gemacht" passiert im **Backend** (ReceiptProcessor).

---

### Backend – Upload-Validierung (`config/config.php`)

Relevante Konstanten:

- `UPLOAD_DIR`: Basisverzeichnis für alle Uploads (pro User wird ein Unterordner angelegt).
- `UPLOAD_MAX_SIZE`: Max. Dateigröße (aktuell 10 MB).
- `ALLOWED_EXTENSIONS`: **erlaubte Dateiendungen** für Belege:

```php
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif', 'pdf']);
```

Damit sind erlaubt:

- **Bilder**: `jpg`, `jpeg`, `png`, `gif`, `webp`, `heic`, `heif`
- **PDFs**: `pdf`

Die Validierung passiert in `ReceiptProcessor::validateFile()`:

- Prüft:
  - Ob tatsächlich eine Datei hochgeladen wurde.
  - Ob die Datei nicht größer als `UPLOAD_MAX_SIZE` ist.
  - Ob die Dateiendung in `ALLOWED_EXTENSIONS` enthalten ist.

---

### Backend – Speichern & PDF-Konvertierung (`ReceiptProcessor::saveFile()`)

`saveFile()` übernimmt jetzt neben dem Speichern auch:

1. **Speichern der Originaldatei** (z. B. Bild oder PDF).
2. **Optional: Konvertierung von Bildern → PDF** (für die eigentliche Ablage).
3. **Optional: Erzeugen eines OCR-Bilds aus PDF** (für bessere Texterkennung).
4. Rückgabe von:
   - Pfad zur gespeicherten Hauptdatei (in der Regel PDF).
   - Separatem Pfad für die OCR-Datei (typischerweise ein Bild).

#### Verhalten im Detail

- **Schritt 1: Original speichern**
  - Originaldatei wird unter `UPLOAD_DIR/<userId>/` mit einem eindeutigen Namen abgelegt.
  - Beispiel: `uploads/42/receipt_65f1c0b9c3e7a.jpg`

- **Schritt 2: Bild → PDF (Fotos als PDF speichern)**
  - Wenn die Dateiendung ein **Bildformat** ist (`jpg`, `jpeg`, `png`, `gif`, `webp`, `heic`, `heif`) **und** die PHP-Extension **Imagick** verfügbar ist:
    - Es wird ein **PDF** aus dem Bild erzeugt:
      - Beispiel: `uploads/42/receipt_65f1c0b9c3e7a.pdf`
    - Die **Hauptdatei** des Belegs ist dann dieses PDF:
      - `file_path` zeigt auf die PDF-Datei.
      - `file_type` wird auf `application/pdf` gesetzt.
    - Für die **OCR** wird weiterhin das ursprüngliche **Bild** verwendet (bessere Erkennung).
  - Falls **Imagick nicht verfügbar** ist:
    - Das Bild bleibt unverändert gespeichert.
    - `file_path` zeigt auf die Bilddatei.

- **Schritt 3: PDF → OCR-Bild (PDF lesbar machen)**
  - Wenn die hochgeladene Datei bereits ein **PDF** ist:
    - Die PDF-Datei wird unverändert als Hauptdatei gespeichert.
    - Wenn **Imagick** verfügbar ist:
      - Aus der **ersten Seite** des PDFs wird ein **JPEG-Vorschaubild** erzeugt (z. B. `receipt_65f1c0b9c3e7a_ocr.jpg`).
      - Dieses Bild wird als **OCR-Eingabe** genutzt.
    - Wenn **Imagick nicht** verfügbar ist:
      - Die OCR arbeitet direkt auf der PDF-Datei (je nach Tesseract-Konfiguration).

- **Rückgabe von `saveFile()`**
  - Enthält u. a.:
    - `file_path` – Pfad der gespeicherten Hauptdatei (idealerweise PDF).
    - `file_type` – MIME-Type der Hauptdatei (z. B. `application/pdf`).
    - `ocr_file_path` – Pfad der Datei, die für OCR genutzt wird (Bild oder PDF).
    - `ocr_file_type` – MIME-Type der OCR-Datei.

---

### Backend – OCR-Pipeline mit PDF-Unterstützung

In `processReceipt()` wird nach dem Speichern die OCR aufgerufen:

- Vorher:
  - OCR lief direkt auf `file_path` mit dem ursprünglichen MIME-Typ.
  - PDFs waren für OpenAI Vision **nicht geeignet** (erwartet Bildinput).

- Jetzt:
  - Es wird – wenn vorhanden – bevorzugt `ocr_file_path` inkl. `ocr_file_type` verwendet:
    - Für Bilder: OCR arbeitet auf dem **Originalbild**.
    - Für PDFs: OCR arbeitet auf dem **aus dem PDF gerenderten Bild**.
  - Die Methode `performOCR()` bleibt unverändert in ihrer Logik:
    1. Versuch **OpenAI Vision** (nur bei Bilddateien).
    2. Versuch **Tesseract OCR** (CLI).
    3. Fallback **Basis-Erkennung**.

Damit gilt:

- **Fotos** → werden als PDF gespeichert, aber OCR nutzt das Bild.
- **PDFs** → werden als PDF gespeichert, OCR nutzt ein gerendertes Bild (falls Imagick vorhanden).

---

### Datenbank – gespeicherte Metadaten

In der Tabelle `receipts` werden wie gewohnt u. a. folgende Felder befüllt:

- `file_path` – Pfad zur gespeicherten Datei (jetzt meist PDF).
- `file_type` – MIME-Type (oft `application/pdf`).
- `ocr_data` – JSON mit allen erkannten OCR-Informationen.
- `category`, `amount`, `tax_amount`, `vendor_name`, `invoice_number`, `invoice_date` etc.

Die interne Unterscheidung "wofür wurde welches File verwendet (Speicherung vs. OCR)" bleibt in der PHP-Schicht (`ReceiptProcessor`) und ist für die Datenbank **transparent**.

---

### Verhalten für Nutzer:innen (Fachseite)

- **Kamera-Upload**:
  - Nutzer:innen machen ein Foto des Belegs.
  - Das System speichert dieses Foto nach Möglichkeit **als PDF-Beleg**.
  - Die Belegerkennung nutzt das ursprüngliche Bild → hohe Erkennungsqualität.

- **Datei-Upload**:
  - Nutzer:innen können sowohl **Bilddateien** als auch **PDFs** hochladen.
  - PDFs werden intern behandelt wie Belege aus einem Scanner:
    - PDF bleibt als PDF gespeichert.
    - Für die Erkennung wird eine Bild-Vorschau erzeugt (sofern möglich).

- **Aus Sicht der Buchhaltung/Weiterverarbeitung**:
  - Es liegen konsistent **PDF-Belege** vor (Fotos werden dorthin konvertiert, PDFs bleiben PDFs).
  - Die Belegerkennung (OCR) funktioniert auch mit **PDF-Belegen**, da diese intern auf Bildbasis verarbeitet werden.

---

### Deployment-Hinweise

1. **Dateien aktualisieren**
   - `config/config.php`
   - `classes/ReceiptProcessor.php`
   - `docs/RECEIPT-PDF-UPLOAD.md` (diese Datei)

2. **Server-Voraussetzungen prüfen**
   - PHP-Version gemäß README (7.4+ bzw. 8.x).
   - **Optional aber empfohlen:** PHP-Extension **Imagick**:
     - Für Windows: Imagick + ImageMagick installieren und in `php.ini` aktivieren.
     - Für Linux: z. B. `sudo apt install php-imagick`.
   - Tesseract-OCR gemäß README installieren, wenn die erweiterte OCR genutzt werden soll.

3. **Rechte prüfen**
   - Verzeichnis `uploads/` muss für den Webserver **schreibbar** sein.

4. **Smoke-Test**
   - Mit einem Test-User einloggen.
   - **Foto aufnehmen**:
     - Erwartung: Upload erfolgreich, Beleg im Dashboard sichtbar, OCR-Ergebnis angezeigt.
     - Im Dateisystem sollte ein PDF im Nutzer-Upload-Ordner liegen.
   - **PDF hochladen**:
     - Erwartung: Upload erfolgreich, OCR versucht, Text zu erkennen (ggf. über gerendertes Bild).
     - Beleg im Dashboard sichtbar, keine Fehlermeldung „ungültiger Dateityp“.

---

### Kurzfassung für Implementierer:innen

- **Konfiguration**:
  - `ALLOWED_EXTENSIONS` um weitere Bildformate + `pdf` erweitert.
- **Speicherung**:
  - Bilder → nach Möglichkeit als PDF gespeichert (Imagick).
  - PDFs → unverändert gespeichert.
- **OCR**:
  - Arbeitet auf separater OCR-Datei:
    - Bild (Originalfoto) oder
    - aus PDF gerenderte Bildseite.
- **Ziel**:
  - Nutzer:innen können **Fotos und PDFs** hochladen,
  - nachgelagerte Prozesse bekommen **robust verarbeitbare PDF-Belege**,
  - und die OCR-Pipeline bleibt maximal effizient.

