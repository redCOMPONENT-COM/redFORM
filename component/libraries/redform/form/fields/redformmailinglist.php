<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

jimport('joomla.form.fields.list');

/**
 * Redform mailing list name
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       2.5
 */
class JFormFieldRedformmailinglist extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'Redformmailinglist';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		RHelperAsset::load('redformmailinglist.js', 'com_redform');

		if ($this->value && !is_array($this->value))
		{
			$values = array($this->value);
		}
		else
		{
			$values = $this->value;
		}

		$html = array('<div class="rfmailinglistnames">');

		$index = 0;

		foreach ((array) $values as $v)
		{
			$html[] = $this->getInputLine($v, $index++);
		}

		$html[] = '</div>';

		return implode("\n", $html);
	}

	/**
	 * Method to get one input line
	 *
	 * @param   string  $value  field value
	 * @param   int     $index  line index
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInputLine($value, $index)
	{
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$html = array('<div class="mailinglist-line">');

		$html[] = '<input type="text" name="' . $this->name . '[]"'
			. ' placeholder="' . JText::_('COM_REDFORM_FIELD_REDFORMMAILINGLIST_PLACEHOLDER') . '"'
			. ' value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"'
			. $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';

		$html[] = '<button type="button" class="add-ml btn btn-sm">' . JText::_('COM_REDFORM_FIELD_REDFORMMAILINGLIST_BUTTON_ADD') . '</button>';
		$html[] = '<button type="button" class="remove-ml btn btn-sm' . ($index ? '' : ' hide') . '">'
			. JText::_('COM_REDFORM_FIELD_REDFORMMAILINGLIST_BUTTON_REMOVE') . '</button>';

		$html[] = '</div>';

		return implode('', $html);
	}
}
