<?php
/**
 * @package     Redform
 * @subpackage  Payment.paypal
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Paypal helper
 *
 * @package     Redform
 * @subpackage  Payment.paypal
 * @since       2.5
 */
class PaymentPaypal extends  RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'paypal';

	protected $params = null;

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
		$app = JFactory::getApplication();
		$submit_key = $request->key;

		if (empty($return_url))
		{
			$return_url = $this->getUrl('processing', $submit_key);
		}

		if (empty($cancel_url))
		{
			$cancel_url = $this->getUrl('cancel', $submit_key);
		}

		// Get price and currency
		$res = $this->_getSubmission($submit_key);

		if ($this->params->get('paypal_sandbox', 1) == 1)
		{
			$paypalurl = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		}
		else
		{
			$paypalurl = "https://www.paypal.com/cgi-bin/webscr";
		}

		$post_variables = Array(
			"cmd" => "_xclick",
			"business" => $this->params->get('paypal_account'),
			"item_name" => $request->title,
			"no_shipping" => '1',
			"invoice" => $request->uniqueid,
			"amount" => $res->price,
			"return" => $return_url,
			"notify_url" => $this->getUrl('notify', $submit_key),
			"cancel_return" => $cancel_url,
			"undefined_quantity" => "0",
			"currency_code" => $res->currency,
			"no_note" => "1"
		);

		$query_string = "?";

		foreach ($post_variables as $name => $value)
		{
			$query_string .= $name . "=" . urlencode($value) . "&";
		}

		$app->redirect($paypalurl . $query_string);
	}

	/**
	 * handle the recpetion of notification
	 *
	 * @return bool paid status
	 */
	public function notify()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

		$post = JRequest::get('post');
		$submit_key = JRequest::getvar('key');
		$paid = 0;

		// RdfHelperLog::simpleLog('PAYPAL NOTIFICATION RECEIVED'. ' for ' . $submit_key);
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		$data = array();

		foreach ($post as $key => $value)
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
			$data[] = "$key=$value";
		}

		// Assign posted variables to local variables
		$item_name = $post['item_name'];
		$item_number = $post['item_number'];
		$payment_status = $post['payment_status'];
		$payment_amount = $post['mc_gross'];
		$payment_currency = $post['mc_currency'];
		$txn_id = $post['txn_id'];
		$receiver_email = $post['receiver_email'];
		$payer_email = $post['payer_email'];

		// Post back to PayPal system to validate
		if ($this->params->get('paypal_sandbox', 1) == 1)
		{
			$paypalurl = "https://www.sandbox.paypal.com";
		}
		else
		{
			$paypalurl = "https://www.paypal.com";
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $paypalurl . '/cgi-bin/webscr');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.paypal.com'));
		$res = curl_exec($ch);
		curl_close($ch);

		if (strcmp($res, "VERIFIED") == 0)
		{
			/* Check the payment_status is Completed
			   check that txn_id has not been previously processed
			   check that receiver_email is your Primary PayPal email
			   check that payment_amount/payment_currency are correct */

			$res = $this->_getSubmission($submit_key);

			if ($payment_amount != $res->price)
			{
				RdfHelperLog::simpleLog('PAYPAL NOTIFICATION WRONG AMOUNT(' . $res->price . ') - ' . $submit_key);
			}
			elseif ($payment_currency != $res->currency)
			{
				RdfHelperLog::simpleLog('PAYPAL NOTIFICATION WRONG CURRENCY (' . $res->currency . ') - ' . $submit_key);
			}
			elseif (strcasecmp($payment_status, 'completed') == 0)
			{
				$paid = 1;
			}
		}
		elseif (strcmp($res, "INVALID") == 0)
		{
			// Log for manual investigation
			RdfHelperLog::simpleLog('PAYPAL NOTIFICATION INVALID IPN' . ' - ' . $submit_key);
		}
		else
		{
			RdfHelperLog::simpleLog('PAYPAL NOTIFICATION HTTP ERROR' . ' for ' . $submit_key);
		}

		// Log ipn
		$query = ' INSERT INTO #__rwf_payment (`date`, `data`, `submit_key`, `status`, `gateway`, `paid`) '
			. ' VALUES (NOW(), ' . $db->Quote(implode("\n", $data))
			. ', ' . $db->Quote($submit_key)
			. ', ' . $db->Quote($payment_status)
			. ', ' . $db->Quote('Paypal')
			. ', ' . $db->Quote($paid)
			. ') ';
		$db->setQuery($query);
		$db->query();

		// For routing
		JRequest::setVar('submit_key', $submit_key);

		return $paid;
	}
}
