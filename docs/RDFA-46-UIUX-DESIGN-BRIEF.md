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

## 5) Testbare UX/A11y-Akzeptanzkriterien (verbindlich)

Hinweis: Jede Anforderung ist mit einer eindeutigen ID versehen und muss als PASS/FAIL pruefbar sein.

### 5.1 Viewport und Responsive

- `UX-RESP-01`: Auf 360x800, 768x1024, 1280x800 gibt es keine horizontale Scrollbar (`document.documentElement.scrollWidth <= innerWidth`).
- `UX-RESP-02`: Hauptnavigation ist auf Desktop direkt sichtbar und auf Mobile per `#nav-toggle` oeffn-/schliessbar.
- `UX-RESP-03`: Kontaktformular inkl. Submit-Button ist auf allen drei Viewports ohne Layoutbruch erreichbar.

### 5.2 Formular und Interaktion

- `UX-FORM-01`: Alle interaktiven Elemente im Formular haben mind. 44px Hoehe; Touch-Targets auf Mobile mind. 44x44px.
- `UX-FORM-02`: Jedes Pflichtfeld ist sichtbar markiert und programmatisch als `required` erkennbar.
- `UX-FORM-03`: Bei invalidem Submit erscheint pro fehlerhaftem Feld ein feldnaher, konkreter Fehlertext.
- `UX-FORM-04`: Nach invalidem Submit springt der Fokus zur Fehlerzusammenfassung oder zum ersten invaliden Feld.
- `UX-FORM-05`: Bereits korrekt eingegebene Werte bleiben nach invalidem Submit erhalten.
- `UX-FORM-06`: Erfolgreicher Submit zeigt eine eindeutige Erfolgsmeldung mit naechstem Schritt.

### 5.3 Tastatur und Fokus

- `A11Y-KB-01`: Alle interaktiven Elemente sind per Tastatur erreichbar; kein Keyboard-Trap.
- `A11Y-KB-02`: Skip-Link ist erster Fokuspunkt und setzt Fokus in den Hauptinhalt.
- `A11Y-KB-03`: Mobile-Menue ist per Tastatur oeffn-/schliessbar; Fokus bleibt im erwarteten Interaktionsfluss.
- `A11Y-FOCUS-01`: Sichtbarer Fokus auf Links, Buttons, Inputs, Selects, Textareas.
- `A11Y-FOCUS-02`: Fokusindikator ist gegen angrenzende Farbe mit mindestens 3:1 kontrastiert und nicht verdeckt.

### 5.4 Kontrast, Semantik, Screenreader

- `A11Y-CONTRAST-01`: Normaler Text hat mindestens 4.5:1 Kontrast.
- `A11Y-CONTRAST-02`: Grosser Text (>=24px regular oder >=18.66px bold) hat mindestens 3:1 Kontrast.
- `A11Y-CONTRAST-03`: UI-Komponenten-Grenzen und Fokusindikatoren haben mindestens 3:1 Kontrast.
- `A11Y-SEM-01`: Jedes Formularfeld hat sichtbares Label mit `label[for]` + eindeutiger Feld-`id`.
- `A11Y-SEM-02`: Fehler-/Hilfetexte sind mit Feldern verknuepft (`aria-describedby`), Invalid-Status via `aria-invalid`.
- `A11Y-SEM-03`: Pro Seite existiert genau eine `h1`; Landmarken `header/main/nav/footer` sind vorhanden.
- `A11Y-SEM-04`: Erfolgs- und Fehlermeldungen werden assistiven Technologien angekuendigt (z. B. `aria-live`).

## 6) Mapping zu QA-Checkliste (objektive Validierung)

| Brief-ID | QA-Check (docs/qa-checklist.md) | Nachweis |
|---|---|---|
| UX-RESP-01 | 6.1 Layout-Stabilitaet + 2.B Responsive Smoke | Responsive Screenshots + manueller Viewport-Check |
| UX-RESP-02 | 6.1 Layout-Stabilitaet + 2.B Responsive Smoke | Nav-Interaktion auf 360/768/1280 |
| UX-RESP-03 | 6.2 Formular-Usability + 2.B Responsive Smoke | Kontaktbereich-Screenshots + manuelle Bedienung |
| UX-FORM-01 | 6.2 Formular-Usability | DevTools-Messung/visuelle Kontrolle je Control |
| UX-FORM-02 | 6.2 Formular-Usability + 6.3 Labels/Semantik | DOM-Check `required` + sichtbare Pflichtmarkierung |
| UX-FORM-03 | 6.2 Inline-Validierung | Invalid-Submit-Screenshot + Fehlertexte |
| UX-FORM-04 | 6.2 Fehlerverhalten nach Submit + 6.3 Tastatur | Keyboard-E2E / Fokusnachweis |
| UX-FORM-05 | 6.2 Fehlerverhalten nach Submit | Re-Submit mit teilweise gueltigen Daten |
| UX-FORM-06 | 6.2 Erfolgsverhalten + 6.3 Struktur | Erfolgsfall-Screenshot + `aria-live`/Statusnachweis |
| A11Y-KB-01 | 6.3 Tastatur + 2.C Accessibility Smoke | Tab-Durchlauf-Protokoll |
| A11Y-KB-02 | 6.3 Tastatur + 2.C Accessibility Smoke | Skip-Link-Screenshot + Fokusziel |
| A11Y-KB-03 | 6.3 Tastatur + 2.B Responsive Smoke | Keyboard-Test auf Mobile-Nav |
| A11Y-FOCUS-01 | 6.3 Fokus + 2.C Accessibility Smoke | Fokus-Screenshots interaktiver Elemente |
| A11Y-FOCUS-02 | 6.3 Fokus + 6.3 Kontrast | Kontrastmessung Fokusring |
| A11Y-CONTRAST-01 | 6.3 Kontrast | Kontrasttool-Messwerte Text |
| A11Y-CONTRAST-02 | 6.3 Kontrast | Kontrasttool-Messwerte Headings |
| A11Y-CONTRAST-03 | 6.3 Kontrast | Kontrasttool-Messwerte Controls/Fokus |
| A11Y-SEM-01 | 6.3 Labels/Semantik + 2.C Accessibility Smoke | DOM-Inspektion `for/id` |
| A11Y-SEM-02 | 6.3 Labels/Semantik | DOM-Inspektion `aria-describedby/aria-invalid` |
| A11Y-SEM-03 | 6.3 Struktur + 2.C Accessibility Smoke | Outline/Landmark-Check |
| A11Y-SEM-04 | 6.3 Struktur | Screenreader-/DOM-Nachweis `aria-live` |

## 7) Delivery-Definition fuer Implementierung

Die Implementierung gilt fuer UI/UX als bereit zur QA, wenn:

1. Visuelle Tokens fuer Typografie, Farbe, Radius, Spacing und Fokus im CSS zentral definiert sind.
2. Desktop- und Mobile-Anforderungen aus Abschnitt 3 reproduzierbar erfuellt sind.
3. Formular-Usability-Anforderungen aus Abschnitt 4 umgesetzt sind.
4. Alle Akzeptanzkriterien aus Abschnitt 5 mit PASS/FAIL ueber das Mapping in Abschnitt 6 validierbar sind.
