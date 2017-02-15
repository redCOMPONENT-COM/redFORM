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
			$return_url = $this->getUrl('notify', $reference);
		}

		if (empty($cancel_url))
		{
			$cancel_url = $this->getUrl('cancel', $reference);
		}

		echo RdfLayoutHelper::render(
			'notify',
			array(
				'action' => $this->getUrl('processing', $reference),
				'details' => $details,
				'request' => $request,
				'price' => $price,
				'params' => $this->params,
				'return' => $return_url,
				'cancel_return' => $cancel_url,
				'payment_type' => $this->params->get('payment_type', array())
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
	public function processing()
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
		$bankCode = $input->post->get('bankcode');
		$orderCode = $this->getOrderID($details->id);
		$paymentType = '';
		$discountAmount = 0;
		$orderDescription = '';
		$taxAmount = 0;
		$feeshipping = 0;
		$returnUrl = urlencode($input->post->getString('return_url'));
		$cancelUrl = urlencode($input->post->getString('return_url'));

		$billingInfo = $this->getBillingInfo($details->id);

		$buyerFullname = $billingInfo->fullname;
		$buyerEmail = $billingInfo->email;
		$buyerMobile = $billingInfo->phone;
		$buyerAddress = $billingInfo->address;

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
			return $app->redirect((string) $nlResult->checkout_url);
		}
		else
		{
			$this->writeTransaction($reference, implode("\n", $resp), $nlResult->error_message, 0);
			return $app->enqueueMessage($msg);
		}
	}

	/**
	 * handle the reception of notification
	 *
	 * @return bool paid status
	 */
	public function notify()
	{
		$app          = JFactory::getApplication();
		$input        = $app->input;
		$app          = JFactory::getApplication();
		$input        = $app->input;
		$reference    = $input->get('reference');
		$details      = $this->getDetails($reference);
		$token        = $input->getString('token');
		$merchantId   = $this->params->get('nganluong_merchant_id');
		$merchantPass = $this->params->get('nganluong_merchant_password');
		$email        = $this->params->get('nganluong_email');
		$url          = $this->params->get('nganluong_url_api');

		$nlCheckout = new NL_CheckOutV3($merchantId, $merchantPass, $email, $url);
		$nlResult   = $nlCheckout->GetTransactionDetail($token);
		$orderCode  = $this->getOrderID($details->id);
		$paid       = 0;

		$resp   = array();
		$resp[] = 'id:' . $nlResult->order_code;
		$resp[] = 'bank: ' . $nlResult->bank_code;
		$resp[] = 'amount: ' . $nlResult->total_amount;
		$resp[] = 'currency: ' . $details->currency;

		if ($nlResult)
		{
			$nlErrorCode         = (string) $nlResult->error_code;
			$nlTransactionStatus = (string) $nlResult->transaction_status;
			$dealId = JFactory::getSession()->get('deal_id');

			if ($nlResult->error_code == '00' && $nlTransactionStatus == '00')
			{
				$paid = 1;
				$this->writeTransaction($reference, implode("\n", $resp), 'SUCCESS', 1);
				$this->updateDeal($dealId, "Won");
			}
			else
			{
				$paid = 0;
				$this->writeTransaction($reference, implode("\n", $resp), 'FAIL', 0);
				$this->updateDeal($dealId, "Lost");
			}
		}

		return $paid;
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

	/**
	 * get order id
	 *
	 * @param   int  $cartId  cart ID
	 *
	 * @return object
	 */
	protected function getBillingInfo($cartId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('b.*')
			->from($db->qn('#__rwf_billinginfo', 'b'))
			->where($db->qn('b.cart_id') . ' = ' . $db->q((int) $cartId));

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * update Deal from Agile CRM
	 *
	 * @param   int     $dealId  deal ID
	 * @param   string  $status  status
	 *
	 * @return object
	 */
	protected function updateDeal($dealId, $status)
	{
		$opportunityJson = array(
			"id" => $dealId,
			"milestone" => $status
		);

		$opportunityJson = json_encode($opportunityJson);

		$return = $this->curlWrap("opportunity/partial-update", $opportunityJson, "PUT", "application/json");
	}

	/**
	 * Triggered after a form submissin has been saved.
	 *
	 * @param   RdfCoreFormSubmission  $result  The result
	 *
	 * @return  void
	 */
	public function curlWrap($entity, $data, $method, $contentType) 
	{
		if ($contentType == NULL) 
		{
		    $contentType = "application/json";
		}

		$plugin = JPluginHelper::getPlugin('redform', 'agile_crm');

		if (empty($plugin))
		{
			return false;
		}

		$params = new JRegistry($plugin->params);

		$agileUrl = "https://" . $params->get('domain') . ".agilecrm.com/dev/api/" . $entity;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);

		switch ($method) 
		{
			case "POST":
				$url = $agileUrl;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case "GET":
				$url = $agileUrl;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
				break;
			case "PUT":
				$url = $agileUrl;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
			case "DELETE":
				$url = $agileUrl;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			default:
				break;
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    "Content-type : $contentType;", 'Accept : application/json'
		));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $params->get('email') . ':' . $params->get('api_key'));
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$output = curl_exec($ch);
		curl_close($ch);

		return $output;
	}
}
