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
 * Forms Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedformModelForms extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_forms';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'form_limit';

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
				'formname', 'f.formname',
				'startdate', 'f.startdate',
				'enddate', 'f.enddate',
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
		parent::populateState('f.formname', 'asc');
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
			->select('f.startdate < NOW() AS formstarted')
			->from('#__rwf_forms as f')

			->group('f.id');

		// Filter by state.
		$state = $this->getState('filter.form_state');

		if (is_numeric($state))
		{
			$query->where('f.published = ' . (int) $state);
		}

		// Filter search
		$search = $this->getState('filter.search_forms');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(f.formname LIKE ' . $search . ')');
		}

		// Ordering
		$orderList = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order = !empty($orderList) ? $orderList : 'f.formname';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get a storage key.
		$store = $this->getStoreId();

		$items = $this->addSubmittersStat($items);

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $items;
	}

	/**
	 * Method to add number of submitters to forms
	 *
	 * @param   array  $items  items
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	private function addSubmittersStat($items)
	{
		if (!$items)
		{
			return $items;
		}

		$ids = array();

		foreach ($items as $i)
		{
			$ids[] = $i->id;
		}

		$query = $this->_db->getQuery(true);

		$query->select('form_id, COUNT(id) AS total')
			->from('#__rwf_submitters')
			->where('form_id IN (' . implode(',', $ids) . ')')
			->group('form_id');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList('form_id');

		foreach ($items as &$i)
		{
			if (isset($res[$i->id]))
			{
				$i->submitters = $res[$i->id]->total;
			}
			else
			{
				$i->submitters = 0;
			}
		}

		return $items;
	}
}
