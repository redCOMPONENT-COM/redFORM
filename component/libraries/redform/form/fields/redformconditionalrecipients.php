<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('textarea');

/**
 * conditional recipients field.
 *
 * @package     Redform.Library
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
		JText::script('COM_REDFORM_CONDITIONAL_RECIPIENTS_FROMNAME_MIN');
		JText::script('COM_REDFORM_CONDITIONAL_RECIPIENTS_FROMNAME_MAX');
		JText::script('COM_REDFORM_CONDITIONAL_RECIPIENTS_FROMNAME_EQUAL');
		JText::script('COM_REDFORM_CONDITIONAL_RECIPIENTS_FROMNAME_REGEX');

		// Comparison functions
		$functionsOptions = array(
			JHTML::_('select.option', 'between', JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_BETWEEN')),
			JHTML::_('select.option', 'inferior', JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_inferior')),
			JHTML::_('select.option', 'superior', JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_superior')),
			JHTML::_('select.option', 'equal', JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_EQUAL')),
			JHTML::_('select.option', 'regex', JText::_('COM_REDFORM_CONDITIONAL_RECIPIENTS_FUNCTION_REGEX')),
		);

		$textarea = parent::getInput();

		$data = array('textarea' => $textarea, 'functionsOptions' => $functionsOptions);

		return RdfHelperLayout::render('fields.redformconditionalrecipients', $data);
	}
}
