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

		$details = $this->getDetails($request->key);
		$reference = $request->key;

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
			'callbackurl' => $this->getUrl('notify', $reference),
			'accepturl' => $this->getUrl('notify', $reference),
			'cancelurl' => $this->getUrl('cancel', $reference),
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
				window.location = "<?php echo $this->getUrl('cancel', $reference); ?>";
			});
			paymentwindow.on('declined', function(){
				alert("<?php echo JText::_('PLG_REDFORM_PAYMENT_EPAY_PAYMENT_WAS_DECLINED'); ?>");
				window.location = "<?php echo $this->getUrl('decline', $reference); ?>";
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
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$paid = 0;

		$reference = $app->input->get('reference');
		$app->input->set('reference', $reference);
		RdfHelperLog::simpleLog('EPAY NOTIFICATION RECEIVED' . ' for ' . $reference);

		if ($app->input->get('accept', 0) == 0)
		{
			// Payment was refused
			RdfHelperLog::simpleLog('EPAY NOTIFICATION PAYMENT REFUSED' . ' for ' . $reference);

			$this->writeTransaction(
				$reference, $app->input->get('error') . ': ' . $app->input->get('errortext'),
				$this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0
			);

			return 0;
		}

		// It was successull, get the details
		$resp = array();
		$resp[] = 'tid:' . $app->input->get('txnid');
		$resp[] = 'orderid:' . $app->input->get('orderid');
		$resp[] = 'amount:' . $app->input->get('amount');
		$resp[] = 'currency:' . $app->input->get('currency');
		$resp[] = 'date:' . $app->input->get('date');
		$resp[] = 'time:' . $app->input->get('time');
		$resp = implode("\n", $resp);

		$details = $this->getDetails($reference);
		$price = $this->getPrice($details);

		$currency = RHelperCurrency::getIsoNumber($details->currency);

		if (round($price * 100) != $app->input->get('amount'))
		{
			RdfHelperLog::simpleLog('EPAY NOTIFICATION PRICE MISMATCH' . ' for ' . $reference);
			$this->writeTransaction($reference, 'EPAY NOTIFICATION PRICE MISMATCH' . "\n" . $resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);

			return false;
		}
		else
		{
			$paid = 1;
		}

		if ($currency != $app->input->get('currency'))
		{
			RdfHelperLog::simpleLog('EPAY NOTIFICATION CURRENCY MISMATCH' . ' for ' . $reference);
			$this->writeTransaction($reference, 'EPAY NOTIFICATION CURRENCY MISMATCH' . "\n" . $resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);

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
				RdfHelperLog::simpleLog('EPAY NOTIFICATION MD5 KEY MISMATCH' . ' for ' . $reference);
				$this->writeTransaction($reference, 'EPAY NOTIFICATION MD5 KEY MISMATCH' . "\n" . $resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);

				return false;
			}
		}

		$this->writeTransaction($reference, $resp, 'SUCCESS', 1);

		return $paid;
	}

	/**
	 * returns state uri object (notify, cancel, etc...)
	 *
	 * @param   string  $state      the state for the url
	 * @param   string  $reference  cart reference
	 *
	 * @return string
	 */
	protected function getUri($state, $reference)
	{
		$uri = parent::getUri($state, $reference);

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
}
