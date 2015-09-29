<?php
/**
 * @package     Redform
 * @subpackage  Payment.Quickpay
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */
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
		$details = $this->getDetails($request->key);
		$reference = $request->key;
		$currency = $details->currency;

		if (!$this->checkParameters())
		{
			return false;
		}

		$orderId = str_replace("-", "", $request->uniqueid) . strftime("%H%M%S");

		$req_params = array(
			'version' => 'v10',
			'merchant_id' => $this->params->get('merchant_id'),
			'agreement_id' => $this->params->get('agreement_id'),
			'order_id' => $orderId,
			'amount' => round($this->getPrice($details) * 100),
			'currency' => $currency,
			'continueurl' => $this->getUrl('processing', $reference),
			'cancelurl' => $this->getUrl('paymentcancelled', $reference),
			'callbackurl' => $this->getUrl('notify', $reference),
			'autocapture' => 0,
			'payment_methods' => $this->_getAllowedCard(),
			'description' => $request->title,
		);
		$req_params['checksum'] = $this->checksum($req_params);

		?>
		<h3><?php echo JText::_('Quickpay Payment Gateway'); ?></h3>
		<form action="https://payment.quickpay.net" method="post">
			<p><?php echo $request->title; ?></p>
			<?php foreach ($req_params as $key => $val): ?>
				<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>"/>
			<?php endforeach; ?>
			<input type="submit" value="Open Quickpay payment window"/>
		</form>
		<?php

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

		// Get operation
		$operation = reset($operations);

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
	 * returns allowed card types
	 *
	 * @return string
	 */
	private function _getAllowedCard()
	{
		$allowed = array();
		$methods = array(
			'american-express',
			'american-express-dk',
			'dankort',
			'danske-dk',
			'diners',
			'diners-dk',
			'edankort',
			'fbg1886',
			'jcb',
			'mastercard',
			'mastercard-dk',
			'mastercard-debet-dk',
			'nordea-dk',
			'visa',
			'visa-dk',
			'visa-electron',
			'visa-electron-dk',
			'paypal',
			'3d-jcb',
			'3d-maestro',
			'3d-maestro-dk',
			'3d-mastercard',
			'3d-mastercard-dk',
			'3d-mastercard-debet-dk',
			'3d-visa',
			'3d-visa-dk',
			'3d-visa-electron',
			'3d-visa-electron-dk',
		);

		foreach ($methods as $type)
		{
			if ($this->params->get($type))
			{
				$allowed[] = $type;
			}
		}

		return implode(",", $allowed);
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
}
