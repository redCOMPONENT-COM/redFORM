<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Fields Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedformModelFields extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_fields';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'field_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration array
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'f.id',
				'field', 'f.field',
				'published', 'f.published',
				'formname', 'fo.formname',
				'ordering', 'f.ordering',
				'field_header', 'f.field_header',
				'fieldtype', 'f.fieldtype',
				'validate', 'f.validate',
				'unique', 'f.unique',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('f.field', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select('f.*')
			->select('fo.formname')
			->from('#__rwf_fields as f')
			->join('LEFT', '#__rwf_form_field as ff ON ff.field_id = f.id')
			->join('LEFT', '#__rwf_forms as fo ON fo.id = ff.form_id')
			->group('f.id');

		// Filter by state.
		$state = $this->getState('filter.field_state');

		if (is_numeric($state))
		{
			$query->where('f.published = ' . (int) $state);
		}

		$formId = $this->getState('filter.form_id');

		if (is_numeric($formId))
		{
			$query->where('ff.form_id = ' . (int) $formId);
		}

		// Filter type
		$type = $this->getState('filter.fieldtype');

		if (!empty($type))
		{
			$query->where('f.fieldtype = ' . $db->quote($type));
		}

		// Filter search
		$search = $this->getState('filter.search_fields');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(f.field LIKE ' . $search . ')');
		}

		// Ordering
		$orderList = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order = !empty($orderList) ? $orderList : 'f.field';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  object[]  An array of results.
	 *
	 * @since   3.0
	 * @throws  \RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$items = parent::_getList($query, $limitstart, $limit);

		if (empty($items))
		{
			return $items;
		}

		$db = \JFactory::getDbo();

		foreach ($items as &$field)
		{
			$query = $db->getQuery(true)
				->select('form_id')
				->from('#__rwf_form_field')
				->where('field_id = ' . $field->id);

			$db->setQuery($query);
			$formIds = $db->loadColumn();

			$field->forms = array_map(
				function ($formId)
				{
					return RdfEntityForm::load($formId);
				},
				$formIds
			);
		}

		return $items;
	}
}
