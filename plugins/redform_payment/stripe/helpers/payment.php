<?php
/**
 * @package     Redform
 * @subpackage  Payment.stripe
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Load stripe library
require_once dirname(__DIR__) . '/lib/init.php';

/**
 * Handles Stripe payments
 *
 * @package     Redform
 * @subpackage  Payment.stripe
 * @since       2.5
 */
class PaymentStripe extends RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'stripe';

	/**
	 * Display or redirect to the payment page for the gateway
	 *
	 * @param   object  $request     payment request object
	 * @param   string  $return_url  return url for redirection
	 * @param   string  $cancel_url  cancel url for redirection
	 *
	 * @return true on success
	 */
	public function process($request, $return_url = null, $cancel_url = null)
	{
		$details = $this->getDetails($request->key);
		$reference = $request->key;

		$price = $this->getPrice($details);

		echo RdfLayoutHelper::render(
			'notify',
			array(
				'action' => $this->getUrl('notify', $reference),
				'details' => $details,
				'request' => $request,
				'price' => $price,
				'params' => $this->params
			),
			dirname(__DIR__) . '/layouts'
		);

		return true;
	}

	/**
	 * handle the reception of notification
	 *
	 * @return bool paid status
	 */
	public function notify()
	{
		$app = JFactory::getApplication();

		$reference = $app->input->get('reference');
		$app->input->set('reference', $reference);
		RdfHelperLog::simpleLog('STRIPE NOTIFICATION RECEIVED' . ' for ' . $reference);

		$details = $this->getDetails($reference);
		$price = $this->getPrice($details);

		\Stripe\Stripe::setApiKey($this->params->get('secretKey'));
		$token = $app->input->get('stripeToken');

		try
		{
			$charge = \Stripe\Charge::create(
				array(
					"amount" => round($price * 100),
					"currency" => $details->currency,
					"source" => $token,
					"description" => $reference)
			);
		}
		catch (\Stripe\Error\Card $e)
		{
			// The card has been declined
			RdfHelperLog::simpleLog('STRIPE NOTIFICATION PAYMENT REFUSED' . ' for ' . $reference);
			$this->writeTransaction(
				$reference, $e->getMessage(),
				$this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0
			);

			return 0;
		}

		$resp = array();
		$resp[] = 'id: ' . $charge->id;
		$resp[] = 'created: ' . $charge->created;
		$resp[] = 'paid: ' . $charge->paid;
		$resp[] = 'status: ' . $charge->status;
		$resp[] = 'amount: ' . $charge->amount;
		$resp[] = 'currency: ' . $charge->currency;

		$this->writeTransaction($reference, implode("\n", $resp), 'SUCCESS', 1);

		return 1;
	}
}
