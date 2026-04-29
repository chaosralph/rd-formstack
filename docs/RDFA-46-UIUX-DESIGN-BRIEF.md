# RDFA-46 UI/UX Design Brief - RD Formstack Solutions Rebuild

Stand: 2026-04-29 (UTC)
Rolle: UI/UX Design
Scope: Neue visuelle und UX-Spezifikation fuer Landingpage + Kontaktformular (kein 1:1 Redesign der Alt-Seite)

## 1) Design-Ziele und Leitplanken

- Keine visuelle Kopie der Alt-Seite; eigenstaendige, moderne Interpretation der Marke.
- Fokus auf Klarheit, Vertrauen, Abschlussorientierung (Kontaktaufnahme).
- Mobile-first Umsetzung mit sauberer Skalierung auf Tablet und Desktop.
- Konsistentes Verhalten aller interaktiven Komponenten (Hover, Focus, Active, Disabled).
- Barrierearme Bedienung nach WCAG 2.1 AA als Mindeststandard.

## 2) Visuelle Richtung

### 2.1 Typografie

Zielbild: technisch-serioes, klar, hoher Lesefluss.

- Primarschrift: "Plus Jakarta Sans", Fallback: `system-ui, -apple-system, Segoe UI, sans-serif`.
- Sekundaerschrift (optional fuer Akzente): "IBM Plex Sans", Fallback: `system-ui, sans-serif`.
- Basisgroessen:
  - Body: 18px / 1.6
  - Small: 16px / 1.5
  - H1: 44px / 1.15 (Desktop), 34px / 1.2 (Mobile)
  - H2: 32px / 1.2 (Desktop), 26px / 1.25 (Mobile)
  - H3: 24px / 1.3
- Lesbarkeitsregeln:
  - Maximale Zeilenlaenge Fliesstext: 70 Zeichen.
  - Keine Fliesstext-Bloecke unter 16px.
  - Ausreichender Kontrast fuer alle Textstufen.

### 2.2 Farbpalette

Zielbild: professionell, neutral, kontraststark, ohne Standard-Purple-Look.

- `--color-bg`: `#F7FAFC`
- `--color-surface`: `#FFFFFF`
- `--color-ink`: `#0F172A`
- `--color-muted`: `#475569`
- `--color-brand`: `#0B5FFF`
- `--color-brand-strong`: `#0847C2`
- `--color-accent`: `#0E9F6E`
- `--color-warning`: `#B45309`
- `--color-error`: `#B42318`
- `--color-border`: `#CBD5E1`
- `--color-focus`: `#FF7A00`

Anwendung:
- Primare CTA: `--color-brand` mit hellem Text.
- Erfolg/Vertrauen (z. B. Bestatigungen): `--color-accent`.
- Fehlerzustand: `--color-error` fuer Text, Icon, Rahmen und Hilfetext.
- Fokus immer ueber `--color-focus` klar sichtbar.

### 2.3 Komponentenstil

- Formensprache: leichte Rundungen (`8px`), klare Kantenfuehrung, keine "glassmorphism"-Effekte.
- Schatten: subtil (`0 4px 20px rgba(15, 23, 42, 0.08)`), nur fuer Layer-Trennung.
- Spacing-System: 8px-Basisraster (`8/12/16/24/32/48/64`).
- Buttons:
  - Mindesthoehe 44px.
  - Primar, Sekundaer, Ghost klar differenziert.
  - Zustandslogik fuer default/hover/focus/active/disabled verpflichtend.
- Form-Controls:
  - Mindesthoehe 44px.
  - Label immer oberhalb des Felds.
  - Hilfetext und Fehlermeldung direkt unter Feld.

### 2.4 Motion-Prinzipien

- Motion dient Orientierung, nicht Dekoration.
- Dauer:
  - Mikrointeraktionen: 120-180ms
  - Panel/Section transitions: 200-280ms
- Easing: `cubic-bezier(0.2, 0.8, 0.2, 1)`.
- Nur 2-3 Motion-Typen im Gesamtprodukt:
  - sanftes Fade+Slide fuer Inhaltsaufbau
  - Fokus/State-Transition auf Controls
  - Mobile-Navigation ein/aus
- `prefers-reduced-motion: reduce` respektieren (Animationen stark reduzieren/abschalten).

## 3) UX-Anforderungen Desktop + Mobile

### 3.1 Informationsarchitektur

- Hero kommuniziert klar:
  - Was wird angeboten?
  - Fuer wen?
  - Welche naechste Aktion?
- Primaere Handlung pro Screen klar priorisieren (Kontaktformular/CTA).
- Sekundaerinformationen visuell nachgeordnet.

### 3.2 Desktop UX (>= 1024px)

- Inhaltsbreite begrenzen (max. ca. 1200px).
- Navigationsstruktur sofort sichtbar, CTA dauerhaft gut auffindbar.
- Formbereich im oberen Scrollbereich erreichbar (kein verstecktes Kernziel).
- Geraeumige Abstaende fuer schnelles Scannen.

### 3.3 Mobile UX (< 1024px)

- Einspaltiges Layout, keine horizontale Scrollbarkeit.
- Touch-Targets mindestens 44x44px.
- Sticky/Floating CTA nur falls sie Inhalte nicht ueberdeckt.
- Mobile Navigation schnell oeffnen/schliessen, Fokusfuehrung korrekt.
- Kontaktformular ohne Zoom-Zwang ausfuellbar (Input-Text nicht zu klein).

## 4) Formular-Usability Anforderungen

- Formularfelder minimieren: nur notwendige Pflichtangaben als required markieren.
- Pflichtfelder klar als solche kenntlich machen; Legende/Erklaerung am Formularanfang.
- Validierung:
  - Inline-Feedback pro Feld
  - Fehlertext konkret und handlungsorientiert
  - Fehlerzustand visuell + textlich (nicht nur Farbe)
- Nach Submit mit Fehlern:
  - Fokus auf Fehlerzusammenfassung oder erstes fehlerhaftes Feld
  - Bereits eingegebene gueltige Daten bleiben erhalten
- Erfolg nach Submit:
  - eindeutig bestaetigen, was erfolgreich war
  - sinnvolle naechste Schritte nennen
- Tastaturfluss:
  - logische Tab-Reihenfolge
  - Enter-/Submit-Verhalten eindeutig

## 5) A11y-Akzeptanzkriterien (verbindlich)

### 5.1 Tastatur

- Alle interaktiven Elemente sind ohne Maus erreichbar.
- Kein Keyboard-Trap in Navigation, Formular oder Overlays.
- Skip-Link ist als erster Fokuspunkt erreichbar und springt in den Hauptinhalt.
- Mobile Menue kann per Tastatur geoeffnet/geschlossen werden.

### 5.2 Fokus

- Sichtbarer Fokus auf allen Links, Buttons, Inputs, Selects, Textareas.
- Fokusindikator unterscheidet sich klar vom Default-Zustand.
- Fokus darf nicht durch `overflow`, `outline: none` oder Layer verdeckt werden.

### 5.3 Kontrast

- Normaler Text: mindestens 4.5:1.
- Grosser Text (>= 24px regular oder >= 18.66px bold): mindestens 3:1.
- UI-Komponenten und Fokusindikatoren: mindestens 3:1 gegen angrenzende Farben.
- Fehler-, Warn- und Erfolgshinweise sind auch ohne Farbe verstaendlich.

### 5.4 Labels und semantische Verknuepfung

- Jedes Formularfeld besitzt ein sichtbares Label.
- Label/Feld sind semantisch verknuepft (`label[for]` + eindeutige `id`).
- Pflicht- und Fehlerstatus sind programmatisch ermittelbar (`required`, `aria-invalid`, `aria-describedby` bei Hilfs-/Fehlertext).
- Platzhalter ersetzen keine Labels.

### 5.5 Struktur und Screenreader-Basis

- Pro Seite genau eine `h1`; Ueberschriftshierarchie ohne Spruenge.
- Landmarken vorhanden: `header`, `main`, `nav`, `footer`.
- Interaktive Elemente mit korrekter Rolle und erkennbarem Namen.
- Statusmeldungen (Fehler/Erfolg) werden assistiven Technologien angekuendigt (z. B. `aria-live`).

## 6) Delivery-Definition fuer Implementierung

Die Implementierung gilt fuer UI/UX als bereit zur QA, wenn:

1. Visuelle Tokens fuer Typografie, Farbe, Radius, Spacing und Fokus im CSS zentral definiert sind.
2. Desktop- und Mobile-Anforderungen aus Abschnitt 3 reproduzierbar erfuellt sind.
3. Formular-Usability-Anforderungen aus Abschnitt 4 umgesetzt sind.
4. Alle A11y-Akzeptanzkriterien aus Abschnitt 5 testbar nachgewiesen werden koennen.
