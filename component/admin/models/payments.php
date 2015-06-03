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
 * Payments Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       1.0
 */
class RedformModelPayments extends RModelList
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
				'id', 'date',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get associated billing info
	 *
	 * @return mixed
	 */
	public function getBilling()
	{
		$query = $this->_db->getQuery(true);

		$query->select('b.*')
			->from('#__rwf_billinginfo AS b')
			->join('INNER', '#__rwf_cart AS c ON c.id = b.cart_id')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.cart_id = c.id')
			->where('ci.payment_request_id = ' . $this->_db->quote($this->getState('payment_request')));

		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();

		return $res;
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
		parent::populateState('date', 'desc');

		$app = JFactory::getApplication();
		$this->setState('payment_request', $app->input->getInt('pr', 0));
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db	= $this->_db;

		$query = $db->getQuery(true)
			->select('p.*')
			->from('#__rwf_payment AS p')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.cart_id = p.cart_id');

		$query->where('ci.payment_request_id = ' . $db->quote($this->getState('payment_request')));

		// Ordering
		$orderList = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order = !empty($orderList) ? $orderList : 'date';
		$direction = !empty($directionList) ? $directionList : 'DESC';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
