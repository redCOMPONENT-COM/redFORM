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
class JFormFieldRdfconditionalrecipients extends JFormFieldTextarea
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Rdfconditionalrecipients';

	protected $fields = array();

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
		$cr_function = JHTML::_('select.genericlist', $options, 'cr_function', 'class="inputbox"');

		$this->fields['functions'] = array('label' => JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_LABEL'))

		$text = array();
		$text[] = '<div id="cond_recipients_ui">';
		$text[] = '<label for="cr_email">' . JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_EMAIL_LABEL') . '</label>'
			. '<input type="text" name="cr_email" id="cr_email"/>';
		$text[] = '<label for="cr_name">' . JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_NAME_LABEL') . '</label>'
			. '<input type="text" name="cr_name" id="cr_name"/>';
		$text[] = '<label for="cr_field">' . JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FIELD_LABEL') . '</label>'
			. '<input type="text" name="cr_field" id="cr_field"/>';
		$text[] = '<label for="cr_function">' . JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_LABEL') . '</label>'
			. $cr_function;
		$text[] = '<span id="cr_params"></span>';
		$text[] = parent::getInput();

		return RLayoutHelper::render('fields.rdfconditionalrecipients', $this);
	}
}
