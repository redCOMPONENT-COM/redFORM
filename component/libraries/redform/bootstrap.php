<?php
/**
 * @package    Redform.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDFORM_REDCORE_INIT_FAILED'), 404);
}

include_once $redcoreLoader;

/**
 * Redform bootstrap class
 *
 * @package     Redevent
 * @subpackage  System
 * @since       3.0
 */
class RdfBootstrap
{
	/**
	 * Effectively bootstraps Redform
	 *
	 * @return void
	 */
	public static function bootstrap()
	{
		if (!defined('REDFORM_BOOTSTRAPPED'))
		{
			// Sets bootstrapped variable, to avoid bootstrapping rEDEVENT twice
			define('REDFORM_BOOTSTRAPPED', 1);

			// Bootstraps redCORE
			RBootstrap::bootstrap();

			// For Joomla! 2.5 compatibility we load bootstrap2
			if (version_compare(JVERSION, '3.0', '<') && JFactory::getApplication()->input->get('view') == 'config')
			{
				RHtmlMedia::setFramework('bootstrap2');
			}

			// Register library prefix
			RLoader::registerPrefix('Rdf', __DIR__);

			// Make available the fields
			JFormHelper::addFieldPath(JPATH_LIBRARIES . '/redform/form/fields');

			// Make available the form rules
			JFormHelper::addRulePath(JPATH_LIBRARIES . '/redform/form/rules');

			if (file_exists(JPATH_LIBRARIES . '/redmember/library.php'))
			{
				RLoader::registerPrefix('Redmember', JPATH_LIBRARIES . '/redmember');
				define('REDFORM_REDMEMBER_INTEGRATION', true);
			}
			else
			{
				define('REDFORM_REDMEMBER_INTEGRATION', false);
			}
		}
	}
}
