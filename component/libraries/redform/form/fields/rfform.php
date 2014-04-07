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
class JFormFieldRFForm extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'RFForm';

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
		$items = $this->getForms($state);

		// Build the field options
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = JHtml::_('select.option', $item->value, $item->text);
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
	protected function getForms($state = null)
	{
		if (empty($this->cache))
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select(array($db->qn('a.id', 'value'), $db->qn('a.formname', 'text')))
				->from($db->qn('#__rwf_forms', 'a'))
				->order('a.formname ASC');

			// Filter by state
			if (is_numeric($state))
			{
				$query->where('a.published = ' . $db->quote($state));
			}
			else
			{
				$query->where('a.published IN (0,1)');
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			if (is_array($result))
			{
				$this->cache = $result;
			}
		}

		return $this->cache;
	}
}
