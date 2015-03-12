<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

$redformLoader = JPATH_LIBRARIES . '/redform/bootstrap.php';

if (!file_exists($redformLoader))
{
	throw new Exception(JText::_('COM_REDFORM_LIB_INIT_FAILED'), 404);
}

include_once $redformLoader;

// Bootstraps redFORM
RdfBootstrap::bootstrap();

$jinput = JFactory::getApplication()->input;

// Require the base controller
require_once JPATH_COMPONENT . '/controller.php';
require_once JPATH_COMPONENT . '/redform.defines.php';

// Execute the controller
$controller = JControllerLegacy::getInstance('redform');
$controller->execute($jinput->get('task'));
$controller->redirect();
