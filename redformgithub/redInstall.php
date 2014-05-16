<?php
/**
 * @package    Redevent.github
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file should be placed in a direct subdirectory of site root
 */

if (!defined('_JEXEC'))
{
	// Initialize Joomla framework
	define('_JEXEC', 1);
}

@ini_set('zend.ze1_compatibility_mode', '0');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', dirname(__DIR__));
}

if (!defined('_JDEFINES'))
{
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.php';

/**
 * Put an application online
 *
 * @package  Joomla.Shell
 *
 * @since    1.0
 */
class RedInstall extends JApplicationCli
{
	private $extension;

	private $manifest;

	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @throws Exception
	 */
	public function doExecute()
	{
		// Check if help is needed.
		if ($this->input->get('h') || $this->input->get('help'))
		{
			$this->help();

			return;
		}

		try
		{
			$this->extension = $this->input->get('extension');

			if (!$this->extension)
			{
				throw new Exception('Extension name must be specified');
			}

			$installer = JInstaller::getInstance();
			$path = $this->getManifestPath();

			if (!$installer->install($path))
			{
				$this->out($installer->getError());
			}

			$this->out('installer went fine');

			//$this->updateDatabase();
			//$this->updateManifestCache();
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage());
			$this->help();
		}
	}

	/**
	 * Update manifest_cache in database
	 *
	 * @return void
	 */
	private function updateManifestCache()
	{
		$db = JFactory::getDbo();

		$manifest = $this->getManifest();
		$newVersion = (string) $manifest->version;

		$manifestCache = $this->getManifestCache();
		$oldVersion = (string) $manifestCache->version;

		$this->out('Current manifest version: ' . $oldVersion);
		$this->out('Replacing with: ' . $newVersion);

		$manifestCache->version = $newVersion;
		$manifestCache->creationDate = (string) $manifest->creationDate;
		$newManifestCache = json_encode($manifestCache);

		$db->setQuery(
			$db->getQuery(true)
				->update('#__extensions')
				->set('manifest_cache = ' . $db->q($newManifestCache))
				->where('element = "com_' . $this->extension . '"')
		)
			->execute();
		$this->out('Extension Version Updated');
	}

	/**
	 * Perform db update
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	private function updateDatabase()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$db = JFactory::getDbo();

		$oldVersion = $this->getCurrentDbSchemaVersion();

		$path = $this->getPath();
		$files = JFolder::files($path . '/sql/updates/mysql', '.sql');
		usort($files, 'version_compare');

		foreach ($files as $queryFile)
		{
			if (version_compare(JFile::stripExt($queryFile), $oldVersion) > 0)
			{
				$this->out('Running sql update file: ' . $queryFile);
				$queryString = file_get_contents($path . '/sql/updates/mysql/' . $queryFile);
				$queries = JInstallerHelper::splitSql($queryString);

				// Process each query in the $queries array (split out of sql file).
				foreach ($queries as $query)
				{
					$query = trim($query);

					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);

						try
						{
							$db->execute();
						}
						catch (Exception $e)
						{
							$rethrow = new Exception($queryFile . ': ' . $e->getMessage());
							throw $rethrow;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Get current schema version
	 *
	 * @return mixed
	 */
	private function getCurrentDbSchemaVersion()
	{
		$row = JTable::getInstance('extension');
		$eid = $row->find(array('element' => strtolower('com_' . $this->extension), 'type' => 'component'));

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('version_id')
			->from('#__schemas')
			->where('extension_id = ' . $eid);
		$db->setQuery($query);
		$version = $db->loadResult();

		return $version;
	}

	/**
	 * Return manifest SimpleXMLElement
	 *
	 * @return SimpleXMLElement manifest
	 *
	 * @throws Exception
	 */
	private function getManifest()
	{
		if (!$this->manifest)
		{
			$path = $this->getPath();

			if (!$manifestFile = $this->getManifestPath())
			{
				// Not found !
				throw new Exception('Manifest not found in ' . $path);
			}

			$this->manifest = new SimpleXMLElement(file_get_contents($manifestFile));
		}

		return $this->manifest;
	}

	/**
	 * Return path to manifest, or false if not found
	 *
	 * @return bool|string
	 */
	private function getManifestPath()
	{
		$path = $this->getPath();

		if ($name = $this->input->get('manifest'))
		{
			if (file_exists($path . '/' . $name))
			{
				return $path . '/' . $name;
			}
		}
		else
		{
			$names = array(
				'com_' . $this->extension . '.xml',
				$this->extension . '.xml',
				'install.xml'
			);

			foreach ($names as $name)
			{
				if (file_exists($path . '/' . $name))
				{
					return $path . '/' . $name;
				}
			}
		}

		return false;
	}

	private function getManifestCache()
	{
		$db = JFactory::getDbo();

		$manifestCache = $db->setQuery(
			$db->getQuery(true)
				->select('manifest_cache')
				->from('#__extensions')
				->where('element = "com_' . $this->extension . '"')
		)
			->loadResult();
		$manifestCache = json_decode($manifestCache);

		return $manifestCache;
	}

	/**
	 * Path to admin
	 *
	 * @return string
	 */
	private function getPath()
	{
		return JPATH_ADMINISTRATOR . '/components/com_' . $this->extension;
	}

	/**
	 * display help
	 *
	 * @return void
	 */
	private function help()
	{
		$this->out('redInstaller');
		$this->out();
		$this->out('Usage:     php -f redInstaller.php [switches]');
		$this->out();
		$this->out('Switches:  --extension=<extension name>');
		$this->out('Switches:  --manifest=<manifest name>');
		$this->out('Switches:  -h | --help Prints this usage information.');
		$this->out();
	}
}

JApplicationCli::getInstance('RedInstall')->execute();
