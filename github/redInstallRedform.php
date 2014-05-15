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
class redInstall extends JApplicationCli
{
	private $extension = 'redform';

    /**
     * Entry point for the script
     *
     * @return  void
     *
     * @since   1.0
     */
    public function doExecute()
    {
        $path = JPATH_ADMINISTRATOR . '/components/com_' . $this->extension;

        $db = JFactory::getDbo();

        // jimport('joomla.application.component.helper');
        $manifestFile = $path.'/com_' . $this->extension . '.xml';
        $manifest = new SimpleXMLElement(file_get_contents($manifestFile));
        $newVersion = (string) $manifest->version;

        $manifestCache = $db->setQuery(
                        $db->getQuery(TRUE)
                        ->select('manifest_cache')
                        ->from('#__extensions')
                        ->where('element = "com_' . $this->extension . '"'))
                        ->loadResult();
        $manifestCache = json_decode($manifestCache);


        $oldVersion = (string) $manifestCache->version;

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $sql = JFolder::files($path.'/sql/updates/mysql', '.sql');

        foreach($sql as $queryFile)
        {
            if (JFile::stripExt($queryFile) > $oldVersion)
            {
                $queryString = file_get_contents($path.'/sql/updates/mysql/'.$queryFile);
                $queries = JInstallerHelper::splitSql($queryString);

                // Process each query in the $queries array (split out of sql file).
                foreach ($queries as $query)
                {
                    $query = trim($query);

                    if ($query != '' && $query{0} != '#')
                    {
                        $db->setQuery($query);

                        if (!$db->execute())
                        {
                            JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), JLog::WARNING, 'jerror');

                            return false;
                        }
                    }
                }

            }
        }

        if ($newVersion > $oldVersion)
        {
            $manifestCache->version = $newVersion;
            $newManifestCache = json_encode($manifestCache);
            $db->setQuery(
                $db->getQuery(TRUE)
                ->update('#__extensions')
                ->set('manifest_cache = '. $db->q($newManifestCache))
                ->where('element = "com_' . $this->extension . '"'))
            ->execute();
            $this->out('Extension Version Updated');
        }
    }
}

JApplicationCli::getInstance('redInstall')->execute();
