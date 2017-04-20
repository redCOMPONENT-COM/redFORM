<?php
/**
 * @package     Redform
 * @subpackage  Payment.epay
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Handles Epay payment credit
 *
 * @package     Redform
 * @subpackage  Payment.epay
 * @since       3.3.18
 */
class PaymentEpayCredit
{
	private $wsdl = "https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL";

	/**
	 * @var RdfEntityPaymentrequest
	 * @since 3.3.18
	 */
	private $paymentRequest;

	/**
	 * @var RdfEntityPayment
	 * @since 3.3.18
	 */
	private $previousPayment;

	/**
	 * @var JRegistry
	 * @since 3.3.18
	 */
	private $params;

	/**
	 * @var SoapClient
	 * @since 3.3.18
	 */
	private $client;

	/**
	 * constructor
	 *
	 * @param   RdfEntityPaymentrequest  $paymentRequest   payment request to credit
	 * @param   RdfEntityPayment         $previousPayment  a previous payment for same submitter
	 * @param   JRegistry                $pluginParams     plugin parameters
	 *
	 * @since 3.3.18
	 */
	public function __construct(RdfEntityPaymentrequest $paymentRequest, RdfEntityPayment $previousPayment, JRegistry $pluginParams)
	{
		$this->paymentRequest = $paymentRequest;
		$this->previousPayment = $previousPayment;
		$this->params = $pluginParams;
	}

	/**
	 * Do the job
	 *
	 * @return boolean
	 *
	 * @since 3.3.18
	 */
	public function process()
	{
		try
		{
			$previousTransactionId = $this->getTransactionId();
			$amount                = round(abs($this->paymentRequest->price + $this->paymentRequest->vat) * 100);

			$transaction = $this->getTransaction($previousTransactionId);

			if ($transaction->transactionInformation->capturedamount == 0)
			{
				$response = $this->deleteTransaction($previousTransactionId);
			}
			else
			{
				$response = $this->creditTransaction($previousTransactionId, $amount);
			}

			$this->setAsPaid($response);
		}
		catch (Exception $e)
		{
			RdfHelperLog::simpleLog('EPAY CREDIT ERROR:' . $e->getMessage());
		}

		return true;
	}

	/**
	 * Get client
	 *
	 * @return SoapClient
	 *
	 * @since 3.3.18
	 */
	private function getClient()
	{
		if (!$this->client)
		{
			$this->client = new SoapClient($this->wsdl);
		}

		return $this->client;
	}

	/**
	 * Get previous transaction id
	 *
	 * @return string
	 *
	 * @since 3.3.18
	 */
	private function getTransactionId()
	{
		$lines = explode("\n", $this->previousPayment->data);

		if (!count($lines))
		{
			throw new RuntimeException('COULDNT GET PREVIOUS TRANSACTION ID FROM PAYMENT ' . $this->previousPayment->id);
		}

		$first = explode(":", $lines[0]);

		if (count($first) != 2)
		{
			throw new RuntimeException('COULDNT GET PREVIOUS TRANSACTION ID FROM PAYMENT ' . $this->previousPayment->id);
		}

		return $first[1];
	}

	private function getEpayError($code)
	{
		$client = $this->getClient();

		$params = array(
			"merchantnumber" => $this->params->get('EPAY_MERCHANTNUMBER'),
			"language" => 2,
			"epayresponsecode" => $code,
			"epayresponse" => ''
		);

		return $client->__soapCall("getEpayError", array($params));
	}

	private function getPbsError($code)
	{
		$client = $this->getClient();

		$params = array(
			"merchantnumber" => $this->params->get('EPAY_MERCHANTNUMBER'),
			"language" => 2,
			"pbsresponsecode" => $code,
			"epayresponse" => ''
		);

		return $client->__soapCall("getPbsError", array($params));
	}

	private function getTransaction($id)
	{
		$client = $this->getClient();

		$params = array(
			"merchantnumber" => $this->params->get('EPAY_MERCHANTNUMBER'),
			"language" => 2,
			"transactionid" => $id,
			"epayresponse" => ''
		);

		$response = $client->__soapCall("gettransaction", array($params));

		if (!$response->gettransactionResult)
		{
			throw new RuntimeException('Couldn\'t get transaction info for ' . $id);
		}

		return $response;
	}

	private function deleteTransaction($id)
	{
		$client = $this->getClient();

		$params = array(
			"merchantnumber" => $this->params->get('EPAY_MERCHANTNUMBER'),
			"language" => 2,
			"transactionid" => $id,
			"epayresponse" => ''
		);

		$response = $client->__soapCall("delete", array($params));

		if (!$response->deleteResult)
		{
			throw new RuntimeException('Couldn\'t delete transaction ' . $id);
		}

		return $response;
	}

	private function creditTransaction($id, $amount)
	{
		$client = $this->getClient();

		$params = array(
			"merchantnumber" => $this->params->get('EPAY_MERCHANTNUMBER'),
			"transactionid" => $id,
			"amount" => $amount,
			"pbsresponse" => "0",
			"epayresponse" => "0"
		);

		$response = $client->__soapCall("credit", array($params));

		if (!$response->creditResult)
		{
			throw new RuntimeException('Couldn\'t credit transaction ' . $id);
		}

		return $response;
	}

	private function setAsPaid($response)
	{
		$cart = new RdfEntityCart;
		$cart->init();
		$cart->addPaymentRequest($this->paymentRequest->id);
		$cart->refresh();

		$data = array(
			'tid:' . $this->getTransactionId(),
			'operation:' . (isset($response->deleteResult) ? 'delete' : 'credit')
		);

		$payment = new RdfEntityPayment;
		$payment->date = JFactory::getDate()->toSql();
		$payment->data = implode("\n", $data);
		$payment->cart_id = $cart->id;
		$payment->status = 'SUCCESS';
		$payment->gateway = 'epay';
		$payment->paid = 1;

		$payment->save();

		$this->paymentRequest->paid = 1;
		$this->paymentRequest->save();
	}
}
