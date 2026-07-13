# Simple Pop-up

A configurable popup module for Joomla 5 and 6.

Displays a popup with freely editable HTML content after a configurable delay. The popup can be shown as a centered modal, a bar at the top or bottom of the page, or as a panel attached to any corner or side. An optional dimmed page overlay is also supported.

## Requirements

* Joomla 5 or 6
* PHP 8.1 or later

## Installation

Install the module like any other Joomla extension:

**System → Install → Extensions** → upload `mod_popup.zip`.

Uploading a newer version automatically performs an upgrade (`method="upgrade"` in the module manifest).

After installation, go to **Content → Site Modules**, create a new **Popup** module, configure its content and options, assign a position and menu items, then publish it.

## Module Options

### Content

| Option                               | Description                                                                                                                                                                                 |
| ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Content                              | WYSIWYG editor for the popup content.                                                                                                                                                       |
| Link for the entire popup (optional) | Makes the whole popup clickable. The close button remains functional, but links and buttons inside the editor content become inaccessible because the overlay link covers the entire popup. |
| Open link in new tab                 | Only available when a link has been specified.                                                                                                                                              |

### Behavior

| Option                         | Description                                                                                                                                        |
| ------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| Delay (milliseconds)           | Time before the popup appears after the page has loaded.                                                                                           |
| Display mode                   | **Once per session** (remembers dismissal using `sessionStorage`) or **Every page load**.                                                          |
| Vertical / Horizontal position | Top / Center / Bottom × Left / Center / Right (9 possible anchor positions). The slide-in animation automatically adapts to the selected position. |
| Background dimming (%)         | 0 = no overlay.                                                                                                                                    |

### Size

| Option         | Description                                                                                  |
| -------------- | -------------------------------------------------------------------------------------------- |
| Width / Height | Automatic (fit content), fixed pixels, percentage, or full width / full height (borderless). |

### Appearance

| Option                      | Description                                                              |
| --------------------------- | ------------------------------------------------------------------------ |
| Background color            | Popup background color.                                                  |
| Border width / Border color | Set the border thickness and color. A width of 0 px disables the border. |
| Shadow                      | None, Light, Medium, or Strong.                                          |

## Project Structure

```text
mod_popup/
├── mod_popup.xml            Module manifest and configuration
├── services/provider.php    Dependency injection registration
├── src/
│   ├── Dispatcher/Dispatcher.php
│   └── Field/PopupeditorField.php
├── tmpl/default.php         Popup rendering
├── media/
│   ├── css/popup.css
│   └── js/popup.js
└── language/{en-GB,de-DE}/  Language files
```

## Design Notes

Some implementation details may be useful if you intend to modify or extend the module.

### No jQuery

The frontend JavaScript is written in plain JavaScript. Since Joomla 5 no longer depends on jQuery, the module avoids it completely.

### Asset Registration

Instead of using `joomla.asset.json`, CSS and JavaScript are registered directly in `tmpl/default.php`.

Unlike components and templates, Joomla does not automatically load a module's `joomla.asset.json`. Assets are therefore registered manually using `registerStyle()` and `registerScript()`, with `assetExists()` checks to avoid duplicate registrations when multiple popup modules are published on the same page.

### Unique Instance IDs

Each module instance receives its own unique ID (`hkpopup-<module-id>`).

This ID is used for both the DOM element and the corresponding `sessionStorage` key, allowing multiple popup instances to work independently.

### Custom Editor Field

`PopupeditorField` extends Joomla's `EditorField`.

Because the module edit form runs entirely in the administrator backend, the frontend asset system is unavailable there. The custom field therefore injects a small CSS block to allow the editor to span the full available width instead of being constrained by Joomla's label column.

The label itself is hidden accessibly using Joomla's built-in `hiddenLabel="true"` attribute, so it remains available to screen readers.

### True Full Width / Full Height

By default, `popup.css` limits the popup to `95vw` and `95vh` to prevent accidental overflow.

When **Full Width** or **Full Height** is selected, those limits are overridden per instance with inline styles (`100%`) so the popup can truly fill the available space.

## Known Limitations

* **Once per session** uses `sessionStorage`. A "Don't show again for X days" option would require cookies with an expiration date and is currently not implemented.
* If the entire popup is configured as a link, any links or buttons inside the popup content become inaccessible.
* Text color is not automatically adjusted to match the selected background color.

## Version History

### 1.2.2

* Added this README.

### 1.2.1

* Replaced the custom editor layout with Joomla's built-in `hiddenLabel="true"` support.

### 1.2.0

* Corrected the media directory structure.
* Merged **Behavior**, **Size**, and **Appearance** into a single **Options** tab.
* Added true borderless full-width/full-height mode.
* Added a custom editor field for full-width editing.

### 1.1.1

* Fixed `WebAssetException` caused by Joomla not automatically loading `joomla.asset.json` for modules.
* Assets are now registered directly in PHP.

### 1.1.0

* Added configurable width and height.
* Added independent vertical and horizontal positioning.
* Changed the delay setting to milliseconds.
* Added once-per-session and repeated display modes.

### 1.0.0

Initial release featuring configurable content, positioning, delay, background overlay, background color, border, shadow, and an optional link covering the entire popup.
