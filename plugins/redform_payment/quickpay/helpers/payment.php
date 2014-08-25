<?php
/**
 * @package     Redform
 * @subpackage  Payment.Quickpay
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Quickpay helper
 *
 * @package     Redform
 * @subpackage  Payment.Quickpay
 * @since       2.5
 */
class PaymentQuickpay extends  RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'quickpay';

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
		$document = JFactory::getDocument();

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;
		$currency = $details->currency;

		$req_params = array(
			'protocol' => 4,
			'msgtype' => "authorize",
			'merchant' => $this->params->get('quickpayid'),
			'language' => "en",
			'ordernumber' => $request->uniqueid,
			'amount' => round($details->price * 100),
			'currency' => $currency,
			'continueurl' => $this->getUrl('processing', $submit_key),
			'cancelurl' => $this->getUrl('paymentcancelled', $submit_key),
			'callbackurl' => $this->getUrl('notify', $submit_key),
			'autocapture' => 0,
			'cardtypelock' => $this->_getAllowedCard(),
			'description' => 0,
			'testmode' => $this->params->get('testmode', 0),
			'splitpayment' => 0,
		);
		$md5 = md5(implode("", $req_params) . $this->params->get('md5secret'));

		if (!$req_params['merchant'])
		{
			echo JText::_('PLG_REDFORM_QUICKPAY_MISSING_QUICKPAYID');

			return false;
		}

		if (!$this->params->get('md5secret'))
		{
			echo JText::_('PLG_REDFORM_QUICKPAY_MISSING_MD5SECRET');

			return false;
		}

		?>
		<h3><?php echo JText::_('Quickpay Payment Gateway'); ?></h3>
		<form action="https://secure.quickpay.dk/form/" method="post">
			<p><?php echo $request->title; ?></p>
			<?php foreach ($req_params as $key => $val): ?>
				<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>"/>
			<?php endforeach; ?>
			<input type="hidden" name="md5check" value="<?php echo $md5; ?>"/>
			<input type="submit" value="Open Quickpay payment window"/>
		</form>
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
		RdfHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_QUICKPAY_NOTIFICATION_RECEIVED', $submit_key));

		// It was successull, get the details
		$resp = array();
		$resp[] = 'tid:' . JRequest::getVar('transaction');
		$resp[] = 'orderid:' . JRequest::getVar('ordernumber');
		$resp[] = 'amount:' . JRequest::getVar('amount');
		$resp[] = 'cur:' . JRequest::getVar('currency');
		$resp[] = 'date:' . substr(JRequest::getVar('time'), 0, 6);
		$resp[] = 'time:' . substr(JRequest::getVar('time'), 6);
		$resp = implode("\n  ", $resp);

		if ($this->params->get('md5secret'))
		{
			$req_params = array(
				JRequest::getVar('msgtype'),
				JRequest::getVar('ordernumber'),
				JRequest::getVar('amount'),
				JRequest::getVar('currency'),
				JRequest::getVar('time'),
				JRequest::getVar('state'),
				JRequest::getVar('qpstat'),
				JRequest::getVar('qpstatmsg'),
				JRequest::getVar('chstat'),
				JRequest::getVar('chstatmsg'),
				JRequest::getVar('merchant'),
				JRequest::getVar('merchantemail'),
				JRequest::getVar('transaction'),
				JRequest::getVar('cardtype'),
				JRequest::getVar('cardnumber'),
				//     	  JRequest::getVar('cardexpire'),
				JRequest::getVar('splitpayment'),
				JRequest::getVar('fraudprobability'),
				JRequest::getVar('fraudremarks'),
				JRequest::getVar('fraudreport'),
				JRequest::getVar('fee')
			);
			$receivedkey = JRequest::getVar('md5check');
			$calc = md5(implode('', $req_params) . $this->params->get('md5secret'));

			if (strcmp($receivedkey, $calc))
			{
				$error = JText::sprintf('PLG_REDFORM_QUICKPAY_MD5_KEY_MISMATCH', $submit_key);
				RdfHelperLog::simpleLog($error);
				$this->writeTransaction($submit_key, $error . $resp, 'FAIL', 0);

				return false;
			}
		}

		if (!JRequest::getVar('qpstat') === '000')
		{
			// Payment was refused
			$error = JText::sprintf('PLG_REDFORM_QUICKPAY_PAYMENT_REFUSED', $submit_key);
			RdfHelperLog::simpleLog($error);
			$this->writeTransaction($submit_key, JRequest::getVar('qpstat') . ': ' . JRequest::getVar('qpstatmsg'), 'FAIL', 0);

			return 0;
		}

		if (JRequest::getVar('state') == 0)
		{
			// Payment was refused
			RdfHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_INITIAL', $submit_key));
			$this->writeTransaction(
				$submit_key,
				JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_INITIAL', $submit_key) . "\n  " . $resp,
				'FAIL',
				0
			);

			return 0;
		}
		elseif (JRequest::getVar('state') == 5)
		{
			// Payment was refused
			RdfHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_CANCELLED', $submit_key));
			$this->writeTransaction(
				$submit_key,
				JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_CANCELLED', $submit_key) . "\n  " . $resp,
				'FAIL',
				0
			);

			return 0;
		}

		$details = $this->_getSubmission($submit_key);

		$currency = $details->currency;

		if (strcasecmp($currency, JRequest::getVar('currency')))
		{
			$error = JText::sprintf('PLG_REDFORM_QUICKPAY_CURRENCY_MISMATCH', $submit_key);
			RdfHelperLog::simpleLog($error);
			$this->writeTransaction($submit_key, $error . $resp, 'FAIL', 0);

			return false;
		}

		if (round($details->price * 100) != JRequest::getVar('amount'))
		{
			$error = JText::sprintf('PLG_REDFORM_QUICKPAY_PRICE_MISMATCH', $submit_key);
			RdfHelperLog::simpleLog($error);
			$this->writeTransaction($submit_key, $error . $resp, 'FAIL', 0);

			return false;
		}
		else
		{
			$paid = 1;
		}

		$this->writeTransaction($submit_key, $resp, 'SUCCESS', 1);

		return $paid;
	}

	/**
	 * returns allowed card types
	 *
	 * @return string
	 */
	private function _getAllowedCard()
	{
		$allowed = array();
		$methods = array(
			'american-express',
			'american-express-dk',
			'dankort',
			'danske-dk',
			'diners',
			'diners-dk',
			'edankort',
			'fbg1886',
			'jcb',
			'mastercard',
			'mastercard-dk',
			'mastercard-debet-dk',
			'nordea-dk',
			'visa',
			'visa-dk',
			'visa-electron',
			'visa-electron-dk',
			'paypal',
			'3d-jcb',
			'3d-maestro',
			'3d-maestro-dk',
			'3d-mastercard',
			'3d-mastercard-dk',
			'3d-mastercard-debet-dk',
			'3d-visa',
			'3d-visa-dk',
			'3d-visa-electron',
			'3d-visa-electron-dk',
		);

		foreach ($methods as $type)
		{
			if ($this->params->get($type))
			{
				$allowed[] = $type;
			}
		}

		return implode(",", $allowed);
	}
}
