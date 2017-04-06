<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Payment
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Create negative cart cancelling a submission
 *
 * @package     Redform.Libraries
 * @subpackage  Payment
 * @since       3.0
 */
class RdfPaymentTurnsubmission
{
	/**
	 * @var RdfEntitySubmitter
	 */
	private $submission;

	private $paidItems;

	/**
	 * RedformeconomicTurnsubmission constructor.
	 *
	 * @param   int  $submissionId  submission id
	 */
	public function __construct($submissionId)
	{
		$submission = RdfEntitySubmitter::load($submissionId);
		$this->submission = $submission;
	}

	/**
	 * Turn a submission
	 *
	 * @return int id of created payment request
	 */
	public function turn()
	{
		$paymentRequests = $this->submission->getPaymentRequests();

		$this->paidItems = array();

		foreach ($paymentRequests as $pr)
		{
			if ($pr->paid)
			{
				$paymentRequestsItems = $pr->getItems();
				$this->paidItems = array_merge($this->paidItems, $paymentRequestsItems);
			}
			else
			{
				// Delete unpaid
				$pr->delete();
			}
		}

		if (!$entity = $this->createPaymentRequest())
		{
			return false;
		}

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRedformAfterTurnSubmission', array($entity));

		if ($entity->price < 0 && $previousPayment = $this->getPreviousPayment())
		{
			JPluginHelper::importPlugin('redform_payment');
			$dispatcher->trigger('onRedformCreditPaymentRequest', array($entity, $previousPayment));
		}

		return $entity->id;
	}

	/**
	 * create Payment Request
	 *
	 * @return RdfEntityPaymentrequest
	 */
	private function createPaymentRequest()
	{
		// Create payment request
		$entity = RdfEntityPaymentrequest::getInstance();

		$entity->submission_id = $this->submission->id;
		$date = JFactory::getDate();
		$entity->created = $date->toSql();
		$entity->price = - $this->getTotalPrice();
		$entity->vat = - $this->getTotalVat();
		$entity->currency = $this->submission->currency;

		if (!($entity->price || $entity->vat))
		{
			return false;
		}

		$entity->save();

		$this->createPaymentRequestItems($entity->id);

		return $entity;
	}

	/**
	 * Create payment request items
	 *
	 * @param   int  $paymentRequestId  payment request id
	 *
	 * @return void
	 */
	private function createPaymentRequestItems($paymentRequestId)
	{
		foreach ($this->paidItems as $item)
		{
			// Create payment request
			$entity = RdfEntityPaymentrequestitem::getInstance();

			$entity->payment_request_id = $paymentRequestId;
			$entity->sku = $item->sku;
			$entity->label = $item->label;
			$entity->price = - $item->price;
			$entity->vat = - $item->vat;

			$entity->save();
		}
	}

	/**
	 * Get total price of paid items
	 *
	 * @return mixed
	 */
	private function getTotalPrice()
	{
		return array_reduce(
			$this->paidItems,
			function ($total, $item)
			{
				return $total += $item->price;
			}
		);
	}

	/**
	 * Get total vat of paid items
	 *
	 * @return mixed
	 */
	private function getTotalVat()
	{
		return array_reduce(
			$this->paidItems,
			function ($total, $item)
			{
				return $total += $item->vat;
			}
		);
	}

	/**
	 * Get a previous payment
	 *
	 * @return RdfEntityPayment
	 *
	 * @since __deploy_version__
	 */
	private function getPreviousPayment()
	{
		if (!$paymentRequests = $this->submission->getPaymentRequests())
		{
			return false;
		}

		$paid = array_filter(
			$paymentRequests,
			function ($paymentRequest)
			{
				return $paymentRequest->paid && $paymentRequest->price > 0;
			}
		);

		$latest = reset($paid);

		return $latest->getPayment();
	}
}
