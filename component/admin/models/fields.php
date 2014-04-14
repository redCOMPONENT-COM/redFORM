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
				'formnane', 'fo.formnane',
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
		parent::populateState('f.ordering', 'asc');
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
			->join('INNER', '#__rwf_forms as fo ON fo.id = f.form_id')
			;

		// Filter by state.
		$state = $this->getState('filter.field_state');

		if (is_numeric($state))
		{
			$query->where('f.published = ' . (int) $state);
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
}
