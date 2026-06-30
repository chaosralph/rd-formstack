# Dokumentenmanagementsystem (DMS) – Vorschläge

## Ziel
Ein Dokumentenmanagementsystem, das in die bestehende Seite **rd.timepro-solutions.de** (PHP/MySQL) integriert wird. Kernfunktionen:

1. **Foto-Upload** → automatische Konvertierung zu PDF
2. **PDF-Speicherung** mit Vorschau und Verwaltung
3. **Export** einzelner oder aller PDFs (Einzel-Download / ZIP-Archiv)

---

## Vorschlag 1: Serverseitige PHP-Lösung (ImageMagick/FPDF)

### Beschreibung
Reiner PHP-Stack: Fotos werden per HTML-Formular oder Kamera-API hochgeladen, serverseitig mit **ImageMagick** oder **GD** verarbeitet und mittels **FPDF/TCPDF** in PDFs konvertiert. Die Dateien werden auf dem Server-Dateisystem gespeichert, Metadaten in MySQL.

### Architektur
```
Browser (Kamera/Datei) → PHP-Upload-Endpoint → ImageMagick/GD (Bildoptimierung)
                                              → FPDF/TCPDF (PDF-Erzeugung)
                                              → Dateisystem (PDF-Speicher)
                                              → MySQL (Metadaten)
```

### Vorteile
| # | Vorteil |
|---|---------|
| 1 | **Nahtlose Integration** – passt direkt in den bestehenden PHP/MySQL-Stack |
| 2 | **Keine zusätzlichen Systeme** – kein Node.js, kein externes API nötig |
| 3 | **Geringe Abhängigkeiten** – FPDF/TCPDF sind reine PHP-Bibliotheken |
| 4 | **Bewährt und stabil** – TCPDF/FPDF sind seit Jahren im Einsatz, gute Dokumentation |
| 5 | **Volle Kontrolle** – Daten bleiben komplett auf dem eigenen Server |
| 6 | **Login-System** bereits vorhanden und direkt nutzbar |

### Nachteile
| # | Nachteil |
|---|---------|
| 1 | **Kein clientseitiger Bildeditor** – Zuschneiden/Drehen muss separat gebaut werden |
| 2 | **ImageMagick muss auf dem Server installiert sein** (oder GD als Fallback) |
| 3 | **Skalierung bei großen Dateien** – Speicherplatz und RAM müssen überwacht werden |
| 4 | **Einfaches UI** – ohne JavaScript-Framework weniger moderne UX |

### Geschätzter Aufwand
- Backend: PHP-Upload, Konvertierung, Datenbank-Schema, Export-Logik
- Frontend: Upload-Formular mit Kamera-Zugriff, Dokumenten-Galerie, Download-Buttons
- Moderate Komplexität, wenige Abhängigkeiten

---

## Vorschlag 2: PHP-Backend + JavaScript-Frontend (Kamera + Cropping)

### Beschreibung
PHP-Backend wie in Vorschlag 1, aber mit einem modernen **JavaScript-Frontend** (z.B. Vanilla JS mit Cropper.js oder ein leichtgewichtiges Framework). Fotos können direkt über die Kamera-API aufgenommen, im Browser zugeschnitten/gedreht und dann hochgeladen werden. PDF-Konvertierung erfolgt serverseitig.

### Architektur
```
Browser (Kamera-API + Cropper.js) → AJAX/Fetch Upload → PHP-Backend
                                                        → ImageMagick (Optimierung)
                                                        → TCPDF (PDF-Erzeugung)
                                                        → Dateisystem + MySQL
```

### Vorteile
| # | Vorteil |
|---|---------|
| 1 | **Moderne UX** – Kamera-Vorschau, Zuschnitt, Drehung direkt im Browser |
| 2 | **Gute Integration** – Backend bleibt PHP/MySQL, kein Systemwechsel |
| 3 | **Mobile-optimiert** – Kamera-API funktioniert gut auf Smartphones |
| 4 | **Schnelleres Feedback** – Bildbearbeitung im Browser, weniger Server-Last |
| 5 | **Drag & Drop** möglich für Desktop-User |
| 6 | **Schrittweise erweiterbar** – kann später um OCR etc. ergänzt werden |

### Nachteile
| # | Nachteil |
|---|---------|
| 1 | **Mehr Frontend-Code** – Cropper.js, Kamera-Handling, AJAX-Logik |
| 2 | **Browser-Kompatibilität** – Kamera-API erfordert HTTPS und aktuelle Browser |
| 3 | **Zwei Technologien** – PHP + JavaScript müssen gewartet werden |
| 4 | **ImageMagick/TCPDF** weiterhin serverseitig nötig |

### Geschätzter Aufwand
- Backend: wie Vorschlag 1
- Frontend: Kamera-Integration, Cropper.js, AJAX-Upload, Dokumenten-Galerie
- Mittlere bis höhere Komplexität, aber bessere Benutzererfahrung

---

## Vorschlag 3: Client-seitige PDF-Erzeugung (jsPDF + html2canvas)

### Beschreibung
Die PDF-Erzeugung findet **vollständig im Browser** statt (via **jsPDF**). Fotos werden aufgenommen/hochgeladen, im Browser in ein PDF konvertiert und dann als fertige PDF an den PHP-Server gesendet. Spart Serverressourcen.

### Architektur
```
Browser (Kamera-API) → jsPDF (PDF im Browser erzeugt) → Upload der fertigen PDF
                                                        → PHP-Backend (Speichern)
                                                        → Dateisystem + MySQL
```

### Vorteile
| # | Vorteil |
|---|---------|
| 1 | **Keine Server-Bibliotheken nötig** – kein ImageMagick, kein TCPDF |
| 2 | **Geringere Server-Last** – PDF wird clientseitig erzeugt |
| 3 | **Einfacheres Backend** – empfängt nur fertige PDFs |
| 4 | **Sofortige Vorschau** – User sieht die PDF vor dem Upload |
| 5 | **Gute Integration** – Backend ist simples PHP/MySQL für Speicherung |

### Nachteile
| # | Nachteil |
|---|---------|
| 1 | **Qualität** – jsPDF-Bildqualität ist teils schlechter als serverseitige Lösung |
| 2 | **Browser-Abhängigkeit** – funktioniert nicht bei deaktiviertem JavaScript |
| 3 | **Große Dateien** – bei hochauflösenden Fotos kann der Browser langsam werden |
| 4 | **Weniger Kontrolle** – serverseitige Nachbearbeitung eingeschränkt |
| 5 | **Sicherheit** – Client kann manipulierte PDFs senden |

### Geschätzter Aufwand
- Backend: einfacher File-Upload, MySQL-Metadaten, Export-Logik
- Frontend: Kamera, jsPDF-Integration, Upload-Logik
- Geringere Gesamtkomplexität, aber eingeschränkte Qualitätskontrolle

---

## Vorschlag 4: PHP + externes DMS-Tool (z.B. Gotenberg als PDF-Service)

### Beschreibung
Verwendung eines spezialisierten **PDF-Microservice** wie **Gotenberg** (Docker-basiert), der über eine REST-API Bilder in hochwertige PDFs konvertiert. PHP fungiert als Vermittler zwischen Frontend und dem PDF-Service.

### Architektur
```
Browser → PHP-Upload → Gotenberg (Docker-Container, REST-API)
                      → Hochwertige PDF zurück → Dateisystem + MySQL
```

### Vorteile
| # | Vorteil |
|---|---------|
| 1 | **Professionelle PDF-Qualität** – Gotenberg nutzt Chromium/LibreOffice unter der Haube |
| 2 | **Sauber getrennt** – PDF-Erzeugung als eigenständiger Service |
| 3 | **Skalierbar** – PDF-Service kann unabhängig skaliert werden |
| 4 | **Vielseitig** – unterstützt HTML→PDF, Bild→PDF, Office→PDF und mehr |
| 5 | **Zukunftssicher** – leicht um OCR, Wasserzeichen etc. erweiterbar |

### Nachteile
| # | Nachteil |
|---|---------|
| 1 | **Docker erforderlich** – Server muss Docker unterstützen |
| 2 | **Zusätzliche Infrastruktur** – ein weiterer Service, der gewartet werden muss |
| 3 | **Ressourcenverbrauch** – Gotenberg-Container braucht RAM (Chromium) |
| 4 | **Komplexität** – mehr bewegliche Teile, Fehlerquellen |
| 5 | **Overkill** – für einfache Bild→PDF-Konvertierung möglicherweise überdimensioniert |

### Geschätzter Aufwand
- Infrastruktur: Docker-Setup, Gotenberg-Konfiguration
- Backend: PHP-Integration mit Gotenberg-API, Dateispeicherung
- Frontend: Upload-Formular
- Höhere Komplexität durch zusätzliche Infrastruktur

---

## Vergleichsmatrix

| Kriterium | Vorschlag 1 (PHP pur) | Vorschlag 2 (PHP+JS) | Vorschlag 3 (Client-PDF) | Vorschlag 4 (Gotenberg) |
|---|---|---|---|---|
| **Integration in bestehende Seite** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| **Benutzerfreundlichkeit (UX)** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| **PDF-Qualität** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Aufwand / Komplexität** | Gering | Mittel | Gering–Mittel | Hoch |
| **Serveranforderungen** | Mittel | Mittel | Gering | Hoch |
| **Mobile-Tauglichkeit** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| **Erweiterbarkeit** | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Zusätzliche Infrastruktur** | Keine | Keine | Keine | Docker |

---

## Empfehlung

Für die Integration in **rd.timepro-solutions.de** empfehle ich **Vorschlag 2 (PHP-Backend + JavaScript-Frontend)** als besten Kompromiss:

- ✅ Passt nahtlos in den bestehenden PHP/MySQL-Stack
- ✅ Moderne, mobile-optimierte Benutzererfahrung (Kamera-API, Cropping)
- ✅ Gute PDF-Qualität durch serverseitige Konvertierung
- ✅ Keine zusätzliche Infrastruktur nötig
- ✅ Schrittweise erweiterbar (OCR, Kategorisierung, etc.)

---

## Offene Fragen / Benötigte Infos

Damit ich mit der Umsetzung starten kann, wären folgende Infos hilfreich:

1. **Welchen Vorschlag bevorzugen Sie?** (oder eine Kombination?)
2. **Serverumgebung**: PHP-Version? Ist ImageMagick/GD installiert? Docker verfügbar?
3. **Benutzerstruktur**: Sollen verschiedene User eigene Dokumente haben? (Multi-Tenancy)
4. **Speicherlimits**: Maximale Dateigröße pro Upload? Gesamtspeicher?
5. **Kategorisierung**: Sollen Dokumente in Ordnern/Kategorien organisiert werden?
6. **OCR**: Soll Texterkennung auf den gescannten Dokumenten laufen?
7. **Berechtigungen**: Wer darf Dokumente sehen/bearbeiten/löschen?
8. **Bestehender Code**: Gibt es Zugriff auf den aktuellen Quellcode von rd.timepro-solutions.de?
9. **Design**: Soll das DMS dem bestehenden Design der Seite folgen?
10. **Mehrere Seiten pro PDF**: Sollen mehrere Fotos zu einem mehrseitigen PDF zusammengefasst werden können?
