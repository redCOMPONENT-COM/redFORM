<?php
/**
 * @package     Redform
 * @subpackage  Payment.pagseguro
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . "/plugins/redform_payment/pagseguro/PagSeguroLibrary/PagSeguroLibrary.php";

/**
 * Pagseguro payment helper
 *
 * @package     Redform
 * @subpackage  Payment.pagseguro
 * @since       2.0
 */
class PaymentPagseguro extends RdfPaymentHelper
{
	protected $gateway = 'pagseguro';

	/**
	 * sends the payment request associated to submit_key to the payment service
	 *
	 * @param   object  $request     request object
	 * @param   string  $return_url  return url
	 * @param   string  $cancel_url  cancel url
	 *
	 * @throws RedformPaymentException
	 * @return bool
	 */
	public function process($request, $return_url = null, $cancel_url = null)
	{
		if (!$this->params->get('account'))
		{
			echo JText::_('PLG_REDFORM_PAGSEGURO_MISSING_ACCOUNT');

			return false;
		}

		if (!$this->params->get('token'))
		{
			echo JText::_('PLG_REDFORM_PAGSEGURO_MISSING_TOKEN');

			return false;
		}

		$details = $this->getDetails($request->key);
		$reference = $request->key;

		$date = JFactory::getDate();

		// Instantiate a new payment request
		$paymentRequest = new PagSeguroPaymentRequest;

		if (!$this->currencyIsSupported($details->currency))
		{
			// Convert
			$price    = $this->convertPrice($this->getPrice($details), $details->currency, 'BRL');
			$currency = 'BRL';
		}
		else
		{
			$price    = $this->getPrice($details);
			$currency = $details->currency;
		}

		// Sets the currency
		$paymentRequest->setCurrency($currency);

		// Add an item for this payment request
		$paymentRequest->addItem('0001', $request->title, 1, sprintf("%.2f", $price));

		$paymentRequest->setReference($request->uniqueid);

		// Sets the url used by PagSeguro for redirect user after ends checkout process
		$paymentRequest->setRedirectUrl($this->getUrl('processing', $reference));
		$paymentRequest->setNotificationURL($this->getUrl('notify', $reference));

		// Get email and fullname from answers
//		$rfcore = new RdfCore;
//		$email = $rfcore->getSubmissionContactEmail($reference);
//
//		if ($email)
//		{
//			$paymentRequest->setSenderEmail($email['email']);
//
//			if (isset($email['fullname']))
//			{
//				$paymentRequest->setSenderName($email['fullname']);
//			}
//		}

		try
		{
			$credentials = new PagSeguroAccountCredentials($this->params->get('account'), $this->params->get('token'));

			// Register this payment request in PagSeguro, to obtain the payment URL for redirect your customer.
			$url = $paymentRequest->register($credentials);
		}
		catch (PagSeguroServiceException $e)
		{
			throw new RedformPaymentException('Pagseguro error: ' . $e->getMessage());
		}

		JHtml::_('behavior.mootools');
		$document = JFactory::getDocument();
		$document->addScript(JURI::root() .  "plugins/redform_payment/pagseguro/js/pagseguro.js");

		?>
		<h3><?php echo JText::_('PLG_REDFORM_PAGSEGURO_FORM_TITLE'); ?></h3>
		<?php echo JHtml::link($url, JText::_('PLG_REDFORM_IRIDIUM_FORM_OPEN_PAYMENT_WINDOW'), array('id' => 'pagsegurolink')); ?>
		<?php

		return true;
	}

	/**
	 * Handle the reception of notification
	 *
	 * @throws RedformPaymentException
	 * @return bool paid status
	 */
	public function notify()
	{
		$mainframe = JFactory::getApplication();

		$reference = $mainframe->input->get('reference');
		$mainframe->input->set('reference', $reference);

		RdfHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_PAGSEGURO_NOTIFICATION_RECEIVED', $reference));

		$code = $mainframe->input->get('notificationCode');
		$type = $mainframe->input->get('notificationType');

		if ($code && $type)
		{
			$notificationType = new PagSeguroNotificationType($type);
			$strType = $notificationType->getTypeFromValue();

			switch ($strType)
			{
				case 'TRANSACTION':
					$transaction = self::TransactionNotification($code);
					break;

				default:
					$error = "Unknown notification type [" . $notificationType->getValue() . "] for cart " . $reference;
					throw new RedformPaymentException($error);
			}
		}
		else
		{
			$error = "Invalid notification parameters for cart " . $reference;
			throw new RedformPaymentException($error);
		}

		try
		{
			$status_code = $transaction->getCode();

			if (!($status_code == 3 || $status_code == 4))
			{
				// Payment was refused
				switch ($status_code)
				{
					case 1:
						$reason = JText::_('PLG_REDFORM_PAGSEGURO_AWAITING_PAYMENT');
						break;
					case 2:
						$reason = JText::_('PLG_REDFORM_PAGSEGURO_IN_REVIEW');
						break;
					case 5:
						$reason = JText::_('PLG_REDFORM_PAGSEGURO_AT_ISSUE');
						break;
					case 7:
						$reason = JText::_('PLG_REDFORM_PAGSEGURO_CANCELLED');
						break;

					default:
						$reason = JText::sprintf('PLG_REDFORM_PAGSEGURO_PAYMENT_NOTIFICATION_UNKNOWN_CODE_S', $status_code);
						break;
				}

				$error = JText::sprintf('PLG_REDFORM_IRIDIUM_NOT_PAID_KEY_S_REASON_S', $reference, $reason);
				throw new RedformPaymentException($error);
			}

			$resp = array();
			$resp[] = 'Sender: ' . $transaction->getSender();
			$resp[] = 'Date: ' . $transaction->getDate();
			$resp[] = 'Amount: ' . $transaction->getGrossAmount();
			$resp = implode("\n", $resp);

			$this->writeTransaction($reference, $resp, 'SUCCESS', 1);
		}
		catch (RedformPaymentException $e) // Just easier for debugging...
		{
			RdfHelperLog::simpleLog($e->getMessage());
			$this->writeTransaction($reference, $e->getMessage() . $resp, 'FAIL', 0);

			return false;
		}

		return 1;
	}

	/**
	 * check transaction
	 *
	 * @param   string  $notificationCode  notification code
	 *
	 * @return PagSeguroTransaction
	 *
	 * @throws RedformPaymentException
	 */
	private function TransactionNotification($notificationCode)
	{
		$credentials = new PagSeguroAccountCredentials($this->params->get('account'), $this->params->get('token'));

		try
		{
			$transaction = PagSeguroNotificationService::checkTransaction($credentials, $notificationCode);
		}
		catch (PagSeguroServiceException $e)
		{
			throw new RedformPaymentException($e->getMessage());
		}

		return $transaction;
	}

	/**
	 * Check if the currency is supported by the plugin
	 *
	 * @param   string  $currency_code  3 letters iso code
	 *
	 * @return true if plugin is supported
	 */
	protected function currencyIsSupported($currency_code)
	{
		return $currency_code === 'BRL';
	}
}
