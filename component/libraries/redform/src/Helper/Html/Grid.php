<?php
/**
 * @package    Redform.Library
 *
 * @copyright  Copyright (C) 2009 - 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redform\Helper\Html;

defined('_JEXEC') or die;

/**
 * Html grid helper
 *
 * @package  Redform.Library
 * @since    3.3.23
 */
abstract class Grid extends \JHtmlRgrid
{
	/**
	 * Returns a isRequired state on a grid
	 *
	 * @param   integer       $value     The state value.
	 * @param   integer       $i         The row index
	 * @param   string|array  $prefix    An optional task prefix or an array of options
	 * @param   boolean       $enabled   An optional setting for access control on the action.
	 * @param   string        $checkbox  An optional prefix for checkboxes.
	 * @param   string        $formId    An optional form id
	 *
	 * @return  string  The HTML code
	 *
	 * @since    3.3.23
	 */
	public static function isRequired($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb', $formId)
	{
		if (is_array($prefix))
		{
			$options  = $prefix;
			$enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
			$checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
			$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		}

		$states = array(
			1 => array('unsetRequired', '', 'COM_REDFORM_REQUIRED', '', false, 'star', 'star'),
			0 => array('setRequired', '', 'COM_REDFORM_NOT_REQUIRED', '', false, 'star-empty', 'star-empty'),
		);

		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox, $formId);
	}
}