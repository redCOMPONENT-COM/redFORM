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
 * Carts Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       3.3.8
 */
class RedformModelCarts extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_carts';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'cart_limit';

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
				'obj.id', 'obj.date',
				'paid', 'obj.paid'
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
		parent::populateState($ordering ?: 'obj.id', $direction ?: 'desc');
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
			->select('obj.*, CASE WHEN pr_paid.id THEN 1 ELSE 0 END AS paid')
			->from('#__rwf_cart as obj')
			->leftJoin('#__rwf_cart_item as item ON item.cart_id = obj.id')
			->leftJoin('#__rwf_payment_request as pr_paid ON pr_paid.id = item.payment_request_id AND pr_paid.paid = 1')
			->group('obj.id');

		// Filter search
		$search = $this->getState('filter.search_carts');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(obj.invoice_id LIKE ' . $search . ')');
		}

		if (is_numeric($this->getState('filter.paid')))
		{
			$query->leftJoin('#__rwf_payment_request as pr ON pr.id = item.payment_request_id')
				->where('pr.paid = ' . $this->getState('filter.paid'));
		}

		// Ordering
		$orderList = $this->getState('list.ordering');
		$directionList = $this->getState('list.direction');

		$order = !empty($orderList) ? $orderList : 'obj.id';
		$direction = !empty($directionList) ? $directionList : 'desc';
		$query->order($db->escape($order) . ' ' . $db->escape($direction));

		return $query;
	}
}
