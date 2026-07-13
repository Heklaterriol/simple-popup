# mod_popup

Konfigurierbares Popup-Modul für Joomla 5/6. Zeigt nach einer einstellbaren
Verzögerung ein Popup mit frei editierbarem HTML-Inhalt an – wahlweise als
zentriertes Modal, als Leiste am oberen/unteren Rand oder als Panel an einer
der vier Ecken/Seiten, mit abgedunkeltem Seitenhintergrund.

## Voraussetzungen

- Joomla 5 oder 6 (namespaced Modul-Architektur)
- PHP 8.1 oder höher

## Installation

Wie jede Joomla-Erweiterung: **System → Installieren → Erweiterungen** →
`mod_popup.zip` hochladen. Ein erneutes Hochladen einer neueren Version
installiert automatisch als Update (`method="upgrade"` im Manifest).

Danach unter **Inhalte → Website-Module** ein neues Modul vom Typ „Popup"
anlegen, Inhalt und Optionen einstellen, Position/Zuweisung setzen,
veröffentlichen.

## Tabs im Modul-Editor

### Inhalt

| Feld | Beschreibung |
|---|---|
| Inhalt | HTML-Editor (WYSIWYG) für den Popup-Inhalt |
| Link über das gesamte Popup (optional) | Macht das ganze Popup zu einem Link. Der Schließen-Button bleibt separat klickbar; andere Links/Buttons *innerhalb* des Editor-Inhalts werden dadurch allerdings unerreichbar, da die Klickfläche alles überdeckt |
| Link in neuem Tab öffnen | Nur sichtbar, wenn ein Link gesetzt ist |

### Options

**Verhalten**
| Feld | Beschreibung |
|---|---|
| Verzögerung (Millisekunden) | Zeit bis das Popup nach dem Laden der Seite erscheint |
| Anzeige | „Einmalig pro Sitzung" (merkt sich das Schließen per `sessionStorage`) oder „Wiederholt" (erscheint bei jedem Seitenaufruf erneut) |
| Position vertikal / horizontal | Getrennt wählbar (oben/mitte/unten × links/mitte/rechts) → 9 mögliche Ankerpunkte, Einfahr-Richtung passt sich automatisch an |
| Hintergrundabdunkelung (%) | 0 = kein abgedunkelter Hintergrund |

**Größe**
| Feld | Beschreibung |
|---|---|
| Breite / Höhe | je automatisch (passt sich dem Inhalt an) / fest in Pixel / relativ in % / volle Breite bzw. Höhe (randlos) |

**Erscheinungsbild**
| Feld | Beschreibung |
|---|---|
| Hintergrundfarbe | Farbe der Popup-Box |
| Randstärke / Randfarbe | 0 px = kein Rand |
| Schatten | kein / leicht / mittel / stark (Schlagschatten nach außen) |

## Technischer Aufbau

```
mod_popup/
├── mod_popup.xml            Manifest, Moduleinstellungen (<config>)
├── services/provider.php    DI-Registrierung (Dispatcher-Factory)
├── src/
│   ├── Dispatcher/Dispatcher.php   liefert u. a. eine pro Instanz eindeutige "uid"
│   └── Field/PopupeditorField.php  custom Feldtyp, siehe unten
├── tmpl/default.php         Rendering: berechnet Styles aus den Parametern
├── media/
│   ├── css/popup.css
│   └── js/popup.js
└── language/{en-GB,de-DE}/  Sprachdateien
```

Ein paar Entscheidungen, die beim Bau eine Rolle gespielt haben:

- **Kein jQuery.** `popup.js` ist reines Vanilla-JS. jQuery ist seit Joomla 5/6
  kein Kernbestandteil mehr und könnte in künftigen Joomla-Versionen ganz
  entfallen.
- **CSS/JS-Registrierung direkt per PHP**, nicht über `joomla.asset.json`.
  Joomla lädt die `joomla.asset.json` eines *Moduls* (anders als bei
  Komponenten/Templates) nicht automatisch ein; `tmpl/default.php` registriert
  die Assets deshalb selbst über `registerStyle()`/`registerScript()`, mit
  `assetExists()`-Prüfung, damit mehrere Modul-Instanzen auf derselben Seite
  sich nicht in die Quere kommen.
- **Eindeutige ID pro Instanz** (`hkpopup-<module-id>`): DOM-ID und
  `sessionStorage`-Schlüssel hängen an der Modul-ID, damit mehrere Popups auf
  einer Seite unabhängig voneinander funktionieren.
- **`PopupeditorField`** (eigener Feldtyp, erbt von Joomlas `EditorField`):
  Das Modul-Bearbeiten-Formular läuft komplett im Administrator; unser
  Web-Asset-System aus `tmpl/default.php` greift dort nicht. Damit das
  Editor-Feld trotzdem die volle Formularbreite nutzt statt durch eine für das
  Label reservierte Spalte eingeengt zu werden, hängt dieser Feldtyp einen
  kleinen, auf die eigene Feld-ID beschränkten `<style>`-Block an sein
  Eingabe-HTML an. Das Label selbst wird über das Joomla-Bordmittel
  `hiddenLabel="true"` barrierefrei ausgeblendet (bleibt für Screenreader
  vorhanden).
- **Fullwidth/Fullheight ohne Rand**: `popup.css` begrenzt die Box standardmäßig
  auf `max-width: 95vw` / `max-height: 95vh` als Sicherheitsnetz gegen
  Überlaufen. Bei „volle Breite"/„volle Höhe" wird das pro Instanz per
  Inline-Style auf `100%` überschrieben, sonst bliebe ein Rand von ca. 2,5 %
  pro Seite stehen.

## Bekannte Einschränkungen

- „Einmalig" bezieht sich auf die Browser-Sitzung (`sessionStorage`), nicht auf
  einen längeren Zeitraum. Für „X Tage nicht erneut anzeigen" bräuchte es
  stattdessen ein Cookie mit Ablaufdatum – bislang nicht umgesetzt.
- Ist ein Link übers ganze Popup gesetzt, werden andere Links/Buttons
  innerhalb des Editor-Inhalts unerreichbar (siehe oben).
- Es gibt keine automatische Kontrastanpassung der Schrift an die gewählte
  Hintergrundfarbe.

## Versionshistorie

- **1.2.2** – README.md ergänzt.
- **1.2.1** – `hiddenLabel="true"` statt eigenem Stacking-Layout für das
  Editor-Feld.
- **1.2.0** – Verzeichnisstruktur korrigiert (`media/css`, `media/js` ohne
  doppelten Modulordner); Verhalten/Größe/Erscheinungsbild zu einem
  gemeinsamen Options-Tab zusammengeführt; echtes randloses
  Fullwidth/Fullheight; custom Editor-Feldtyp für volle Feldbreite.
- **1.1.1** – Fix: `WebAssetException`, da Joomla die `joomla.asset.json`
  eines Moduls nicht automatisch lädt; Assets werden seither direkt per PHP
  registriert.
- **1.1.0** – Größe (Breite/Höhe: automatisch/Pixel/%/voll), vertikale und
  horizontale Position getrennt wählbar, Verzögerung in Millisekunden,
  einmalige/wiederholte Anzeige.
- **1.0.0** – Erste Version: Inhalt, Position (oben/unten/links/rechts/mittig),
  Verzögerung in Sekunden, Hintergrundabdunkelung, Hintergrundfarbe, Rand,
  Schatten, optionaler Link übers ganze Popup.
