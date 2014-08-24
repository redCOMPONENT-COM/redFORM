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
		$html .= '<div class="control-group ' . (isset($field['class']) && !empty($field['class']) ? ' ' . $field['class'] : '' ) . '">';
		$html .= '  <div class="control-label">' . $field['label'] . '</div>';
		$html .= '  <div class="controls">' . $field['field'] . '</div>';
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

	if ($rfield->isHidden())
	{
		$html .= $rfield->getInput();

		continue;
	}

	$html .= '<div class="control-group type-' . $field->fieldtype . $field->getParam('class', '') . '">';

	if ($rfield->displayLabel())
	{
		$html .= '<div class="control-label">' . $rfield->getLabel() . '</div>';
	}

	$html .= '<div class="controls">' . $rfield->getInput() . '</div>';


	$html .= '</div>';
}

echo $html;
