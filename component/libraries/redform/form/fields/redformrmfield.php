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
 * redFORM redMEMBER Field
 *
 * @package     Redform.Library
 * @subpackage  Fields
 * @since       1.0
 */
class JFormFieldRedformRmField extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'RedformRmField';

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
		if (!REDMEMBER_INTEGRATION)
		{
			return false;
		}

		$options = array();

		// Filter by state
		$state = $this->element['state'] ? (int) $this->element['state'] : null;

		// Get the forms
		$items = $this->getFields($state);

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
	protected function getFields($state = null)
	{
		if (empty($this->cache))
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select('f.field_dbname AS value, CASE WHEN (t.tab_name) THEN CONCAT(t.tab_name, " - ", f.field_name) ELSE f.field_name END AS text')
				->from('#__redmember_fields AS f')
				->join('LEFT', '#__redmember_tab AS t ON t.tab_id = f.field_tabid')
				->order('text ASC');

			// Filter by state
			if (is_numeric($state))
			{
				$query->where('f.published = ' . $db->quote($state));
			}
			else
			{
				$query->where('f.published IN (0,1)');
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
