<?php
/**
 * @package    Redform.Admin
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Check access.
if (!JFactory::getUser()->authorise('core.manage', 'com_redform'))
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

	return false;
}

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDITEM_REDCORE_INIT_FAILED'), 404);
}

// Bootstraps redCORE
RBootstrap::bootstrap();

// log helper class
require_once (JPATH_COMPONENT_ADMINISTRATOR .'/helpers/helper.php');

require_once (JPATH_COMPONENT_SITE .'/redform.defines.php');

// Redmember integration
if (file_exists(JPATH_ROOT . '/components/com_redmember'))
{
	define('REDMEMBER_INTEGRATION', true);
}
else
{
	define('REDMEMBER_INTEGRATION', false);
}

// Register backend prefix
RLoader::registerPrefix('Redform', __DIR__);

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

// Make available the fields
JFormHelper::addFieldPath(JPATH_LIBRARIES . '/redform/form/fields');

// Make available the form rules
JFormHelper::addRulePath(JPATH_LIBRARIES . '/redform/form/rules');

// Add the include path for html
JHtml::addIncludePath(JPATH_LIBRARIES . '/redform/html');

$app = JFactory::getApplication();

// Instantiate and execute the front controller.
$controller = JControllerLegacy::getInstance('Redform');
$controller->execute($app->input->get('task'));
$controller->redirect();
