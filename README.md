# Simple Popup for Joomla (mod_simplepopup)

Configurable popup module for Joomla 5/6. Shows a popup with freely
editable HTML content after an adjustable delay - as a centred modal, a
bar at the top/bottom edge, or a panel anchored to one of the four
corners/sides, with a dimmed page background.

## Requirements

- Joomla 5 or 6 (namespaced module architecture)
- PHP 8.1 or higher

## Installation

Like any Joomla extension: **System → Install → Extensions** → upload
the zip. Uploading a newer version again installs automatically as an
update (`method="upgrade"` in the manifest).

Then, under **Content → Site Modules**, create a new module of type
"Popup", set the content and options, set position/assignment, publish.

## Tabs in the module editor

### Content

| Field | Description |
|---|---|
| Content | HTML editor (WYSIWYG) for the popup content |
| Popup link (optional) | Makes the whole popup a link. The close button stays separately clickable; other links/buttons *inside* the editor content become unreachable though, since the click area covers everything |
| Open link in a new tab | Only shown once a link is set |

### Options

**Behaviour**
| Field | Description |
|---|---|
| Delay (milliseconds) | Time before the popup appears after the page has loaded |
| Display | "Once per session" (remembers closing via `sessionStorage`), "Repeated" (appears again on every page load), or "Don't show again for X hours" (remembers closing via a cookie with the given expiry) |
| Vertical / horizontal position | Chosen separately (top/middle/bottom × left/centre/right) → 9 possible anchor points, the entry direction adapts automatically |
| Background dimming (%) | 0 = no dimmed background |

**Size**
| Field | Description |
|---|---|
| Width / height | each: automatic (fits content) / fixed in pixels / relative in % / full width or height (edge-to-edge) |

**Appearance**
| Field | Description |
|---|---|
| Background colour | Colour of the popup box |
| Text colour | Colour of the text inside the popup |
| Link colour | Colour of links inside the popup content; leave blank for the browser/editor default |
| Border width / colour | 0 px = no border |
| Shadow | none / soft / medium / strong (drop shadow cast outward) |

## Technical structure

```
mod_simplepopup/
├── mod_simplepopup.xml            Manifest, module settings (<config>)
├── services/provider.php    DI registration (dispatcher factory)
├── src/
│   └── Dispatcher/Dispatcher.php   provides, among other things, a per-instance unique "uid"
├── tmpl/default.php         Rendering: computes inline styles from the parameters
├── media/
│   ├── css/popup.css
│   └── js/popup.js
└── language/{en-GB,de-DE}/  Language files
```

A few decisions that shaped the build:

- **No jQuery.** `popup.js` is plain vanilla JS. jQuery is no longer a
  core part of Joomla 5/6 and could be dropped entirely in a future
  release.
- **CSS/JS registered directly in PHP**, not via `joomla.asset.json`.
  Joomla does not automatically load a *module's* `joomla.asset.json`
  (unlike components/templates); `tmpl/default.php` registers the
  assets itself via `registerStyle()`/`registerScript()`, guarded by
  `assetExists()` so multiple instances of the module on the same page
  don't collide.
- **Unique ID per instance** (`hkpopup-<module-id>`): the DOM id and the
  `sessionStorage`/cookie key are both tied to the module id, so
  multiple popups on one page work independently of each other.
- **Full width/height without margin**: `popup.css` limits the box to
  `max-width: 95vw` / `max-height: 95vh` by default, as a safety net
  against overflow. With "full width"/"full height" this is overridden
  per instance to `100%`, otherwise a margin of roughly 2.5% per side
  would remain.

## Known limitations

- If a popup-wide link is set, other links/buttons inside the editor
  content become unreachable (see above).
- There is no automatic contrast adjustment between the text/link
  colour and the chosen background colour - if you pick a dark
  background, remember to also set a lighter text/link colour.

## Version history

- **1.1.0** – Added text colour and link colour (standard Joomla colour
  picker); added a "Don't show again for X hours" display mode using a
  cookie with an expiry.
- **1.0.0** – First public release: content editor, optional
  popup-wide link, delay in milliseconds, once-per-session/repeated
  display, vertical/horizontal position (9 anchor points), background
  dimming, size (width/height: automatic/pixels/%/full), background
  colour, border, shadow.
