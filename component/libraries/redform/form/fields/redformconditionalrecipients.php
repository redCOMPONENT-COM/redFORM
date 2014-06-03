<?php
/**
 * @package     Redcore
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

JFormHelper::loadFieldClass('textarea');

/**
 * Text field.
 *
 * @package     Redcore
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldRedformconditionalrecipients extends JFormFieldTextarea
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Redformconditionalrecipients';

	public $fields = array();

	public $textarea;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Comparison functions
		$options = array(
			JHTML::_('select.option', 'between', JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_BETWEEN')),
			JHTML::_('select.option', 'inferior', JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_inferior')),
			JHTML::_('select.option', 'superior', JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_superior')),
		);
		$this->fields['functions'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_LABEL'),
			'field' => JHTML::_('select.genericlist', $options, 'cr_function', 'class="inputbox"')
		);

		$this->fields['email'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL'),
			'field' => '<input type="text" name="cr_email" id="cr_email"/>'
		);

		$this->fields['name'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL'),
			'field' => '<input type="text" name="cr_name" id="cr_name"/>'
		);

		$this->fields['field'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_LABEL'),
			'field' => '<select name="cr_field" id="cr_field"></select>'
		);

		$this->textarea = parent::getInput();

		return RdfHelperLayout::render('fields.redformconditionalrecipients', $this);
	}
}
