<?php
/**
 * @package     Redform.Library
 * @subpackage  Fields
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * redFORM Field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       1.0
 */
class RedformFormFieldField extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'field';

	/**
	 * A static cache.
	 *
	 * @var  array
	 */
	protected $cache = array();

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		// Filter by state
		$state = $this->element['state'] ? (int) $this->element['state'] : null;

		// Get the forms
		$items = $this->getFields($state);

		$excludeFields = array();
		$excludeFormFields = isset($this->element['excludeFormFields']) ?
			filter_var($this->element['excludeFormFields'], FILTER_VALIDATE_BOOLEAN) :
			false;

		if ($excludeFormFields)
		{
			$formId = $this->form->getField('form_id')->value;
			$form = RdfEntityForm::load($formId);
			$fields = $form->getFormFields();

			if ($fields)
			{
				$excludeFields = array_map(
					function($item)
					{
						return $item->field_id;
					},
					$fields
				);
			}
		}

		// Build the field options
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($item->value == $this->value || !in_array($item->value, $excludeFields))
				{
					$options[] = JHtml::_('select.option', $item->value, $item->text);
				}
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the list of forms.
	 *
	 * @param   integer  $state  The companies state
	 *
	 * @return  array  An array of company names.
	 */
	protected function getFields($state = null)
	{
		if (empty($this->cache))
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select(array($db->qn('a.id', 'value'), $db->qn('a.field', 'text')))
				->from($db->qn('#__rwf_fields', 'a'))
				->order('a.field ASC');

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup
	 */
	protected function getInput()
	{
		$filterform = $this->element['filterform'] ?: null;

		if ($filterform)
		{
			JFactory::getDocument()->addScriptDeclaration(
				'(function ($) {
					$(document).ready(function(){
						redformFieldsFilter("' . $this->name . '", "' . (String) $filterform . '");
					});
				})(jQuery);'
			);

			RHelperAsset::load('filterform.js', 'com_redform');
		}

		return parent::getInput();
	}
}
