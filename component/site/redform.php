<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Frontend file
 */

/**
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDITEM_REDCORE_INIT_FAILED'), 404);
}

// Bootstraps redCORE
RBootstrap::bootstrap();

$jinput = JFactory::getApplication()->input;

// Register library prefix
JLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

// Require the base controller
require_once (JPATH_COMPONENT . '/controller.php');
require_once (JPATH_COMPONENT . '/redform.defines.php');

// Execute the controller
$controller = JControllerLegacy::getInstance('redform');
$controller->execute($jinput->get('task'));
$controller->redirect();
