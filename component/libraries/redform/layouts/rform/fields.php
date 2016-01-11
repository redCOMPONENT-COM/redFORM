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

//$document = JFactory::getDocument();
//$document->addScript('/media/system/js/punycode.js');
RHelperAsset::load('punycode.js');

RHelperAsset::load('formsteps.js', 'com_redform');
RHelperAsset::load('formsteps.css', 'com_redform');

if (isset($options['extrafields'][$index]))
{
	$fields = array_merge($options['extrafields'][$index], $fields);
}

$sections = array_map(
	function ($item)
	{
		return $item->section_id;
	},
	$fields
);

$sortedSections = array();

foreach (array_unique($sections) as $section)
{
	$sortedSections[$section] = new stdClass;
	$sortedSections[$section]->id = $section;
	$sortedSections[$section]->fields = array();
}

foreach ($fields as $f)
{
	$sortedSections[$f->section_id]->fields[] = $f;
}

foreach ($sortedSections as $s)
{
	$section = RdfEntitySection::load($s->id);
	$html .= '<fieldset class="redform-section' . ($section->class ? ' ' . $section->class : '') . '">';

	foreach ($s->fields as $field)
	{
		if (!($app->isAdmin() || $field->published))
		{
			// Only display unpublished fields in backend form
			continue;
		}

		$field->setFormIndex($index);
		$field->setUser($user);

		// Set value if editing
		if ($answers && $field->id)
		{
			$value = $answers->getFieldAnswer($field->id);
			$field->setValue($value, true);
		}
		else
		{
			$field->lookupDefaultValue();
		}

		if (!$field->isHidden())
		{
			$html .= '<div class="fieldline type-' . $field->fieldtype . $field->getParam('class', '') . '">';
		}

		if (!$field->isHidden())
		{
			$element = "<div class=\"field\">";
		}
		else
		{
			$element = '';
		}

		if (!$field->isHidden() && $field->displayLabel())
		{
			$label = '<div class="label">' . $field->getLabel() . '</div>';
		}
		else
		{
			$label = '';
		}

		$element .= $field->getInput();

		if ($field->isHidden())
		{
			$html .= $element;
		}
		else
		{
			$html .= $label . $element;

			// Fieldtype div
			$html .= '</div>';

			if ($field->isRequired() || strlen($field->tooltip))
			{
				$html .= '<div class="fieldinfo">';

				if ($field->isRequired())
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

	$html .= '</fieldset>';
}

echo $html;
