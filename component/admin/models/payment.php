<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

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

		PluginHelper::importPlugin('redform');
		$this->event_after_save = 'onPaymentAfterSave';
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A reference to a JTable object.
	 *
	 * @return  void
	 */
	protected function prepareTable($table)
	{
		parent::prepareTable($table);

		if (!$table->id && !$table->cart_id)
		{
			$table->cart_id = $this->getNewCartId($table);
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		if (!parent::save($data))
		{
			return false;
		}

		if ($data['paid'])
		{
			$this->setPaymentRequestsAsPaid();
		}

		return true;
	}

	/**
	 * Create a new cart for a payment request
	 *
	 * @param   int  $paymentRequestId  payment request id
	 *
	 * @return RdfEntityCart
	 */
	public function createCart($paymentRequestId)
	{
		$paymentRequest = RdfEntityPaymentrequest::load($paymentRequestId);

		if (!$paymentRequest->isValid())
		{
			throw new Exception('Payment request not found');
		}

		$cart = RTable::getAdminInstance('Cart', array(), 'com_redform');
		$cart->reference = uniqid();
		$cart->created = JFactory::getDate()->toSql();
		$cart->price = $paymentRequest->price;
		$cart->vat = $paymentRequest->vat;
		$cart->currency = $paymentRequest->currency;
		$cart->store();

		$cartItem = RTable::getAdminInstance('Cartitem', array(), 'com_redform');
		$cartItem->cart_id = $cart->id;
		$cartItem->payment_request_id = $paymentRequestId;
		$cartItem->store();

		$entity = RdfEntityCart::getInstance($cart->id);
		$entity->loadFromTable($cart);

		PluginHelper::importPlugin('redform');
		RFactory::getDispatcher()->trigger('onAfterRedformCartCreated', array(&$entity));

		return $entity;
	}

	/**
	 * Get a new cart
	 *
	 * @param   object  $table  current table
	 *
	 * @return integer
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

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  object|boolean  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (empty($item->id))
		{
			$item->cart_id = $this->getState('cart_id');
		}

		return $item;
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		parent::populateState();

		$this->setState('payment_request', Factory::getApplication()->input->getInt('pr', 0));
		$this->setState('cart_id', Factory::getApplication()->input->getInt('cart_id', 0));
	}
}
