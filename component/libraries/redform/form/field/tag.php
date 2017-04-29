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
 * redFORM Field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       1.0
 */
class RedformFormFieldTag extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'tag';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$model = RModel::getAdminInstance('tags', ['ignore_request' => true], 'com_redform');

		if ($targetField = $this->element['target_field'] ? (string) $this->element['target_field'] : null)
		{
			$model->setState('filter.field', $targetField);
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$targetFieldName = $this->element['target_field'] ? (string) $this->element['target_field'] : null;
		$field = $this->form->getField($targetFieldName);

		$formField = $this->element['form_id_field'] ? (string) $this->element['form_id_field'] : null;
		$formId = $this->form->getValue($formField);

		return RdfLayoutHelper::render('redform.modal.tags', compact('field', 'formId'));
	}
}
