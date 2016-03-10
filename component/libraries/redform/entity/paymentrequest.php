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
}
