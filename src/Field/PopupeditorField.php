<?php

/**
 * @package     Simple Popup Module
 * @copyright   Copyright (C) 2026 Hekla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Hekla\Module\Popup\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\EditorField;

/**
 * Editor-Feld, das im Modul-Bearbeiten-Formular die volle verfügbare Breite
 * einnimmt, statt durch eine für das Label reservierte Spalte eingeengt zu
 * werden.
 *
 * Das Ausblenden des Labels selbst übernimmt das Joomla-Bordmittel
 * hiddenLabel="true" (barrierefrei per CSS-Klasse "sr-only", Label bleibt für
 * Screenreader vorhanden). Was dabei fehlt: die Eingabespalte (".controls")
 * behält trotzdem ihre ursprüngliche, schmalere Grid-Breite bei, da Bootstrap
 * die Spaltenbreite nicht automatisch nachzieht, nur weil das Label
 * unsichtbar ist. Da Joomla für das Modul-Bearbeiten-Formular (com_modules,
 * Administrator) kein eigenes CSS/JS unseres Moduls lädt – unser
 * Web-Asset-System in tmpl/default.php greift nur beim Rendern auf der
 * Website –, hängt dieses Feld den nötigen, auf die eigene Feld-ID
 * beschränkten <style>-Fix direkt an sein eigenes Eingabe-HTML an.
 *
 * @since  1.2.0
 */
class PopupeditorField extends EditorField
{
    /**
     * @var    string
     * @since  1.2.0
     */
    protected $type = 'Popupeditor';

    /**
     * @return  string
     *
     * @since   1.2.0
     */
    protected function getInput()
    {
        $input = parent::getInput();
        $id    = $this->id;

        $style = '<style>'
            . '.control-group:has(#' . $id . ')>.controls{width:100%;max-width:100%;flex:0 0 100%;}'
            . '</style>';

        return $input . $style;
    }
}
