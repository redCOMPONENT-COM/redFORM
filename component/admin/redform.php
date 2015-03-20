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

$redformLoader = JPATH_LIBRARIES . '/redform/bootstrap.php';

if (!file_exists($redformLoader))
{
	throw new Exception(JText::_('COM_REDFORM_LIB_INIT_FAILED'), 404);
}

include_once $redformLoader;

// Bootstraps redFORM
RdfBootstrap::bootstrap();

// Forcing boostrap2 screws up things on 3.x for some reason
// RHtmlMedia::setFramework('bootstrap2');

require_once JPATH_COMPONENT_SITE . '/redform.defines.php';

// Register backend prefix
RLoader::registerPrefix('Redform', __DIR__);

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
