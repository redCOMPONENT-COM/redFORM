<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * redFORM Section Field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       3.3.8
 */
class RedformFormFieldBillingfield extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'billingfield';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Get an instance of billing form
		RForm::addFormPath(JPATH_SITE . '/components/com_redform/models/forms');
		$model = RModel::getFrontInstance('billing', array('ignore_request' => true), 'com_redform');
		$form = $model->getForm(array(), false);

		$options = array();

		if ($fieldsets = $form->getFieldsets())
		{
			foreach ($form->getFieldsets() as $fieldset)
			{
				foreach ($fieldset as $field)
				{
					if (!in_array($field->fieldname, array('id', 'cart_id')))
					{
						$option = new stdclass;
						$option->value = $field->fieldname;
						$option->text = $field->getTitle();
						$options[] = $option;
					}
				}
			}
		}
		else
		{
			foreach ($form->getFieldset() as $field)
			{
				if (!in_array($field->fieldname, array('id', 'cart_id')))
				{
					$option = new stdclass;
					$option->value = $field->fieldname;
					$option->text = $field->getTitle();
					$options[] = $option;
				}
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
