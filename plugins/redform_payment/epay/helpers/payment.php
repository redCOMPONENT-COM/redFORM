<?php
/**
 * @package     Redform
 * @subpackage  Payment.epay
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Handles Epay payments
 *
 * @package     Redform
 * @subpackage  Payment.epay
 * @since       2.5
 */
class PaymentEpay extends RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'epay';

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
		$document = JFactory::getDocument();
		$document->addScript("https://ssl.ditonlinebetalingssystem.dk/integration/ewindow/paymentwindow.js");

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;

		$currency = RHelperCurrency::getIsoNumber($details->currency);

		$price = $this->getPrice($details);

		$params = array(
			'merchantnumber' => $this->params->get('EPAY_MERCHANTNUMBER'),
			'currency' => $currency,
			'amount' => round($price * 100),
			'orderid' => $request->uniqueid,
			'windowstate' => $this->params->get('windowstate'),
			'paymentcollection' => $this->params->get('paymentcollection'),
			'lockpaymentcollection' => $this->params->get('lockpaymentcollection'),
			'language' => $this->params->get('language'),
			'instantcapture' => $this->params->get('instantcapture'),
			'callbackurl' => $this->getUrl('notify', $submit_key),
			'accepturl' => $this->getUrl('notify', $submit_key),
			'cancelurl' => $this->getUrl('cancel', $submit_key),
			'ordertext' => $request->title, // Shown in the payment window + receipt
			'group' => $this->params->get('group'),
			'description' => $request->adminDesc, // This description can be seen in the ePay administration
			'opacity' => 50,
		);

		if ($this->params->get('paymenttype'))
		{
			$types = implode(',', $this->params->get('paymenttype'));
			$params['paymenttype'] = $types;
		}

		?>
		<div id="payment-div"></div>
		<script>
			paymentwindow = new PaymentWindow({
				<?php

				foreach ($params as $key => $value)
				{
					echo "'" . $key . "': \"" . $value . "\",\n";
				}

				if ($this->params->get('md5'))
				{
					$hash = md5(implode("", array_values($params)));
					echo "'hash': \"" . $hash . "\"\n";
				}
				?>
			});
			<?php if ($this->params->get('windowstate') != '1'): ?>
			paymentwindow.append('payment-div');
			<?php endif; ?>
			paymentwindow.on('close', function(){
				alert("<?php echo JText::_('PLG_REDFORM_PAYMENT_EPAY_PAYMENT_WAS_CANCELLED'); ?>");
				window.location = "<?php echo $this->getUrl('cancel', $submit_key); ?>";
			});
			paymentwindow.on('declined', function(){
				alert("<?php echo JText::_('PLG_REDFORM_PAYMENT_EPAY_PAYMENT_WAS_DECLINED'); ?>");
				window.location = "<?php echo $this->getUrl('decline', $submit_key); ?>";
			});
			paymentwindow.open();
		</script>
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
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$paid = 0;

		$submit_key = JRequest::getvar('key');
		JRequest::setVar('submit_key', $submit_key);
		RdfHelperLog::simpleLog('EPAY NOTIFICATION RECEIVED' . ' for ' . $submit_key);

		if (JRequest::getVar('accept', 0) == 0)
		{
			// Payment was refused
			RdfHelperLog::simpleLog('EPAY NOTIFICATION PAYMENT REFUSED' . ' for ' . $submit_key);
			$this->writeTransaction(
				$submit_key, JRequest::getVar('error') . ': ' . JRequest::getVar('errortext'),
				$this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0
			);

			return 0;
		}

		// It was successull, get the details
		$resp = array();
		$resp[] = 'tid:' . JRequest::getVar('txnid');
		$resp[] = 'orderid:' . JRequest::getVar('orderid');
		$resp[] = 'amount:' . JRequest::getVar('amount');
		$resp[] = 'currency:' . JRequest::getVar('currency');
		$resp[] = 'date:' . JRequest::getVar('date');
		$resp[] = 'time:' . JRequest::getVar('time');
		$resp = implode("\n", $resp);

		$details = $this->_getSubmission($submit_key);
		$price = $this->getPrice($details);

		$currency = RHelperCurrency::getIsoNumber($details->currency);

		if (round($price * 100) != JRequest::getVar('amount'))
		{
			RdfHelperLog::simpleLog('EPAY NOTIFICATION PRICE MISMATCH' . ' for ' . $submit_key);
			$this->writeTransaction($submit_key, 'EPAY NOTIFICATION PRICE MISMATCH' . "\n" . $resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);

			return false;
		}
		else
		{
			$paid = 1;
		}

		if ($currency != JRequest::getVar('currency'))
		{
			RdfHelperLog::simpleLog('EPAY NOTIFICATION CURRENCY MISMATCH' . ' for ' . $submit_key);
			$this->writeTransaction($submit_key, 'EPAY NOTIFICATION CURRENCY MISMATCH' . "\n" . $resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);

			return false;
		}

		if ($this->params->get('md5', 0) > 0)
		{
			$params = $_GET;
			$var = "";

			$exclude = array('hash');

			foreach ($params as $key => $value)
			{
				if (!in_array($key, $exclude))
				{
					$var .= $value;
				}
			}

			$genstamp = md5($var . $this->params->get('EPAY_MD5_KEY'));

			if ($genstamp != $_GET["hash"])
			{
				RdfHelperLog::simpleLog('EPAY NOTIFICATION MD5 KEY MISMATCH' . ' for ' . $submit_key);
				$this->writeTransaction($submit_key, 'EPAY NOTIFICATION MD5 KEY MISMATCH' . "\n" . $resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);

				return false;
			}
		}

		$this->writeTransaction($submit_key, $resp, 'SUCCESS', 1);

		return $paid;
	}

	/**
	 * returns state uri object (notify, cancel, etc...)
	 *
	 * @param   string  $state       the state for the url
	 * @param   string  $submit_key  submit key
	 *
	 * @return string
	 */
	protected function getUri($state, $submit_key)
	{
		$uri = parent::getUri($state, $submit_key);

		switch ($state)
		{
			case 'cancel':
				$uri->setVar('accept', '0');
				break;
			case 'notify':
				$uri->setVar('accept', '1');
				break;
			case 'decline':
				$uri->setVar('task', 'payment.notify');
				$uri->setVar('accept', '0');
				break;
		}

		return $uri;
	}

	/**
	 * get price, checking for extra fee
	 *
	 * @param   object  $details  details
	 *
	 * @return float
	 */
	private function getPrice($details)
	{
		if ((float) $this->params->get('extrafee'))
		{
			$extraPercentage = (float) $this->params->get('extrafee');
			$price = $details->price * (1 + $extraPercentage / 100);

			// Trim to precision
			$price = round($price, RHelperCurrency::getPrecision($details->currency));
		}
		else
		{
			$price = $details->price;
		}

		return $price;
	}
}
