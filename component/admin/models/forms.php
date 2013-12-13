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
				'published', 'f.published',
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
	 * Adds a table if it doesn't exist yet
	 */
	private function AddFormTable($formid)
	{
		$db = JFactory::getDBO();
		/* construct form name */
		$q = "SHOW TABLES LIKE ".$db->Quote($db->getPrefix().'rwf_forms_'.$formid);
		$db->setQuery($q);
		$result = $db->loadResultArray();
		if (count($result) == 0) {
			/* Table doesn't exist, need to create it */
			$q = "CREATE TABLE ".$db->nameQuote('#__rwf_forms_'.$formid)." (";
			$q .= $db->nameQuote('id')." INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ";
			$q .= ") COMMENT = ".$db->Quote('redFORMS Form '.$formid);
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', $db->getErrorMsg());
		}
	}
}
