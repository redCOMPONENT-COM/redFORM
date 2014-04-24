<?php
/**
 * @package     Redform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Find redCORE installer to use it as base system
if (!class_exists('Com_RedcoreInstallerScript'))
{
	$searchPaths = array(
		// Install
		dirname(__FILE__) . '/redCORE',
		// Discover install
		JPATH_ADMINISTRATOR . '/components/com_redcore',
		// Uninstall
		JPATH_LIBRARIES . '/redcore'
	);

	if ($redcoreInstaller = JPath::find($searchPaths, 'install.php'))
	{
		require_once $redcoreInstaller;
	}
	else
	{
		throw new Exception(JText::_('COM_REDFORM_INSTALLER_ERROR_REDCORE_IS_REQUIRED'), 500);
	}
}

/**
 * Class Com_redformInstallerScript
 *
 * @package     Redform
 * @subpackage  Installer
 * @since       3.0
 */
class Com_RedformInstallerScript extends Com_RedcoreInstallerScript
{
	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @return  boolean
	 */
	public function postflight($type, $parent)
	{
		// Execute the postflight tasks from the manifest
		$this->postFlightFromManifest($type, $parent);

		return true;
	}
}
