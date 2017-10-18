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
	 * Method to install the component
	 *
	 * @param   object  $parent  Class calling this method
	 *
	 * @return  boolean          True on success
	 */
	public function installOrUpdate($parent)
	{
		parent::installOrUpdate($parent);

		$this->createDashboardModules();

		return true;
	}

	/**
	 * Creates the dashboard modules.
	 *
	 * @return  void
	 */
	private function createDashboardModules()
	{
		// Widgets
		$this->createBackendModule(
			'Dashboard Latest submissions Widget',
			'mod_redform_latest_submissions',
			'redform_dashboard_widget'
		);
	}

	/**
	 * Creates the Split Test chart admin module if not existing.
	 *
	 * @param   string   $title     The title of the module
	 * @param   string   $module    The module
	 * @param   string   $position  The module position
	 * @param   integer  $ordering  The module ordering
	 *
	 * @return  void
	 */
	private function createBackendModule($title, $module, $position, $ordering = 0)
	{
		$moduleTable = JTable::getInstance('Module');

		$moduleTable->load(array('module' => $module, 'position' => $position, 'client_id' => 1));

		$moduleTable->save(
			array(
				'title' => $title,
				'position' => $position,
				'ordering' => $ordering,
				'published' => 1,
				'module' => $module,
				'client_id' => 1,
				'access' => 1,
				'language' => '*',
				'params' => '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',
			)
		);

		// Assign the module to all pages
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('moduleid'))
			->from($db->qn('#__modules_menu'))
			->where($db->qn('moduleid') . ' = ' . $moduleTable->id);

		$db->setQuery($query);
		$menuExists = (bool) $db->loadResult();

		$menuObject = (object) array('moduleid' => $moduleTable->id, 'menuid' => 0);

		if ($menuExists)
		{
			$db->updateObject('#__modules_menu', $menuObject, 'moduleid');
		}

		else
		{
			$db->insertObject('#__modules_menu', $menuObject);
		}
	}
}
