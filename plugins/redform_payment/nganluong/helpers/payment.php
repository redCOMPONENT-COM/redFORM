<?php
/**
 * @package     Redform
 * @subpackage  Payment.paypal
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Load Nganluong library
require_once dirname(__DIR__) . '/lib/NL_Checkoutv3.php';

/**
 * Paypal helper
 *
 * @package     Redform
 * @subpackage  Payment.paypal
 * @since       2.5
 */
class PaymentNganluong extends  RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'nganluong';

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
		$details = $this->getDetails($request->key);
		$reference = $request->key;

		$price = $this->getPrice($details);

		if (empty($return_url))
		{
			$return_url = $this->getUrl('processing', $reference);
		}

		if (empty($cancel_url))
		{
			$cancel_url = $this->getUrl('cancel', $reference);
		}

		echo RdfLayoutHelper::render(
			'notify',
			array(
				'action' => $this->getUrl('notify', $reference),
				'details' => $details,
				'request' => $request,
				'price' => $price,
				'params' => $this->params,
				"return" => $return_url,
				"cancel_return" => $cancel_url,
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
		$input = $app->input;
		$reference = $input->get('reference');
		$input->set('reference', $reference);
		$details = $this->getDetails($reference);
		$price = $this->getPrice($details);

		$merchantId = $this->params->get('nganluong_merchant_id');
		$merchantPass = $this->params->get('nganluong_merchant_password');
		$email = $this->params->get('nganluong_email');
		$url = $this->params->get('nganluong_url_api');

		$nlCheckout = new NL_CheckOutV3($merchantId, $merchantPass, $email, $url);
		$totalAmount = $price;
		$items = array();
		$paymentMethod = $input->post->get('option_payment');
		$bankCode = @$input->post->get('bankcode');
		$orderCode = $this->getOrderID($details->id);
		$paymentType = '';
		$discountAmount = 0;
		$orderDescription = '';
		$taxAmount = 0;
		$feeshipping = 0;
		$returnUrl = $input->post->get('return_url');
		$cancelUrl = urlencode($input->post->get('cancel_url'));

		$buyerFullname = $input->post->get('buyer_fullname');
		$buyerEmail = $input->post->get('buyer_email');
		$buyerMobile = $input->post->get('buyer_mobile');
		$buyerAddress = '';

		$resp = array();
		$resp[] = 'id:' . $orderCode;
		$resp[] = 'bank: ' . $bankCode;
		$resp[] = 'amount: ' . $price;
		$resp[] = 'currency: ' . $details->currency;

		if (!empty($paymentMethod) && !empty($buyerEmail) && !empty($buyerMobile) && !empty($buyerFullname))
		{
			if ($paymentMethod == "VISA")
			{
				$nlResult = $nlCheckout->VisaCheckout($orderCode, $totalAmount, $paymentType, $orderDescription, $taxAmount, $feeshipping, $discountAmount, $returnUrl, $cancelUrl, $buyerFullname, $buyerEmail, $buyerMobile, $buyerAddress, $items, $bankCode);
			}
			elseif ($paymentMethod == "NL")
			{
				$nlResult = $nlCheckout->NLCheckout($orderCode, $totalAmount, $paymentType, $orderDescription, $taxAmount, $feeshipping, $discountAmount, $returnUrl, $cancelUrl, $buyerFullname, $buyerEmail, $buyerMobile, $buyerAddress, $items);
			}
			elseif ($paymentMethod == "ATM_ONLINE" && !empty($bankCode))
			{
				$nlResult = $nlCheckout->BankCheckout($orderCode, $totalAmount, $bankCode, $paymentType, $orderDescription, $taxAmount, $feeshipping, $discountAmount, $returnUrl, $cancelUrl, $buyerFullname, $buyerEmail, $buyerMobile, $buyerAddress, $items);
			}
			elseif ($paymentMethod == "NH_OFFLINE")
			{
					$nlResult = $nlCheckout->officeBankCheckout($orderCode, $totalAmount, $bankCode, $paymentType, $orderDescription, $taxAmount, $feeshipping, $discountAmount, $returnUrl, $cancelUrl, $buyerFullname, $buyerEmail, $buyerMobile, $buyerAddress, $items);
			}
			elseif ($paymentMethod == "ATM_OFFLINE")
			{
					$nlResult = $nlCheckout->BankOfflineCheckout($orderCode, $totalAmount, $bankCode, $paymentType, $orderDescription, $taxAmount, $feeshipping, $discountAmount, $returnUrl, $cancelUrl, $buyerFullname, $buyerEmail, $buyerMobile, $buyerAddress, $items);
			}
			elseif ($paymentMethod == "IB_ONLINE")
			{
					$nlResult = $nlCheckout->IBCheckout($orderCode, $totalAmount, $bankCode, $paymentType, $orderDescription, $taxAmount, $feeshipping, $discountAmount, $returnUrl, $cancelUrl, $buyerFullname, $buyerEmail, $buyerMobile, $buyerAddress, $items);
			}
		}

		if ($nlResult->error_code == '00')
		{
			$this->writeTransaction($reference, implode("\n", $resp), 'SUCCESS', 1);
			$return = $app->redirect((string) $nlResult->checkout_url);
		}
		else
		{
			$this->writeTransaction($reference, implode("\n", $resp), 'FAIL', 0);
			$return = $nlResult->error_message;
		}

		return $return;
	}

	/**
	 * get order id
	 *
	 * @param   int  $cartId  cart ID
	 *
	 * @return object
	 */
	protected function getOrderID($cartId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('s.id')
			->from($db->qn('#__rwf_submitters', 's'))
			->leftjoin($db->qn('#__rwf_payment_request', 'pr') . ' ON ' . $db->qn('s.id') . ' = ' . $db->qn('pr.submission_id'))
			->leftjoin($db->qn('#__rwf_cart_item', 'ci') . ' ON ' . $db->qn('pr.id') . ' = ' . $db->qn('ci.payment_request_id'))
			->where($db->qn('ci.cart_id') . ' = ' . $db->q((int) $cartId));

		return $db->setQuery($query)->loadResult();
	}
}
