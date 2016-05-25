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
		// Reset includePaths
		$paths = array();

		// (1 - highest priority) Received a custom high priority path
		if (!is_null($this->basePath))
		{
			$paths[] = rtrim($this->basePath, DIRECTORY_SEPARATOR);
		}

		// Component layouts & overrides if exist
		$component = $this->options->get('component', null);

		if (!empty($component))
		{
			// (2) Component template overrides path
			$paths[] = JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts/' . $component;

			// (3) Component path
			if ($this->options->get('client') == 0)
			{
				$paths[] = JPATH_SITE . '/components/' . $component . '/layouts';
			}
			else
			{
				$paths[] = JPATH_ADMINISTRATOR . '/components/' . $component . '/layouts';
			}
		}

		// (4) Standard Joomla! layouts overriden
		$paths[] = JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts';

		// (5 - lower priority) redform library base layouts
		$paths[] = JPATH_LIBRARIES . '/redform/layouts';

		// (6 - lower priority) Frontend base layouts
		$paths[] = JPATH_LIBRARIES . '/redcore/layouts';

		// (7 - lower priority) Frontend base layouts
		$paths[] = JPATH_ROOT . '/layouts';

		// (8 - lowest priority) custom defaultLayoutsPath
		if ($path = $this->options->get('defaultLayoutsPath'))
		{
			$paths[] = $path;
		}

		return $paths;
	}
}
