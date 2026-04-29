# RDFA-49 UI/UX Delta Review (gegen RDFA-46/48)

Stand: 2026-04-29 (UTC)  
Rolle: UI/UX Design  
Scope: Delta-Review der neuen Security-/Validation-Flows ohne Redesign

Geprüfte/angepasste Dateien (Ownership):
- `public/assets/js/site.js`
- `public/assets/css/site.css` (Review, keine Änderung nötig)
- `templates/pages/home.php` (Review, keine Änderung nötig)
- Referenz für Formular-Markup (read-only): `templates/pages/contact-section.php`

## Delta-Fokus

Geprüft wurden nur Kriterien, die durch RDFA-46/48 Security-/Validation-Flows berührt sind:
- Pflichtfeld-/Validierungsverhalten
- Fehlerdarstellung und Fokus nach invalidem Submit
- Live-Regionen für AT-Ankündigungen
- Semantische Verknüpfung (`aria-invalid`, `aria-describedby`)

## Ergebnis PASS/FAIL (betroffene Kriterien)

| Kriterium | Status | Delta-Bewertung |
|---|---|---|
| UX-FORM-02 | PASS | Pflichtfelder bleiben visuell markiert (`*`) und programmatisch `required`. |
| UX-FORM-03 | PASS | Feldnahe Fehlertexte werden weiterhin pro invalidem Feld erzeugt (`.field-error`). |
| UX-FORM-04 | PASS | Fokus geht bei invalidem Submit konsistent auf Fehlerzusammenfassung (`#form-error-summary`); kein doppelter Fokuswechsel mehr. |
| UX-FORM-05 | PASS | Bereits eingegebene gültige Werte bleiben erhalten; Submit wird nur blockiert. |
| A11Y-SEM-02 | PASS | `aria-invalid` gesetzt; `aria-describedby` wird jetzt auch korrekt entfernt, wenn keine Referenzen mehr bestehen. |
| A11Y-SEM-04 | PASS | Fehlerzusammenfassung bleibt Live-Region (`role="alert"`, `aria-live="assertive"`), ergänzt um `aria-atomic="true"` für stabile Ankündigung. |

## Umgesetzte Minimal-Korrekturen

In `public/assets/js/site.js`:
1. `clearFieldError()` entfernt `aria-describedby` vollständig, wenn nach Fehlerbereinigung keine IDs mehr übrig sind.
2. Fehlerzusammenfassung ergänzt um `aria-atomic="true"`.
3. Bei invalidem Submit Fokusstrategie vereinfacht: Fokus nur auf die Summary, kein direktes Überschreiben durch Fokus auf erstes Feld.

## Hinweise

- Der beauftragte Dokumentpfad `docs/RDFA-49-UIUX-DELTA-REVIEW.md` existierte nicht und wurde neu angelegt.
- Finale WCAG-/UX-Freigabe für Kontrast und Viewports bleibt wie üblich ein Browser-/Screenreader-Lauf; diese Delta-Bewertung ist codebasiert auf den betroffenen Flows.
