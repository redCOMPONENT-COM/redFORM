<?php
/**
 * @package     Redform
 * @subpackage  Payment.Quickpay
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');

/**
 * Quickpay helper
 *
 * @package     Redform
 * @subpackage  Payment.Quickpay
 * @since       2.5
 */
class PaymentQuickpay extends  RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'quickpay';

	/**
	 * @var \Joomla\Registry\Registry
	 */
	protected $params;

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
		$cart = $this->getDetails($request->key);
		$reference = $request->key;
		$currency = $cart->currency;
		$params = $this->params;

		if (!$this->checkParameters())
		{
			return false;
		}

		$orderId = $cart->id . strftime("%H%M%S");

		$language = JFactory::getLanguage();
		$quickpayLang = substr($language->getTag(), 0, 2);

		try
		{
			$client  = $this->getClient();
			$payment = $client->request->post(
				'/payments',
				[
					'order_id'          => $orderId,
					'currency'          => $currency,
					'text_on_statement' => substr($request->title, 0, 22),
				]
			);

			$status = $payment->httpStatus();

			if ($status !== 201)
			{
				throw new RuntimeException('Failed creating payment');
			}

			$paymentObject = $payment->asObject();

			$reqParams = array(
				'amount'       => round($this->getPrice($cart) * 100),
				'continueurl'  => $this->getUrl('processing', $reference),
				'cancelurl'    => $this->getUrl('cancel', $reference),
				'callbackurl'  => $this->getUrl('notify', $reference),
				'auto_capture' => $this->params->get('auto_capture', 0),
				'payment_methods' => $this->params->get('payment_methods', '3d-creditcard'),
				'description'  => $request->title,
				'language'     => $quickpayLang
			);

			if ($this->params->get('branding_id'))
			{
				$reqParams['branding_id'] = (int) $this->params->get('branding_id');
			}

			// Construct url to create payment link
			$endpoint = sprintf("/payments/%s/link", $paymentObject->id);

			// Issue a put request to create payment link
			$link = $client->request->put($endpoint, $reqParams);

			// Determine if payment link was created succesfully
			if ($link->httpStatus() !== 200)
			{
				throw new RuntimeException('Failed getting payment link');
			}

			// Get payment link url
			$paymentLink = $link->asObject()->url;
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			RdfHelperLog::simpleLog($e->getMessage());

			return false;
		}

		echo RdfLayoutHelper::render(
			'redform_payment.quickpay',
			compact('params', 'intro', 'paymentLink', 'return_url', 'cart'),
			'',
			array('defaultLayoutsPath' => dirname(__DIR__) . '/layouts')
		);

		return true;
	}

	/**
	 * handle the recpetion of notification
	 *
	 * @return bool paid status
	 */
	public function notify()
	{
		if (!$this->checkParameters())
		{
			return false;
		}

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$paid = 0;

		$input = $mainframe->input;

		$request_body = file_get_contents("php://input");
		$checksum = $this->signCallback($request_body);

		if ($checksum !== $_SERVER["HTTP_QUICKPAY_CHECKSUM_SHA256"])
		{
			RdfHelperLog::simpleLog('Wrong checksum for quickpay callback: ' . $checksum . '/' . $_SERVER["HTTP_QUICKPAY_CHECKSUM_SHA256"] . ' / ' . print_r($request_body, true));

			return false;
		}

		$reference = $input->get('reference');
		$input->set('submit_key', $reference);
		RdfHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_QUICKPAY_NOTIFICATION_RECEIVED', $reference));

		$details = $this->getDetails($reference);

		$callBackData = json_decode($request_body);

		// It was successull, get the details
		$operations = $callBackData->operations;

		if ($callBackData->test_mode && !$this->params->get('testmode'))
		{
			RdfHelperLog::simpleLog('Quickpay: received test mode transaction while not in test mode...' . print_r($this->params, true));

			return false;
		}

		if (!count($operations))
		{
			RdfHelperLog::simpleLog('Quickpay callback: no operations');

			return false;
		}

		// Get last operation
		$operation = end($operations);

		$resp = array();
		$resp[] = 'tid:' . $callBackData->order_id;
		$resp[] = 'orderid:' . $callBackData->order_id;
		$resp[] = 'amount:' . $operation->amount;
		$resp[] = 'currency:' . $callBackData->currency;
		$resp[] = 'date:' . $callBackData->created_at;
		$resp = implode("\n  ", $resp);

		if ($operation->qp_status_code !== '20000')
		{
			// Payment was refused
			$error = JText::sprintf('PLG_REDFORM_QUICKPAY_PAYMENT_REFUSED', $reference);
			RdfHelperLog::simpleLog($error);
			$this->writeTransaction($reference, $operation->qp_status_code . ': ' . $operation->qp_status_msg, 'FAIL', 0);

			return false;
		}

		if (!$callBackData->accepted)
		{
			// Payment was refused
			RdfHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_INITIAL', $reference));
			$this->writeTransaction(
				$reference,
				'Payment not accepted for reference: ' . $reference . "\n  " . $resp,
				'FAIL',
				0
			);

			return false;
		}

		$currency = $details->currency;

		if (strcasecmp($currency, $callBackData->currency))
		{
			$error = JText::sprintf('PLG_REDFORM_QUICKPAY_CURRENCY_MISMATCH', $reference);
			RdfHelperLog::simpleLog($error);
			$this->writeTransaction($reference, $error . $resp, 'FAIL', 0);

			return false;
		}

		if (round($this->getPrice($details) * 100) != $operation->amount)
		{
			$error = JText::sprintf('PLG_REDFORM_QUICKPAY_PRICE_MISMATCH', $reference);
			RdfHelperLog::simpleLog($error);
			$this->writeTransaction($reference, $error . $resp, 'FAIL', 0);

			return false;
		}
		else
		{
			$paid = 1;
		}

		$this->writeTransaction($reference, $resp, 'SUCCESS', 1);

		return $paid;
	}

	/**
	 * Compute checksum
	 *
	 * @param   array  $params  params without checksum
	 *
	 * @return string
	 */
	private function checksum($params)
	{
		ksort($params);
		$base = implode(" ", $params);

		return hash_hmac("sha256", $base, $this->params->get('api_key'));
	}

	/**
	 * Get checksum for callback
	 *
	 * @param   array  $base  input data
	 *
	 * @return string
	 */
	private function signCallback($base)
	{
		return hash_hmac("sha256", $base, $this->params->get('private_key'));
	}

	/**
	 * Check config
	 *
	 * @return bool
	 */
	private function checkParameters()
	{
		if (!$this->params->get('merchant_id'))
		{
			echo JText::_('PLG_REDFORM_QUICKPAY_MISSING_MERCHANT_ID');

			return false;
		}

		if (!$this->params->get('agreement_id'))
		{
			echo JText::_('PLG_REDFORM_QUICKPAY_MISSING_agreement_id');

			return false;
		}

		if (!$this->params->get('api_key'))
		{
			echo JText::_('PLG_REDFORM_QUICKPAY_MISSING_api_key');

			return false;
		}

		if (!$this->params->get('private_key'))
		{
			echo JText::_('PLG_REDFORM_QUICKPAY_MISSING_private_key');

			return false;
		}

		return true;
	}

	/**
	 * Get client
	 *
	 * @return \QuickPay\QuickPay
	 */
	private function getClient()
	{
		static $client;

		if (is_null($client))
		{
			$client = new QuickPay\QuickPay(":" . $this->params->get('api_key'));
		}

		return $client;
	}
}
