<?php
/**
 * @package     Redform.Library
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Layout helper for fast rendering
 *
 * @since  3.0
 */
class RdfLayoutFile extends RLayoutFile
{
	/**
	 * Get the default array of include paths
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getDefaultIncludePaths()
	{
		$paths = parent::getDefaultIncludePaths();

		// Comes after redcore base layouts
		array_splice($paths, count($paths) - 2, 0, JPATH_LIBRARIES . '/redform/layouts');

		// (lowest priority) custom defaultLayoutsPath
		if ($path = $this->options->get('defaultLayoutsPath'))
		{
			$paths[] = $path;
		}

		return $paths;
	}

	/**
	 * Refresh the list of include paths
	 *
	 * @return  void
	 */
	protected function refreshIncludePaths()
	{
		parent::refreshIncludePaths();

		// If method getDefaultIncludePaths does not exists we are in old Layout system
		if (version_compare(JVERSION, '3.0', '>') && version_compare(JVERSION, '3.5', '<'))
		{
			$redFormLayoutPath = JPATH_LIBRARIES . '/redform/layouts';

			// If we already added the path, then do not add it again
			if ($this->includePaths[count($this->includePaths) - 2] != $redFormLayoutPath)
			{
				// Comes after (1 - lower priority) Frontend base layouts
				array_splice($this->includePaths, count($this->includePaths) - 2, 0, $redFormLayoutPath);
			}

			// (lowest priority) custom defaultLayoutsPath
			if ($path = $this->options->get('defaultLayoutsPath'))
			{
				$this->includePaths[] = $path;
			}
		}
	}
}
