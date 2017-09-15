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
$multi = $data['multi'];

$html = '';

JHtml::_('rbootstrap.tooltip', '.hasToolTip');
JHtml::_('behavior.keepalive');

RHelperAsset::load('punycode.js');

RHelperAsset::load('formsteps.js', 'com_redform');
RHelperAsset::load('formsteps.css', 'com_redform');
JText::script('LIB_REFORM_STEPS_PREVIOUS');
JText::script('LIB_REFORM_STEPS_NEXT');

RHelperAsset::load('showon.js', 'com_redform');

if (isset($options['extrafields'][$index]))
{
	$fields = array_merge($options['extrafields'][$index], $fields);
}

if ($multi > 1)
{
	$html .= '<fieldset><legend class="subform-legend">' . JText::sprintf('COM_REDFORM_FIELDSET_SIGNUP_NB', $index) . '</legend>';
	$html .= '<div class="subform-fields">';
}

$sections = RdfHelper::sortFieldBySection($fields);

foreach ($sections as $s)
{
	$section = RdfEntitySection::load($s->id);
	$section = '<fieldset class="redform-section' . ($section->class ? ' ' . $section->class : '') . '">';

	foreach ($s->fields as $field)
	{
		$field->setForm($form);

		if ($field->isHidden())
		{
			$section .= $field->getInput();
			continue;
		}

		$class = "fieldline type-" . $field->fieldtype . $field->getParam('class', '');
		$rel = "";

		if ($showon = $field->getParam('showon'))
		{
			$showon = explode(':', $showon, 2);

			// We need the form field id, from the field id given in parameters
			$targetField = RdfHelper::findFormFieldByFieldId($fields, $showon[0]);
			$class .= ' rfshowon_' . implode(' rfshowon_', explode(',', $showon[1]));
			$rel = ' rel="rfshowon_field' . $targetField->id . '_' . $index . '"';
		}

		$fieldDiv = '<div class="' . $class . '"' . $rel . '>';

		if ($field->displayLabel())
		{
			$fieldDiv .= '<div class="label">';
			$fieldDiv .= $field->getLabel();

			if (!empty($field->tooltip))
			{
				$fieldDiv .= '<div class="label-field-tip">' . $field->tooltip . '</div>';
			}

			$fieldDiv .= '</div>';
		}

		$fieldDiv .= '<div class="field">' . $field->getInput() . '</div>';

		// Fieldline_ div
		$fieldDiv .= '</div>';
		$section .= $fieldDiv;
	}

	$section .= '</fieldset>';

	$html .= $section;
}

$html .= $this->sublayout('progressbar', $sections);

if ($multi > 1)
{
	$html .= '</div>';
	$html .= '</fieldset>';
}

echo $html;
