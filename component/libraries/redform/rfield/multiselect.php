<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Rfield
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * redFORM field
 *
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @since       2.5
 */
class RedformRfieldMultiselect extends RedformRfieldSelect
{
	protected $type = 'multiselect';

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	protected function getFormElementName()
	{
		$name = parent::getFormElementName() . '[]';

		return $name;
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	protected function getSelectProperties()
	{
		$properties = parent::getSelectProperties();
		$properties['multiple'] = 'multiple';

		return $properties;
	}
}
