<?php
/**
 * @package     Redform.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Paymentrequest entity.
 *
 * @since  3.0
 */
class RdfEntityPaymentrequest extends RdfEntityBase
{
	/**
	 * Get submitter
	 *
	 * @return RdfEntitySubmitter
	 */
	public function getSubmitter()
	{
		$submitter = RdfEntitySubmitter::load($this->submission_id);

		return $submitter;
	}

	/**
	 * Get items
	 *
	 * @return RdfEntityPaymentrequestitem[]
	 */
	public function getItems()
	{
		if (!$this->hasId())
		{
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('pri.*')
			->from('#__rwf_payment_request_item AS pri')
			->where('pri.payment_request_id = ' . $this->id);

		$db->setQuery($query);

		if (!$res = $db->loadObjectList())
		{
			return false;
		}

		$items = array();

		foreach ($res as $data)
		{
			$item = RdfEntityPaymentrequestitem::getInstance();
			$item->bind($data);
			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Get associated payment
	 *
	 * @return RdfEntityPayment
	 *
	 * @since __deploy_version__
	 */
	public function getPayment()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('p.*')
			->from('#__rwf_payment AS p')
			->innerJoin('#__rwf_cart_item AS ci ON ci.cart_id = p.cart_id')
			->where('ci.payment_request_id = ' . $this->id)
			->where('p.paid = 1');

		$db->setQuery($query);

		if (!$res = $db->loadObject())
		{
			return false;
		}

		$entity = RdfEntityPayment::getInstance($res->id)->bind($res);

		return $entity;
	}
}
