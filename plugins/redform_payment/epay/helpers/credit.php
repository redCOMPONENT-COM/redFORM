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
	 * @var RdfEntityCart
	 *
	 * @since __deploy_version__
	 */
	private $cart;

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

			$this->createCart($this->paymentRequest);

			$response = $this->creditTransaction($previousTransactionId, $amount);

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

	/**
	 * Get epayresponse error
	 *
	 * @param   string  $code  error code
	 *
	 * @return mixed
	 */
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

	/**
	 * Get pbs error
	 *
	 * @param   string  $code  code
	 *
	 * @return mixed
	 */
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

	/**
	 * Get transaction from epay
	 *
	 * @param   integer  $id  Transaction
	 *
	 * @return mixed
	 */
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

	/**
	 * Delete a transaction on epay
	 *
	 * @param   integer  $id  Transaction id
	 *
	 * @return mixed
	 */
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

	/**
	 * Credit transaction
	 *
	 * @param   integer  $id      transaction id
	 * @param   integer  $amount  amount
	 *
	 * @return mixed
	 */
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
			$error = array('Couldn\'t credit transaction ' . $id . "");
			$error = array_merge($error, $this->getReponseErrorStrings($response));

			throw new RuntimeException(implode(" / ", $error));
		}

		return $response;
	}

	/**
	 * Set as paid
	 *
	 * @param   mixed  $response  soap response
	 *
	 * @return void
	 */
	private function setAsPaid($response)
	{
		$data = array(
			'tid:' . $this->getTransactionId(),
			'operation:' . (isset($response->deleteResult) ? 'delete' : 'credit')
		);

		$payment = new RdfEntityPayment;
		$payment->date = JFactory::getDate()->toSql();
		$payment->data = implode("\n", $data);
		$payment->cart_id = $this->cart->id;
		$payment->status = 'SUCCESS';
		$payment->gateway = 'epay';
		$payment->paid = 1;

		$payment->save();

		$this->paymentRequest->paid = 1;
		$this->paymentRequest->save();
	}

	/**
	 * Creates cart
	 *
	 * @param   RdfEntityPaymentrequest  $paymentRequest  payment request
	 *
	 * @return RdfEntityCart
	 */
	private function createCart(RdfEntityPaymentrequest $paymentRequest)
	{
		$this->cart = new RdfEntityCart;
		$this->cart->init();
		$this->cart->addPaymentRequest($paymentRequest->id);
		$this->cart->refresh();

		return $this->cart;
	}

	/**
	 * Get error strings from soap call in array
	 *
	 * @param   object  $response  response from soap call
	 *
	 * @return array
	 */
	private function getReponseErrorStrings($response)
	{
		$error = array();

		if (!empty($response->pbsresponse))
		{
			$pbsresponse = $this->getPbsError($response->pbsresponse);

			if (!empty($pbsresponse->pbsresponsestring))
			{
				$error[] = $pbsresponse->pbsresponsestring;
			}
		}

		if (!empty($response->epayresponse))
		{
			$epayresponse = $this->getEpayError($response->epayresponse);

			if (!empty($epayresponse->epayresponsestring))
			{
				$error[] = $epayresponse->epayresponsestring;
			}
		}

		return $error;
	}
}
