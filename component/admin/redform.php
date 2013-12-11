<?php
/**
 * @package    Redform.Admin
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// TODO: this should go...
require_once (JPATH_COMPONENT_ADMINISTRATOR . '/helpers/helper.php');
require_once (JPATH_COMPONENT_SITE . '/redform.core.php');
/* Load the necessary stylesheet */
$document = JFactory::getDocument();
$document->addStyleSheet('com_redform/redform.css');

// Redmember integration
if (file_exists(JPATH_ROOT.'/components/com_redmember'))
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
RLoader::registerPrefix('RDF', JPATH_LIBRARIES . '/redform');

// Make available the fields
JFormHelper::addFieldPath(JPATH_LIBRARIES . '/redform/form/fields');

// Make available the form rules
JFormHelper::addRulePath(JPATH_LIBRARIES . '/redform/form/rules');

// Add the include path for html
JHtml::addIncludePath(JPATH_LIBRARIES . '/redform/html');

$app = JFactory::getApplication();

// Check access.
if (!JFactory::getUser()->authorise('core.manage', 'com_redform'))
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

	return false;
}

// Instantiate and execute the front controller.
$controller = JControllerLegacy::getInstance('Redform');
$controller->execute($app->input->get('task'));
$controller->redirect();
