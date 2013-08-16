<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
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

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redform/classes/paymenthelper.class.php';

require_once JPATH_SITE . "/plugins/redform_payment/pagseguro/PagSeguroLibrary/PagSeguroLibrary.php";

class PaymentPagseguro extends RDFPaymenthelper
{
	protected $gateway = 'pagseguro';

	/**
	 * sends the payment request associated to submit_key to the payment service
	 * @param string $submit_key
	 */
	public function process($request, $return_url = null, $cancel_url = null)
	{
		if (!$this->params->get('account')) {
			echo JText::_('PLG_REDFORM_PAGSEGURO_MISSING_ACCOUNT');
			return false;
		}
		if (!$this->params->get('token')) {
			echo JText::_('PLG_REDFORM_PAGSEGURO_MISSING_TOKEN');
			return false;
		}

		$document = JFactory::getDocument();

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;
		$currency = $details->currency;

		$date = JFactory::getDate();

		// Instantiate a new payment request
		$paymentRequest = new PagSeguroPaymentRequest();

		if (!$details->currency == 'BRL')
		{
			echo JText::sprintf('PLG_REDFORM_PAGSEGURO_ONSUPPORTED_CURRENCY_S', $details->currency);
			return false;
		}

		// Sets the currency
		$paymentRequest->setCurrency($details->currency);
//		$paymentRequest->setCurrency('BRL');

		// Add an item for this payment request
		$paymentRequest->addItem('0001', $request->title, 1, sprintf("%.2f", $details->price));

		$paymentRequest->setReference($request->uniqueid);

		// Sets the url used by PagSeguro for redirect user after ends checkout process
		$paymentRequest->setRedirectUrl($this->getUrl('processing', $submit_key));
		$paymentRequest->setNotificationURL($this->getUrl('notify', $submit_key));

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
	}

	/**
	 * handle the recpetion of notification
	 * @return bool paid status
	 */
  public function notify()
  {
	  $mainframe = JFactory::getApplication();
	  $db = JFactory::getDBO();
	  $paid = 0;

	  $submit_key = $mainframe->input->get('key');
	  $mainframe->input->set('submit_key', $submit_key);

	  RedformHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_PAGSEGURO_NOTIFICATION_RECEIVED', $submit_key));

	  $code = $mainframe->input->get('notificationCode');
	  $type = $mainframe->input->get('notificationType');

	  if ($code && $type)
	  {
		  $notificationType = new PagSeguroNotificationType($type);
		  $strType = $notificationType->getTypeFromValue();

		  switch($strType)
		  {
			  case 'TRANSACTION':
				  $transaction = self::TransactionNotification($code);
				  break;

			  default:
				  $error = "Unknown notification type [".$notificationType->getValue()."] for key " . $submit_key;
				  throw new PaymentException($error);
		  }
	  }
	  else
	  {
		  $error = "Invalid notification parameters for key " . $submit_key;
		  throw new PaymentException($error);
	  }

	  $details = $this->_getSubmission($submit_key);

	  try
	  {
		  $status_code = $transaction->getCode();
		  if (!($status_code == 3 || $status_code == 4))
		  {
			  // payment was refused
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
			  $error = JText::sprintf('PLG_REDFORM_IRIDIUM_NOT_PAID_KEY_S_REASON_S'
				  , $submit_key, $reason);
			  throw new PaymentException($error);
		  }

		  $details = $this->_getSubmission($submit_key);

		  $resp = array();
		  $resp[] = 'Sender: ' . $transaction->getSender();
		  $resp[] = 'Date: ' . $transaction->getDate();
		  $resp[] = 'Amount: ' . $transaction->getGrossAmount();
		  $resp = implode("\n", $resp);

		  $this->writeTransaction($submit_key, $resp, 'SUCCESS', 1);
	  }
	  catch (PaymentException $e) // just easier for debugging...
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
}
