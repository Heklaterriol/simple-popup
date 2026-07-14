<?php

/**
 * @package     Simple Popup Module
 * @subpackage  mod_simplepopup
 *
 * @copyright   Copyright (C) 2026 Hekla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Hekla\Module\Popup\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;

/**
 * Dispatcher class for mod_simplepopup.
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

        // Unique ID per module instance so that multiple popups on the same
        // page (DOM IDs, sessionStorage keys) do not interfere with each other.
        $data['uid'] = 'hkpopup-' . (int) $this->module->id;

        return $data;
    }
}
