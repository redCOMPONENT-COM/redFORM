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
 * Create negative cart cancelling a multiple submission
 *
 * @package     Redform.Libraries
 * @subpackage  Payment
 * @since       3.3.21
 */
class RdfPaymentTurnmultiple
{
	/**
	 * @var RdfEntitySubmitter[]
	 *
	 * @since 3.3.21
	 */
	private $submissions;

	/**
	 * @var integer[]
	 *
	 * @since 3.3.21
	 */
	private $creditRequestsIds;

	/**
	 * constructor.
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @since 3.3.21
	 */
	public function __construct($submitKey)
	{
		$submissions = RdfEntitySubmitter::loadBySubmitKey($submitKey);
		$this->submissions = $submissions;
	}

	/**
	 * Turn a submission
	 *
	 * @return int id of created payment request
	 *
	 * @since 3.3.21
	 */
	public function turn()
	{
		$this->creditRequestsIds = array();

		foreach ($this->submissions as $submission)
		{
			$helper = new RdfPaymentTurnsubmission($submission->id);

			if ($creditRequestId = $helper->turn())
			{
				$this->creditRequestsIds[] = $creditRequestId;
			}
		}

		return $this->creditRequestsIds;
	}

	/**
	 * Try to refund the payment
	 *
	 * @return void
	 *
	 * @since 3.3.21
	 */
	public function processRefund()
	{
		if (!$latestPayment = $this->getPreviousPayment())
		{
			return false;
		}

		$paymentRequests = array();

		foreach ($this->creditRequestsIds as $paymentRequestId)
		{
			$paymentRequest = RdfEntityPaymentrequest::load($paymentRequestId);
			$paymentRequests[] = $paymentRequest;
		}

		if ($previousPayment = $this->getPreviousPayment())
		{
			JPluginHelper::importPlugin('redform');
			$dispatcher = JDispatcher::getInstance();

			JPluginHelper::importPlugin('redform_payment');
			$dispatcher->trigger(
				'onRedformCreditPaymentRequests', array($paymentRequests, $previousPayment)
			);
		}
	}

	/**
	 * Get a previous payment
	 *
	 * @return RdfEntityPayment
	 *
	 * @since 3.3.21
	 */
	private function getPreviousPayment()
	{
		foreach ($this->submissions as $submission)
		{
			$paymentRequest = $submission->getPaymentRequests();

			$paid = array_filter(
				$paymentRequest,
				function ($paymentRequest)
				{
					return $paymentRequest->paid && $paymentRequest->price > 0;
				}
			);

			if (!empty($paid))
			{
				$latest = reset($paid);

				return $latest->getPayment();
			}
		}

		return false;
	}
}
