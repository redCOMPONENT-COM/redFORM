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
 * @since       __deploy_version__
 */
class RdfRfieldRepeattext extends RdfRfieldTextfield
{
	/**
	 * Field type name
	 * @var string
	 */
	protected $type = 'repeattext';

	/**
	 * Get reference field for value check
	 *
	 * @return RdfRfield
	 */
	public function getReferenceField()
	{
		$referenceFieldId = $this->params->get('field');

		return array_reduce(
			$this->form->getFormFields(),
			function ($carry, $formfield) use ($referenceFieldId)
			{
				return $formfield->field_id == $referenceFieldId ? $formfield : $carry;
			}
		);
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	public function getInputProperties()
	{
		$properties = parent::getInputProperties();

		$validate =  'validate-custom' . $this->id;

		$properties['class']        = empty($properties['class']) ? $validate : $properties['class'] . ' ' . $validate;
		$properties['onpaste']      = 'return false;';
		$properties['onDrop']       = 'return false;';
		$properties['autocomplete'] = 'off';

		return $properties;
	}

	/**
	 * Check that data is valid
	 *
	 * @param   RdfRfield[]  $fields  all fields with value
	 *
	 * @return boolean
	 */
	public function validate($fields)
	{
		if (!parent::validate($fields))
		{
			return false;
		}

		$targetFieldId = $this->params->get('field');
		$targetField = array_reduce(
			$fields,
			function ($carry, $formfield) use ($targetFieldId)
			{
				return $formfield->field_id == $targetFieldId ? $formfield : $carry;
			}
		);

		if ($this->getValue() != $targetField->getValue())
		{
			$this->setError($this->name . ': ' . JText::_('LIB_REDFORM_FIELD_REPEATTEXT_VALUES_DONT_MATCH'));

			return false;
		}

		return true;
	}
}
