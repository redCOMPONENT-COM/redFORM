<?php
/**
 * @package     Redform.plugins
 * @subpackage  payment
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Ideal payment helper
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class PaymentIridium extends RdfPaymentHelper
{
	protected $gateway = 'iridium';

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
		if (!$this->params->get('merchantid'))
		{
			echo JText::_('PLG_REDFORM_IRIDIUM_MISSING_MERCHANTID');

			return false;
		}

		if (!$this->params->get('password'))
		{
			echo JText::_('PLG_REDFORM_IRIDIUM_MISSING_PASSWORD');

			return false;
		}

		$document = JFactory::getDocument();

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;
		$currency = $details->currency;

		$date = JFactory::getDate();

		$req_params = array(
			'MerchantID' => $this->params->get('merchantid'),
			'Amount' => round($details->price * 100),
			'CurrencyCode' => RHelperCurrency::getIsoNumber($currency),
			'EchoAVSCheckResult'  => 'true',
			'EchoCV2CheckResult'  => 'true',
			'EchoThreeDSecureAuthenticationCheckResult'  => 'true',
			'EchoCardType'  => 'true',
			'OrderID' => $request->uniqueid,
			'TransactionType' => "SALE",
			'TransactionDateTime' => $date->format('Y-m-d H:i:s O'),
			'CallbackURL' => $this->getUrl('notify', $submit_key),
			'EmailAddressEditable' => 'true',
			'PhoneNumberEditable' => 'true',
			'CV2Mandatory' => 'true',
			'Address1Mandatory' => 'true',
			'CityMandatory' => 'true',
			'PostCodeMandatory' => 'true',
			'StateMandatory' => 'false',
			'CountryMandatory' => 'true',
			'ResultDeliveryMethod' => 'POST',
		);

		$hashdigest = array(
			'PreSharedKey'  => $this->params->get('presharedkey'),
			'MerchantID'    => $req_params['MerchantID'],
			'Password'      => $this->params->get('password'),
			'Amount'        => $req_params['Amount'],
			'CurrencyCode'  => $req_params['CurrencyCode'],
			'EchoAVSCheckResult'  => $req_params['EchoAVSCheckResult'],
			'EchoCV2CheckResult'  => $req_params['EchoCV2CheckResult'],
			'EchoThreeDSecureAuthenticationCheckResult'  => $req_params['EchoThreeDSecureAuthenticationCheckResult'],
			'EchoCardType'  => $req_params['EchoCardType'],
/* 			'AVSOverridePolicy'  => '',
			'CV2OverridePolicy'  => '',
			'ThreeDSecureOverridePolicy'  => '',*/
			'OrderID'       => $req_params['OrderID'],
			'TransactionType'     => $req_params['TransactionType'],
			'TransactionDateTime' => $req_params['TransactionDateTime'],
			'CallbackURL'       => $req_params['CallbackURL'],
			'OrderDescription'  => '',
			'CustomerName'  => '',
			'Address1'  => '',
			'Address2'  => '',
			'Address3'  => '',
			'Address4'  => '',
			'City'  => '',
			'State'  => '',
			'PostCode'  => '',
			'CountryCode'  => '',
// 			'EmailAddress'  => '',
// 			'PhoneNumber'  => '',
			'EmailAddressEditable' => $req_params['EmailAddressEditable'],
			'PhoneNumberEditable' => $req_params['PhoneNumberEditable'],
			'CV2Mandatory' => $req_params['CV2Mandatory'],
			'Address1Mandatory' => $req_params['Address1Mandatory'],
			'CityMandatory' => $req_params['CityMandatory'],
			'PostCodeMandatory' => $req_params['PostCodeMandatory'],
			'StateMandatory' => $req_params['StateMandatory'],
			'CountryMandatory' => $req_params['CountryMandatory'],
			'ResultDeliveryMethod'  => $req_params['ResultDeliveryMethod'],
		);

		$hashstring = array();

		foreach ($hashdigest as $key => $val)
		{
			$hashstring[] = $key . '=' . $val;
		}

		$hashstring = implode('&', $hashstring);

		if ($this->params->get('hashmethod', 'sha1') == 'md5')
		{
			$req_params['HashDigest'] = md5($hashstring);
		}
		else
		{
			$req_params['HashDigest'] = sha1($hashstring);
		}
		?>
		<h3><?php echo JText::_('PLG_REDFORM_IRIDIUM_FORM_TITLE'); ?></h3>
		<form action="https://mms.iridiumcorp.net/Pages/publicpages/paymentform.aspx" method="post">
		<p><?php echo $request->title; ?></p>
		<?php foreach ($req_params as $key => $val): ?>
		<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>" />
		<?php endforeach; ?>
		<input type="submit" value="<?php echo JText::_('PLG_REDFORM_IRIDIUM_FORM_OPEN_PAYMENT_WINDOW'); ?>" />
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
		RdfHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_IRIDIUM_NOTIFICATION_RECEIVED', $submit_key));

		// It was successful, get the details
		$resp = array();
		$resp[] = 'tid:' . JRequest::getVar('CrossReference');
		$resp[] = 'orderid:' . JRequest::getVar('OrderID');
		$resp[] = 'amount:' . JRequest::getVar('Amount');
		$resp[] = 'cur:' . RHelperCurrency::getIsoCode(JRequest::getVar('CurrencyCode'));
		$resp[] = 'date:' . JRequest::getVar('TransactionDateTime');
		$resp = implode("\n  ", $resp);

		// Calculate hashdigest
		$hash_vars = array(
			'PreSharedKey' => $this->params->get('presharedkey'),
			'MerchantID' => $_REQUEST['MerchantID'],
			'Password' => $this->params->get('password'),
			'StatusCode' => $_REQUEST['StatusCode'],
			'Message' => $_REQUEST['Message'],
			'PreviousStatusCode' => $_REQUEST['PreviousStatusCode'],
			'PreviousMessage' => $_REQUEST['PreviousMessage'],
			'CrossReference' => $_REQUEST['CrossReference'],
			'AddressNumericCheckResult' => $_REQUEST['AddressNumericCheckResult'],
			'PostCodeCheckResult' => $_REQUEST['PostCodeCheckResult'],
			'CV2CheckResult' => $_REQUEST['CV2CheckResult'],
			'ThreeDSecureCheckResult' => $_REQUEST['ThreeDSecureCheckResult'],
			'CardType' => $_REQUEST['CardType'],
			'CardClass' => $_REQUEST['CardClass'],
			'CardIssuer' => $_REQUEST['CardIssuer'],
			'CardIssuerCountryCode' => $_REQUEST['CardIssuerCountryCode'],
			'Amount' => $_REQUEST['Amount'],
			'CurrencyCode' => $_REQUEST['CurrencyCode'],
			'OrderID' => $_REQUEST['OrderID'],
			'TransactionType' => $_REQUEST['TransactionType'],
			'TransactionDateTime' => $_REQUEST['TransactionDateTime'],
			'OrderDescription' => $_REQUEST['OrderDescription'],
			'CustomerName' => $_REQUEST['CustomerName'],
			'Address1' => $_REQUEST['Address1'],
			'Address2' => $_REQUEST['Address2'],
			'Address3' => $_REQUEST['Address3'],
			'Address4' => $_REQUEST['Address4'],
			'City' => $_REQUEST['City'],
			'State' => $_REQUEST['State'],
			'PostCode' => $_REQUEST['PostCode'],
			'CountryCode' => $_REQUEST['CountryCode'],
		);

		if (isset($_REQUEST['EmailAddress']))
		{
			$hash_vars['EmailAddress'] = JRequest::getVar('EmailAddress');
		}

		if (isset($_REQUEST['PhoneNumber']))
		{
			$hash_vars['PhoneNumber'] = JRequest::getVar('PhoneNumber');
		}

		$hashstring = array();

		foreach ($hash_vars as $key => $val)
		{
			$hashstring[] = $key . '=' . $val;
		}

		$hashstring = implode('&', $hashstring);

		if ($this->params->get('hashmethod', 'sha1') == 'md5')
		{
			$HashDigest = md5($hashstring);
		}
		else
		{
			$HashDigest = sha1($hashstring);
		}

		try
		{
			if (strcmp($HashDigest, JRequest::getVar('HashDigest')))
			{
				$error = JText::sprintf('PLG_REDFORM_IRIDIUM_HASHDIGEST_MISMATCH', $submit_key);
				throw new RedformPaymentException($error);
			}

			// Hash match, record result
			if (!JRequest::getVar('StatusCode') == 0)
			{
				// Payment was refused
				switch (JRequest::getVar('StatusCode'))
				{
					case 4:
						$reason = JText::_('PLG_REDFORM_IRIDIUM_PAYMENT_REFUSED_CARD_REFERRED');
						break;
					case 5:
						$reason = JText::_('PLG_REDFORM_IRIDIUM_PAYMENT_REFUSED_CARD_DECLINED');
						break;
					case 20:
						$reason = JText::_('PLG_REDFORM_IRIDIUM_PAYMENT_REFUSED_DUPLICATE_TRANSACTION');
						break;
					case 30:
						$reason = JText::_('PLG_REDFORM_IRIDIUM_PAYMENT_REFUSED_EXCEPTION');
						break;

					default:
						$reason = JText::sprintf('PLG_REDFORM_IRIDIUM_PAYMENT_REFUSED_UNKOWN_CODE', JRequest::getVar('StatusCode'));
						break;
				}

				$error = JText::sprintf('PLG_REDFORM_IRIDIUM_PAYMENT_REFUSED_KEY_S_REASON_S_MESSAGE_M',
					$submit_key, $reason, JRequest::getVar('Message')
				);
				throw new RedformPaymentException($error);
			}

			$details = $this->_getSubmission($submit_key);

			$currency = $details->currency;

			if (strcasecmp($currency, RHelperCurrency::getIsoCode(JRequest::getVar('CurrencyCode'))))
			{
				$error = JText::sprintf('PLG_REDFORM_IRIDIUM_CURRENCY_MISMATCH_EXPECTED_S_RECEIVED_S',
					$submit_key, $currency, RHelperCurrency::getIsoCode(JRequest::getVar('CurrencyCode'))
				);
				throw new RedformPaymentException($error);
			}

			if (round($details->price * 100) != JRequest::getVar('Amount'))
			{
				$error = JText::sprintf('PLG_REDFORM_IRIDIUM_PRICE_MISMATCH_EXPECTED_S_RECEIVED_S',
					$submit_key, $details->price * 100, JRequest::getVar('Amount')
				);
				throw new RedformPaymentException($error);
			}
			else
			{
				$paid = 1;
			}

			$this->writeTransaction($submit_key, $resp, 'SUCCESS', 1);
		}
		catch (RedformPaymentException $e)
		{
			RdfHelperLog::simpleLog($e->getMessage());
			$this->writeTransaction($submit_key, $e->getMessage() . $resp, 'FAIL', 0);

			return false;
		}

		return $paid;
	}
}
