<?php
/**
 * @package     Redform.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = $displayData;

$app = JFactory::getApplication();

$options = $data['options'];
$fields = $data['fields'];
$answers = $data['answers'];
$user = $data['user'];
$index = $data['index'];

$html = '';

if (isset($options['extrafields']) && count($options['extrafields']))
{
	foreach ($options['extrafields'] as $field)
	{
		$html .= '<div class="fieldline' . (isset($field['class']) && !empty($field['class']) ? ' ' . $field['class'] : '' ) . '">';
		$html .= '<div class="label">' . $field['label'] . '</div>';
		$html .= '<div class="field">' . $field['field'] . '</div>';
		$html .= '</div>';
	}
}

foreach ($fields as $field)
{
	if (!($app->isAdmin() || $field->published))
	{
		// Only display unpublished fields in backend form
		continue;
	}

	// Init rfield
	$rfield = RdfRfieldFactory::getFormField($field->id);
	$rfield->setFormIndex($index);
	$rfield->setUser($user);

	// Set value if editing
	if ($answers)
	{
		$value = $answers->getFieldAnswer($field->id);
		$rfield->setValue($value, true);
	}
	else
	{
		$rfield->lookupDefaultValue();
	}

	if (!$rfield->isHidden())
	{
		$html .= '<div class="fieldline type-' . $field->fieldtype . $field->getParam('class', '') . '">';
	}

	if (!$rfield->isHidden())
	{
		$element = "<div class=\"field\">";
	}
	else
	{
		$element = '';
	}

	if (!$rfield->isHidden() && $rfield->displayLabel())
	{
		$label = '<div class="label">' . $rfield->getLabel() . '</div>';
	}
	else
	{
		$label = '';
	}

	$element .= $rfield->getInput();

	if ($rfield->isHidden())
	{
		$html .= $element;
	}
	else
	{
		$html .= $label . $element;

		// Fieldtype div
		$html .= '</div>';

		if ($rfield->isRequired() || strlen($field->tooltip))
		{
			$html .= '<div class="fieldinfo">';

			if ($rfield->isRequired())
			{
				$img = JHTML::image(JURI::root() . 'media/com_redform/images/warning.png', JText::_('COM_REDFORM_Required'));
				$html .= ' <span class="editlinktip hasTipField" title="' . JText::_('COM_REDFORM_Required') . '" style="text-decoration: none; color: #333;">' . $img . '</span>';
			}

			if (strlen($field->tooltip) > 0)
			{
				$img = JHTML::image(JURI::root() . 'media/com_redform/images/info.png', JText::_('COM_REDFORM_ToolTip'));
				$html .= ' <span class="editlinktip hasTipField" title="' . htmlspecialchars($field->field) . '::' . htmlspecialchars($field->tooltip) . '" style="text-decoration: none; color: #333;">' . $img . '</span>';
			}

			$html .= '</div>';
		}

		// Fieldline_ div
		$html .= '</div>';
	}
}

echo $html;
