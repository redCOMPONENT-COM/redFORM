<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfHelperLayout
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       3.0
 */
class RdfHelperLayout extends RLayoutHelper
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
		if (!$options)
		{
			$options = array();
		}

		if (!isset($options['suffixes']))
		{
			if (JComponentHelper::getParams('com_redform')->get('form_layout') == 'bootstrap'
				|| (JFactory::getApplication()->isAdmin() && JFactory::getApplication()->input->get('options') == 'com_redform'))
			{
				$options['suffixes'] = array('bootstrap');
			}
			else
			{
				$options['suffixes'] = array('j25');
			}
		}

		return parent::render($layoutFile, $displayData, $basePath, $options);
	}
}
