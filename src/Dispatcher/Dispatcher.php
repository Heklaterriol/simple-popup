<?php

/**
 * @package     Hekla.Module.Popup
 * @subpackage  mod_popup
 *
 * @copyright   Copyright (C) 2026 Hekla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Hekla\Module\Popup\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;

/**
 * Dispatcher class for mod_popup.
 *
 * @since  1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   1.0.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        // Eindeutige ID pro Modul-Instanz, damit mehrere Popups auf derselben
        // Seite (DOM-IDs, sessionStorage-Schlüssel) sich nicht gegenseitig stören.
        $data['uid'] = 'hkpopup-' . (int) $this->module->id;

        return $data;
    }
}
