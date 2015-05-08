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
 * Form Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       2.5
 */
class RedformModelPayment extends RModelAdmin
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->setState('payment_request', JFactory::getApplication()->input->getInt('pr', 0));
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  &$table  A reference to a JTable object.
	 *
	 * @return  void
	 */
	protected function prepareTable(&$table)
	{
		parent::prepareTable($table);

		if (!$table->id && !$table->cart_id)
		{
			$table->cart_id = $this->getNewCartId($table);
		}
	}

	/**
	 * Get a new cart
	 *
	 * @param   object  $table  current table
	 *
	 * @return int
	 */
	protected function getNewCartId($table)
	{
		$pr = $this->getState('payment_request');

		if (!$pr)
		{
			throw new RuntimeException('Missing payment request id');
		}

		$query = ' INSERT INTO #__rwf_cart (created, price, vat, currency, paid) '
			. ' SELECT NOW(), price, vat, currency, ' . $this->_db->quote($table->paid)
			. ' FROM #__rwf_payment_request '
			. ' WHERE id = ' . $pr;

		$this->_db->setQuery($query);
		$this->_db->execute();

		$cart_id = $this->_db->insertid();

		$row = $this->getTable('Cartitem');
		$row->cart_id = $cart_id;
		$row->payment_request_id = $pr;

		$row->store();

		return $cart_id;
	}

	/**
	 * set associated Payment Requests As Paid
	 *
	 * @return void
	 */
	public function setPaymentRequestsAsPaid()
	{
		$id = $this->getState($this->getName() . '.id');

		$query = $this->_db->getQuery(true);

		$query->update('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
			->join('INNER', '#__rwf_payment AS p ON p.cart_id = ci.cart_id')
			->where('p.id = ' . $id)
			->set('pr.paid = 1');

		$this->_db->setQuery($query);
		$this->_db->execute();
	}
}
