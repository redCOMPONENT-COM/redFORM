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
 * Installation file
 */

/* ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class com_redformInstallerScript
{
	public $installer = null;

	/**
	 * Get the common JInstaller instance used to install all the extensions
	 *
	 * @return JInstaller The JInstaller object
	 */
	public function getInstaller()
	{
		if (is_null($this->installer))
		{
			$this->installer = new JInstaller;
		}

		return $this->installer;
	}

	/**
	 * Shit happens. Patched function to bypass bug in package uninstaller
	 *
	 * @param   JInstaller  $parent  Parent object
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getManifest($parent)
	{
		$element = strtolower(str_replace('InstallerScript', '', __CLASS__));
		$elementParts = explode('_', $element);

		if (count($elementParts) == 2)
		{
			$extType = $elementParts[0];

			if ($extType == 'pkg')
			{
				$rootPath = $parent->getParent()->getPath('extension_root');
				$manifestPath = dirname($rootPath);
				$manifestFile = $manifestPath . '/' . $element . '.xml';

				if (file_exists($manifestFile))
				{
					return JFactory::getXML($manifestFile);
				}
			}
		}

		return $parent->get('manifest');
	}

	/**
	 * Search a extension in the database
	 *
	 * @param   string  $element  Extension technical name/alias
	 * @param   string  $type     Type of extension (component, file, language, library, module, plugin)
	 * @param   string  $state    State of the searched extension
	 * @param   string  $folder   Folder name used mainly in plugins
	 *
	 * @return  integer           Extension identifier
	 */
	protected function searchExtension($element, $type, $state = null, $folder = null)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('extension_id')
			->from($db->quoteName("#__extensions"))
			->where("type = " . $db->quote($type))
			->where("element = " . $db->quote($element));

		if (!is_null($state))
		{
			$query->where("state = " . (int) $state);
		}

		if (!is_null($folder))
		{
			$query->where("folder = " . $db->quote($folder));
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		if ($type == 'update')
		{
			// get db version
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('s.version_id');
			$query->from('#__extensions AS e');
			$query->join('INNER', '#__schemas AS s ON s.extension_id = e.extension_id');
			$query->where('e.element = '.$db->Quote('com_redform'));
			$db->setQuery($query);
			$version = $db->loadResult();

			if (version_compare("2.0.b.5.0", $version))
			{
				$fields = $db->getTableColumns('#__rwf_values');
				if (!isset($fields['price']))
				{
					$query = ' ALTER TABLE #__rwf_values ADD '.$db->nameQuote('price') . ' DOUBLE NULL DEFAULT NULL ';
					$db->setQuery($query);
					try {
						$db->query();
					}
					catch (Exception $e) {
						echo $e->getMessage();
					}
				}
			}
		}
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		// Install library
		$this->installLibraries($parent);

		/* Install plugin */
		$plg = JPATH_SITE.'/administrator/components/com_redform/plugins/content/redform';

		$db = JFactory::getDbo();
		$installer = new JInstaller();

		if ($installer->install($plg))
		{
			// autopublish the plugin
			$query = ' UPDATE #__extensions SET enabled = 1 WHERE folder = '. $db->Quote('content') . ' AND element = '.$db->Quote('redform');
			$db->setQuery($query);

			if ($db->query())
			{
				echo JText::_('COM_REDFORM_Succesfully_installed_redform_content_plugin').'<br />';
			}
			else
			{
				echo JText::_('COM_REDFORM_Error_publishing_redform_content_plugin').'<br />';
			}
		}
		else
		{
			echo JText::_('COM_REDFORM_Plugin_install_failed') . $installer->getError().'<br />';
		}
	}

	/**
	 * Install the package libraries
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 */
	private function installLibraries($parent)
	{
		// Required objects
		$installer = $this->getInstaller();
		$manifest  = $parent->get('manifest');
		$src       = $parent->getParent()->getPath('source');

		if ($nodes = $manifest->libraries->library)
		{
			foreach ($nodes as $node)
			{
				$extName = $node->attributes()->name;
				$extPath = $src . '/libraries/' . $extName;
				$result  = 0;

				// Standard install
				if (is_dir($extPath))
				{
					$result = $installer->install($extPath);
				}
				elseif ($extId = $this->searchExtension($extName, 'library', '-1'))
					// Discover install
				{
					$result = $installer->discover_install($extId);
				}
			}
		}
	}

	/**
	 * method to uninstall the component
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function uninstall($parent)
	{
		// Uninstall extensions
		$this->uninstallLibraries($parent);
	}

	/**
	 * Uninstall the package libraries
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 */
	protected function uninstallLibraries($parent)
	{
		// Required objects
		$installer = $this->getInstaller();
		$manifest  = $this->getManifest($parent);

		if ($nodes = $manifest->libraries->library)
		{
			foreach ($nodes as $node)
			{
				$extName = $node->attributes()->name;
				$result  = 0;

				if ($extId = $this->searchExtension($extName, 'library', 0))
				{
					$result = $installer->uninstall('library', $extId);
				}
			}
		}
	}
}
