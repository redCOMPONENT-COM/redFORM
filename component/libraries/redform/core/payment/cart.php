<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCorePaymentCart
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfCorePaymentCart
{
	/**
	 * @var  array
	 */
	protected $data;

	/**
	 * @var  JDatabaseDriver
	 */
	protected $db;

	/**
	 * @var RdfEntityBilling
	 */
	protected $billingInfo;

	/**
	 * Constructor
	 *
	 * @param   array  $config  optional config
	 */
	public function __construct($config = array())
	{
		$this->db = isset($config['db']) ? $config['db'] : JFactory::getDbo();
	}

	/**
	 * Get billing info
	 *
	 * @return RdfEntityBilling
	 */
	public function getBillingInfo()
	{
		if (empty($this->billingInfo))
		{
			if ($this->id)
			{
				$billingInfo = RdfEntityBilling::getInstance();
				$billingInfo->loadByCartId($this->id);

				$this->billingInfo = $billingInfo;
			}
		}

		return $this->billingInfo;
	}

	/**
	 * return a new cart for payment
	 *
	 * @param   string  $submitKey  submitkey for which we want a payment
	 *
	 * @return RdfEntityCart
	 *
	 * @throws Exception
	 */
	public function getNewCart($submitKey)
	{
		$paymentRequests = $this->getUnpaidSubmitKeyPaymentRequests($submitKey);

		if (!$paymentRequests)
		{
			throw new Exception('Nothing to pay');
		}

		$ids = array();
		$price = 0;
		$vat = 0;
		$currency = '';

		foreach ($paymentRequests as $pr)
		{
			$ids[] = $pr->id;
			$price += $pr->price;
			$vat += $pr->vat;
			$currency = $pr->currency;
		}

		$cart = RTable::getAdminInstance('Cart', array(), 'com_redform');
		$cart->reference = uniqid();
		$cart->created = JFactory::getDate()->toSql();
		$cart->price = $price;
		$cart->vat = $vat;
		$cart->currency = $currency;

		$cart->store();

		foreach ($ids as $id)
		{
			$cartItem = RTable::getAdminInstance('Cartitem', array(), 'com_redform');
			$cartItem->cart_id = $cart->id;
			$cartItem->payment_request_id = $id;
			$cartItem->store();
		}

		$this->data = $cart;

		$entity = RdfEntityCart::getInstance($cart->id);
		$entity->loadFromTable($cart);

		JPluginHelper::importPlugin('redform');
		RFactory::getDispatcher()->trigger('onAfterRedformCartCreated', array(&$entity));

		return $entity;
	}

	/**
	 * return a new cart for payment
	 *
	 * @param   string  $submitKey  submitkey for which we want a payment
	 *
	 * @return RdfEntityCart
	 *
	 * @throws Exception
	 */
	public function getSubmissionCart($submitKey)
	{
		$paymentRequests = $this->getUnpaidSubmitKeyPaymentRequests($submitKey);

		if (!$paymentRequests)
		{
			throw new Exception('Nothing to pay');
		}

		$ids = JArrayHelper::getColumn($paymentRequests, 'id');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__rwf_cart AS c')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.cart_id = c.id')
			->join('INNER', '#__rwf_payment_request AS pr ON pr.id = ci.payment_request_id')
			->where('ci.payment_request_id IN (' . implode(", ", $ids) . ')');

		$db->setQuery($query);

		if ($res = $db->loadObject())
		{
			$cart = RdfEntityCart::getInstance($res->id);
			$cart->bind($res);

			return $cart;
		}

		return $this->getNewCart($submitKey);
	}

	/**
	 * Load cart by id
	 *
	 * @param   int  $id  cart id
	 *
	 * @return $this
	 */
	public function loadById($id)
	{
		$table = RTable::getAdminInstance('Cart', array(), 'com_redform');
		$table->load($id);
		$this->data = $table;

		return $this;
	}

	/**
	 * Load cart by reference
	 *
	 * @param   string  $reference  reference
	 *
	 * @return $this
	 */
	public function loadByReference($reference)
	{
		$table = RTable::getAdminInstance('Cart', array(), 'com_redform');
		$table->load(array('reference' => $reference));
		$this->data = $table;

		return $this;
	}

	/**
	 * Getter
	 *
	 * @param   string  $name  property
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function __get($name)
	{
		if (isset($this->data->{$name}))
		{
			return $this->data->{$name};
		}

		throw new Exception('Property not found or not accessible: ' . $name);
	}

	/**
	 * write transaction to db
	 *
	 * @param   string  $gateway  payment gateway
	 * @param   string  $data     data from gateway
	 * @param   string  $status   status (paid, cancelled, ...)
	 * @param   int     $paid     1 for paid
	 * @param   string  $date     date in mysql format
	 *
	 * @return void
	 */
	public function writeTransaction($gateway, $data, $status, $paid, $date = null)
	{
		$table = RTable::getAdminInstance('Payment', array(), 'com_redform');
		$table->date = $date ?: JFactory::getDate()->toSql();
		$table->data = $data;
		$table->cart_id = $this->id;
		$table->status = $status;
		$table->gateway = $gateway;
		$table->paid = $paid;

		$table->store();

		if ($paid)
		{
			$this->setPaymentRequestAsPaid();
		}

		// Trigger event for custom handling
		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onPaymentAfterSave', array('com_redform.payment.helper', $table, true));
	}

	/**
	 * Return unpaid payment requests for submission associated to submit key
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return mixed
	 */
	private function getUnpaidSubmitKeyPaymentRequests($submitKey)
	{
		$query = $this->db->getQuery(true);

		$query->select('pr.id, pr.price, pr.vat, pr.currency')
			->from('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_submitters AS s ON s.id = pr.submission_id')
			->where('pr.paid = 0')
			->where('s.submit_key = ' . $this->db->quote($submitKey));

		$this->db->setQuery($query);
		$res = $this->db->loadObjectList();

		return $res;
	}

	/**
	 * set associated Payment Request As Paid
	 *
	 * @return void
	 */
	private function setPaymentRequestAsPaid()
	{
		$query = $this->db->getQuery(true);

		$query->update('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_cart_item AS ci on ci.payment_request_id = pr.id')
			->where('ci.cart_id = ' . $this->id)
			->set('pr.paid = 1');

		$this->db->setQuery($query);
		$this->db->execute();
	}
}
