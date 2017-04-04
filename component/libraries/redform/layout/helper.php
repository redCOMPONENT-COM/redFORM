<?php
/**
 * @package     Redevent.Library
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
class RdfLayoutHelper extends RLayoutHelper
{
	/**
	 * Method to render the layout.
	 *
	 * @param   string  $layoutFile   Dot separated path to the layout file, relative to base path
	 * @param   object  $displayData  Object which properties are used inside the layout file to build displayed output
	 * @param   string  $basePath     Base path to use when loading layout files
	 * @param   mixed   $options      Optional custom options to load. JRegistry or array format
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	public static function render($layoutFile, $displayData = null, $basePath = '', $options = null)
	{
		if (empty($options['suffixes']))
		{
			$options = is_null($options) ? array() : $options;
			$layoutSuffix = JComponentHelper::getParams('com_redform')->get('form_layout');

			if ($layoutSuffix == 'bootstrap'
				|| (JFactory::getApplication()->isAdmin() && JFactory::getApplication()->input->get('options') == 'com_redform'))
			{
				$options['suffixes'] = array('bootstrap');
			}
			elseif ($layoutSuffix)
			{
				$options['suffixes'] = array($layoutSuffix);
			}
		}

		$basePath = empty($basePath) ? self::$defaultBasePath : $basePath;

		// Make sure we send null to RLayoutFile if no path set
		$basePath = empty($basePath) ? null : $basePath;
		$layout = new RdfLayoutFile($layoutFile, $basePath, $options);
		$renderedLayout = $layout->render($displayData);

		return $renderedLayout;
	}
}
