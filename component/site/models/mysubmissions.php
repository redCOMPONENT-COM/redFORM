<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redform Component my submissions Model
 *
 * @package  Redform.Site
 * @since    3.3.17
 */
class RedformModelMysubmissions extends RModelList
{
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
				'id', 'submission_date'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  RdfEntitySubmitter[]  An array of results.
	 *
	 * @since   3.3.17
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$this->getDbo()->setQuery($query, $limitstart, $limit);

		if (!$results = $this->getDbo()->loadObjectList())
		{
			return false;
		}

		return RdfEntitySubmitter::loadArray($results);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.3.17
	 */
	protected function getListQuery()
	{
		if (!$user_id = (int) $this->getState('user_id'))
		{
			throw new RuntimeException('Not allowed', 500);
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__rwf_submitters')
			->where('integration = ""')
			->where('user_id = ' . $user_id);

		// Ordering
		$orderList = $this->getState('list.ordering') ?: 'id';
		$directionList = $this->getState('list.direction') ?: 'desc';
		$query->order($db->escape($orderList) . ' ' . $db->escape($directionList));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.3.17
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		$this->setState('user_id', $user->id);

		$params = $app->getParams();
		$this->setState('params', $params);

		return parent::populateState($ordering, $direction);
	}
}
