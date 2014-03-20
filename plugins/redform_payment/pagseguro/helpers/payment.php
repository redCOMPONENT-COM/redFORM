<?php
/**
 * @package     Redform
 * @subpackage  Payment.pagseguro
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redform/classes/paymenthelper.class.php';
require_once JPATH_SITE . '/components/com_redform/redform.core.php';

require_once JPATH_SITE . "/plugins/redform_payment/pagseguro/PagSeguroLibrary/PagSeguroLibrary.php";

/**
 * Pagseguro payment helper
 *
 * @package     Redform
 * @subpackage  Payment.pagseguro
 * @since       2.0
 */
class PaymentPagseguro extends RDFPaymenthelper
{
	protected $gateway = 'pagseguro';

	/**
	 * sends the payment request associated to submit_key to the payment service
	 *
	 * @param   object  $request     request object
	 * @param   string  $return_url  return url
	 * @param   string  $cancel_url  cancel url
	 *
	 * @throws PaymentException
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

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;

		$date = JFactory::getDate();

		// Instantiate a new payment request
		$paymentRequest = new PagSeguroPaymentRequest();

		if (!$this->currencyIsSupported($details->currency))
		{
			// Convert
			$price    = $this->convertPrice($details->price, $details->currency, 'BRL');
			$currency = 'BRL';
		}
		else
		{
			$price    = $details->price;
			$currency = $details->currency;
		}

		// Sets the currency
		$paymentRequest->setCurrency($currency);

		// Add an item for this payment request
		$paymentRequest->addItem('0001', $request->title, 1, sprintf("%.2f", $price));

		$paymentRequest->setReference($request->uniqueid);

		// Sets the url used by PagSeguro for redirect user after ends checkout process
		$paymentRequest->setRedirectUrl($this->getUrl('processing', $submit_key));
		$paymentRequest->setNotificationURL($this->getUrl('notify', $submit_key));

		// Get email and fullname from answers
		$rfcore = new RedformCore;
		$emails = $rfcore->getSubmissionContactEmail($submit_key);

		if ($emails)
		{
			$contact = reset($emails);
			$paymentRequest->setSenderEmail($contact['email']);

			if (isset($contact['fullname']))
			$paymentRequest->setSenderName($contact['fullname']);
		}

		try
		{
			$credentials = new PagSeguroAccountCredentials($this->params->get('account'), $this->params->get('token'));

			// Register this payment request in PagSeguro, to obtain the payment URL for redirect your customer.
			$url = $paymentRequest->register($credentials);
		}
		catch (PagSeguroServiceException $e)
		{
			throw new PaymentException($e->getMessage());
		}

		?>
		<h3><?php echo JText::_('PLG_REDFORM_PAGSEGURO_FORM_TITLE'); ?></h3>
		<?php echo JHtml::link($url, JText::_('PLG_REDFORM_IRIDIUM_FORM_OPEN_PAYMENT_WINDOW')); ?>
		<?php

		return true;
	}

	/**
	 * Handle the reception of notification
	 *
	 * @throws PaymentException
	 * @return bool paid status
	 */
	public function notify()
	{
		$mainframe = JFactory::getApplication();

		$submit_key = $mainframe->input->get('key');
		$mainframe->input->set('submit_key', $submit_key);

		RedformHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_PAGSEGURO_NOTIFICATION_RECEIVED', $submit_key));

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
					$error = "Unknown notification type [" . $notificationType->getValue() . "] for key " . $submit_key;
					throw new PaymentException($error);
			}
		}
		else
		{
			$error = "Invalid notification parameters for key " . $submit_key;
			throw new PaymentException($error);
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

				$error = JText::sprintf('PLG_REDFORM_IRIDIUM_NOT_PAID_KEY_S_REASON_S', $submit_key, $reason);
				throw new PaymentException($error);
			}

			$resp = array();
			$resp[] = 'Sender: ' . $transaction->getSender();
			$resp[] = 'Date: ' . $transaction->getDate();
			$resp[] = 'Amount: ' . $transaction->getGrossAmount();
			$resp = implode("\n", $resp);

			$this->writeTransaction($submit_key, $resp, 'SUCCESS', 1);
		}
		catch (PaymentException $e) // Just easier for debugging...
		{
			RedformHelperLog::simpleLog($e->getMessage());
			$this->writeTransaction($submit_key, $e->getMessage() . $resp, 'FAIL', 0);

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
	 * @throws PaymentException
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
			throw new PaymentException($e->getMessage());
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
