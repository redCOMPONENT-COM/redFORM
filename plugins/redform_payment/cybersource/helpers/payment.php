<?php
/**
 * @package     Redform
 * @subpackage  Payment.cybersource
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Handles Cybersource payments
 *
 * @package     Redform
 * @subpackage  Payment.cybersource
 * @since       2.5
 */
class PaymentCybersource extends RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'cybersource';

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
		$reference = $request->key;

		if (empty($return_url))
		{
			$return_url = $this->getUrl('notify', $reference);
		}

		if (empty($cancel_url))
		{
			$cancel_url = $this->getUrl('cancel', $reference);
		}

		// Get price and currency
		$details = $this->getDetails($reference);

		if ($this->params->get('sandbox', 1) == 1)
		{
			$target = "https://testsecureacceptance.cybersource.com/pay";
		}
		else
		{
			$target = "https://secureacceptance.cybersource.com/pay";
		}

		$lang = JFactory::getLanguage();
		$price = $this->getPrice($details);

		$post_variables = Array(
			"access_key" => $this->params->get('access_key'),
			"profile_id" => $this->params->get('profile_id'),
			"transaction_uuid" => uniqid(),
			"amount" => $price,
			"currency" => $details->currency,
			"locale" => $lang->getTag(),
			"override_backoffice_post_url" => $this->getUrl('notify', $reference),
			"override_custom_cancel_page" => $cancel_url,
			"override_custom_receipt_page" => $return_url,
			"reference_number" => $request->uniqueid,
			"signed_date_time" => gmdate("Y-m-d\TH:i:s\Z"),
			"transaction_type" => "authorization",
			"unsigned_field_names" => "",
			"signed_field_names" => "",
		);

		$billingInfo = $details->getBillingInfo();
		$post_variables = $this->addAddress($post_variables, $billingInfo);

		$fields = array_keys($post_variables);
		$post_variables["signed_field_names"] = implode(",", $fields);
		$post_variables["signature"] = $this->sign($post_variables);

		echo $this->plugin->render('process', compact('target', 'request', 'price', 'details', 'post_variables'));

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
		$paid = 0;

		$reference = $app->input->get('reference');
		$app->input->set('reference', $reference);
		RdfHelperLog::simpleLog('CYBERSOURCE NOTIFICATION RECEIVED for ' . $reference);

		$calcSig = $this->sign($_REQUEST);
		$postSig = $app->input->getString('signature');

		if (strcmp($calcSig, $postSig) != 0)
		{
			RdfHelperLog::simpleLog('CYBERSOURCE NOTIFICATION PAYMENT INVALID SIGNATURE for ' . $reference);

			$this->writeTransaction(
				$reference, 'INVALID SIGNATURE', 'ERROR', $paid
			);

			return 0;
		}

		if ($app->input->get('reason_code') != 100)
		{
			return $this->handleNotAccepted($_REQUEST);
		}

		// It was successull, get the details
		$resp = array();
		$resp[] = 'tid:' . $app->input->getString('transaction_id');
		$resp[] = 'amount:' . $app->input->get('auth_amount');
		$resp[] = 'currency:' . $app->input->get('req_currency');
		$resp[] = 'date:' . $app->input->get('signed_date_time');
		$resp = implode("\n", $resp);

		$this->writeTransaction($reference, $resp, 'SUCCESS', 1);

		return 1;
	}

	/**
	 * Add address field from billing info if available
	 *
	 * @param   array             $params       post params
	 * @param   RdfEntityBilling  $billingInfo  billing info
	 *
	 * @return array
	 */
	private function addAddress($params, $billingInfo)
	{
		if ($billingInfo->address)
		{
			$params['bill_to_surname'] = substr($billingInfo->fullname, 0, 60);
			$params['bill_to_address_line1'] = substr($billingInfo->address, 0, 60);
			$params['bill_to_address_postal_code'] = substr($billingInfo->zipcode, 0, 10);
			$params['bill_to_address_city'] = substr($billingInfo->city, 0, 50);

			if (strlen($billingInfo->country) == 2
				&& !is_numeric($billingInfo->country)
				&& RHelperCountry::isValid($billingInfo->country))
			{
				$params['bill_to_address_country'] = $billingInfo->country;
			}

			$params['bill_to_email'] = substr($billingInfo->email, 0, 255);
			$params['bill_to_company_name'] = substr($billingInfo->company, 0, 40);
		}

		return $params;
	}

	/**
	 * Handle payment not accepted
	 *
	 * @param   array  $params  parameters
	 *
	 * @return int
	 */
	private function handleNotAccepted($params)
	{
		switch ($params['decision'])
		{
			case "DECLINE":
				return $this->handleDecline($params);

			case "REVIEW":
				return $this->handleReview($params);

			case "ERROR":
				return $this->handleError($params);

			default:
				return $this->handleUndefinedDecision($params);
		}
	}

	/**
	 * Handle declined state
	 *
	 * @param   array  $params  parameters
	 *
	 * @return int
	 */
	private function handleDecline($params)
	{
		$reference = $this->input->get('reference');

		RdfHelperLog::simpleLog('CYBERSOURCE NOTIFICATION PAYMENT DECLINED for ' . $reference);

		$this->writeTransaction(
			$reference, $this->input->get('reason_code') . ': ' . $this->input->getString('message'), 'DECLINED', 0
		);

		return 0;
	}

	/**
	 * Handle Review state
	 *
	 * @param   array  $params  parameters
	 *
	 * @return int
	 */
	private function handleReview($params)
	{
		$reference = $this->input->get('reference');

		RdfHelperLog::simpleLog('CYBERSOURCE NOTIFICATION PAYMENT REVIEW for ' . $reference);

		$this->writeTransaction(
			$reference, $this->input->get('reason_code') . ': ' . $this->input->getString('message'), 'REVIEW', 0
		);

		return 0;
	}

	/**
	 * Handle generic error state
	 *
	 * @param   array  $params  parameters
	 *
	 * @return int
	 */
	private function handleError($params)
	{
		$reference = $this->input->get('reference');

		$message = $this->input->get('reason_code') . ': ' . $this->input->getString('message');

		if ($invalid_fields = $this->input->get('invalid_fields'))
		{
			$message .= "Invalid fields: " . $invalid_fields . ".";
		}

		if ($required_fields  = $this->input->get('required_fields'))
		{
			$message .= "Required fields: " . $required_fields . ".";
		}

		RdfHelperLog::simpleLog('CYBERSOURCE NOTIFICATION PAYMENT ERROR for ' . $reference . ": $message");

		$this->writeTransaction(
			$reference, $message, 'ERROR', 0
		);

		return 0;
	}

	/**
	 * Handle UndefinedDecision state
	 *
	 * @param   array  $params  parameters
	 *
	 * @return int
	 */
	private function handleUndefinedDecision($params)
	{
		$reference = $this->input->get('reference');

		RdfHelperLog::simpleLog('CYBERSOURCE NOTIFICATION INVALID DECISION for ' . $reference);

		$this->writeTransaction(
			$reference, $this->input->get('reason_code') . ': ' . $this->input->getString('message'), 'ERROR', 0
		);

		return 0;
	}

	/**
	 * Compute signature
	 *
	 * @param   array  $params  post params
	 *
	 * @return string
	 */
	private function sign($params)
	{
		$data = $this->buildDataToSign($params);
		$secretKey = $this->params->get('secret_key');

		return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
	}

	/**
	 * Get params string for signature
	 *
	 * @param   array  $params  parameters
	 *
	 * @return string
	 */
	private function buildDataToSign($params)
	{
		$signedFieldNames = explode(",", $params["signed_field_names"]);

		foreach ($signedFieldNames as $field)
		{
			$dataToSign[] = $field . "=" . $params[$field];
		}

		return implode(",", $dataToSign);
	}
}
