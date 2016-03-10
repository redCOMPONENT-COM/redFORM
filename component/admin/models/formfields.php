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
class RedformModelFormfields extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_formfields';

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
				'id', 'ff.id',
				'field', 'f.field',
				'published', 'ff.published',
				'formname', 'fo.formname',
				'ordering', 'ff.ordering',
				'field_header', 'f.field_header',
				'fieldtype', 'f.fieldtype',
				'validate', 'ff.validate',
				'unique', 'ff.unique',
				's.ordering',
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
		parent::populateState($ordering ?: 'ff.ordering', $direction ?: 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @throws Exception
	 */
	protected function getListQuery()
	{
		$db	= $this->getDbo();

		$query = $db->getQuery(true)
			->select('ff.id, f.field, f.fieldtype, ff.validate, ff.unique, ff.ordering, ff.field_id, ff.published')
			->select('fo.formname')
			->select('s.name AS section')
			->from('#__rwf_fields as f')
			->join('INNER', '#__rwf_form_field as ff ON ff.field_id = f.id')
			->join('LEFT', '#__rwf_section as s ON s.id = ff.section_id')
			->join('INNER', '#__rwf_forms as fo ON fo.id = ff.form_id');

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
		else
		{
			throw new Exception('Form id is required');
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

		$order = !empty($orderList) ? $orderList : 'ff.ordering';
		$direction = !empty($directionList) ? $directionList : 'ASC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
