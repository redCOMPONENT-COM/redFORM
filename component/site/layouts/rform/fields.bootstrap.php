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

if (isset($options['extrafields'][$index]))
{
	$fields = array_merge($options['extrafields'][$index], $fields);
}

foreach ($fields as $field)
{
	if (!($app->isAdmin() || $field->published))
	{
		// Only display unpublished fields in backend form
		continue;
	}

	// Init rfield
	$field->setFormIndex($index);
	$field->setUser($user);

	// Set value if editing
	if ($answers)
	{
		$value = $answers->getFieldAnswer($field->id);
		$field->setValue($value, true);
	}
	else
	{
		$field->lookupDefaultValue();
	}

	if ($field->isHidden())
	{
		$html .= $field->getInput();

		continue;
	}

	$html .= '<div class="control-group type-' . $field->fieldtype . $field->getParam('class', '') . '">';

	if ($field->displayLabel())
	{
		$html .= '<div class="control-label">' . $field->getLabel() . '</div>';
	}

	$html .= '<div class="controls">' . $field->getInput() . '</div>';


	$html .= '</div>';
}

echo $html;
