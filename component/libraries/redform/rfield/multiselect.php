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
class RdfRfieldMultiselect extends RdfRfieldSelect
{
	protected $type = 'multiselect';

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	public function getFormElementName()
	{
		$name = parent::getFormElementName() . '[]';

		return $name;
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	public function getSelectProperties()
	{
		$properties = parent::getSelectProperties();
		$properties['multiple'] = 'multiple';

		return $properties;
	}

	/**
	 * Get and set the value from post data, using appropriate filtering
	 *
	 * @param   int  $signup  form instance number for the field
	 *
	 * @return mixed
	 */
	public function getValueFromPost($signup)
	{
		$input = JFactory::getApplication()->input;

		$postName = 'field' . $this->load()->id . '_' . (int) $signup;

		$this->value = $input->get($postName, '', 'array');

		return $this->value;
	}

	/**
	 * Returns field value
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}
}
