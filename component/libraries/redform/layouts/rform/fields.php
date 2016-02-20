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
$form = $data['form'];

$html = '';

// Custom tooltip
$toolTipArray = array('className' => 'redformtip' . $form->classname);
JHTML::_('behavior.tooltip', '.hasTipField', $toolTipArray);
JHtml::_('behavior.keepalive');

RHelperAsset::load('punycode.js');

RHelperAsset::load('formsteps.js', 'com_redform');
RHelperAsset::load('formsteps.css', 'com_redform');

if (isset($options['extrafields'][$index]))
{
	$fields = array_merge($options['extrafields'][$index], $fields);
}

foreach (RdfHelper::sortFieldBySection($fields) as $s)
{
	$section = RdfEntitySection::load($s->id);
	$section = '<fieldset class="redform-section' . ($section->class ? ' ' . $section->class : '') . '">';

	foreach ($s->fields as $field)
	{
		if ($field->isHidden())
		{
			$section .= $field->getInput();
			continue;
		}

		$class = "fieldline type-" . $field->fieldtype . $field->getParam('class', '');
		$fieldDiv = '<div class="' . $class . '">';

		if ($field->displayLabel())
		{
			$fieldDiv .= '<div class="label">' . $field->getLabel() . '</div>';
		}

		$fieldDiv .= '<div class="field">' . $field->getInput() . '</div>';

		if ($field->isRequired() || strlen($field->tooltip))
		{
			$fieldDiv .= '<div class="fieldinfo">';

			if ($field->isRequired())
			{
				$img = JHTML::image(JURI::root() . 'media/com_redform/images/warning.png', JText::_('COM_REDFORM_Required'));
				$fieldDiv .= ' <span class="editlinktip hasTipField" title="' . JText::_('COM_REDFORM_Required') . '" style="text-decoration: none; color: #333;">' . $img . '</span>';
			}

			if (strlen($field->tooltip) > 0)
			{
				$img = JHTML::image(JURI::root() . 'media/com_redform/images/info.png', JText::_('COM_REDFORM_ToolTip'));
				$fieldDiv .= ' <span class="editlinktip hasTipField" title="' . htmlspecialchars($field->field) . '::' . htmlspecialchars($field->tooltip) . '" style="text-decoration: none; color: #333;">' . $img . '</span>';
			}

			$fieldDiv .= '</div>';
		}

		// Fieldline_ div
		$fieldDiv .= '</div>';
		$section .= $fieldDiv;
	}

	$section .= '</fieldset>';

	$html .= $section;
}

echo $html;
