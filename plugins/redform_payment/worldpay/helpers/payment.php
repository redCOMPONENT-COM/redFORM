<?php
/**
 * @package     Redform
 * @subpackage  Payment.worldpay
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

/**
 * Worldpay payment helper
 *
 * @package     Redform
 * @subpackage  Payment.worldpay
 * @since       2.0
 */
class PaymentWorldpay extends RdfPaymentHelper
{
	protected $gateway = 'worldpay';

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
		if (!$this->params->get('merchantid'))
		{
			echo JText::_('PLG_REDFORM_WORLDPAY_MISSING_MERCHANTID');

			return false;
		}

		if (!$this->params->get('password'))
		{
			echo JText::_('PLG_REDFORM_WORLDPAY_MISSING_PASSWORD');

			return false;
		}

		$document = JFactory::getDocument();
		$document->addScript(JURI::root() . "plugins/redform_payment/worldpay/js/script.js");

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;
		$currency = $details->currency;

		$date = JFactory::getDate();

		$req_params = array(
			'MerchantID' => $this->params->get('merchantid'),
			'Amount' => round($details->price * 100),
			'CurrencyCode' => RedformHelperLogCurrency::getIsoNumber($currency),
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
			'DateOfBirthEditable' => 'true',
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
			'EmailAddressEditable' => $req_params['EmailAddressEditable'],
			'PhoneNumberEditable' => $req_params['PhoneNumberEditable'],
			'DateOfBirthEditable' => $req_params['DateOfBirthEditable'],
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
		<h3><?php echo JText::_('PLG_REDFORM_WORLDPAY_FORM_TITLE'); ?></h3>
		<form action="https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx" method="post" id="worldpayForm">

			<p><?php echo $request->title; ?></p>

			<?php foreach ($req_params as $key => $val): ?>
				<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>" />
			<?php endforeach; ?>


			<input type="submit" value="<?php echo JText::_('PLG_REDFORM_WORLDPAY_FORM_OPEN_PAYMENT_WINDOW'); ?>" />
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
		$input = JFactory::getApplication()->input;
		$paid = 0;

		$submit_key = $input->get('key');
		$input->set('submit_key', $submit_key);
		RedformHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_WORLDPAY_NOTIFICATION_RECEIVED', $submit_key));

		// It was successull, get the details
		$resp = array();
		$resp[] = 'tid:' . $input->get('CrossReference', '', 'string');
		$resp[] = 'orderid:' . $input->get('OrderID', '', 'string');
		$resp[] = 'amount:' . $input->get('Amount', 0);
		$resp[] = 'currency:' . RedformHelperLogCurrency::getIsoCode($input->get('CurrencyCode', 0));
		$resp[] = 'date:' . $input->get('TransactionDateTime', '', 'string');
		$resp = implode("\n  ", $resp);

		// Calculate hashdigest
		$hash_vars = array(
			'PreSharedKey'  => $this->params->get('presharedkey'),
			'MerchantID'    => $input->get('MerchantID', '', 'string'),
			'Password'      => $this->params->get('password'),
			'StatusCode'    => $input->get('StatusCode', '', 'string'),
			'Message'    => $input->get('Message', '', 'string'),
			'PreviousStatusCode'    => $input->get('PreviousStatusCode', '', 'string'),
			'PreviousMessage'    => $input->get('PreviousMessage', '', 'string'),
			'CrossReference'    => $input->get('CrossReference', '', 'string'),
			'AddressNumericCheckResult'    => $input->get('AddressNumericCheckResult', '', 'string'),
			'PostCodeCheckResult'    => $input->get('PostCodeCheckResult', '', 'string'),
			'CV2CheckResult'    => $input->get('CV2CheckResult', '', 'string'),
			'ThreeDSecureAuthenticationCheckResult'    => $input->get('ThreeDSecureAuthenticationCheckResult', '', 'string'),
			'CardType'    => $input->get('CardType', '', 'string'),
			'CardClass'    => $input->get('CardClass', '', 'string'),
			'CardIssuer'    => $input->get('CardIssuer', '', 'string'),
			'CardIssuerCountryCode'    => $input->get('CardIssuerCountryCode', '', 'string'),
			'Amount'        => $input->get('Amount', '', 'string'),
			'CurrencyCode'        => $input->get('CurrencyCode', '', 'string'),
			'OrderID'        => $input->get('OrderID', '', 'string'),
			'TransactionType'        => $input->get('TransactionType', '', 'string'),
			'TransactionDateTime'        => $input->get('TransactionDateTime', '', 'string'),
			'OrderDescription'        => $input->get('OrderDescription', '', 'string'),
			'CustomerName'        => $input->get('CustomerName', '', 'string'),
			'Address1'        => $input->get('Address1', '', 'string'),
			'Address2'        => $input->get('Address2', '', 'string'),
			'Address3'        => $input->get('Address3', '', 'string'),
			'Address4'        => $input->get('Address4', '', 'string'),
			'City'        => $input->get('City', '', 'string'),
			'State'        => $input->get('State', '', 'string'),
			'PostCode'        => $input->get('PostCode', '', 'string'),
			'CountryCode'        => $input->get('CountryCode', 0),
		);

		if (isset($_REQUEST['EmailAddress']))
		{
			$hash_vars['EmailAddress'] = $input->get('EmailAddress', '', 'string');
		}

		if (isset($_REQUEST['PhoneNumber']))
		{
			$hash_vars['PhoneNumber'] = $input->get('PhoneNumber', '', 'string');
		}

		if (isset($_REQUEST['DateOfBirth']))
		{
			$hash_vars['DateOfBirth'] = $input->get('DateOfBirth', '', 'string');
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
			if (strcmp($HashDigest, $input->get('HashDigest', '', 'string')))
			{
				$error = JText::sprintf('PLG_REDFORM_WORLDPAY_HASHDIGEST_MISMATCH', $submit_key);
				throw new PaymentException($error);
			}

			// Hash match, record result
			switch ($input->getInt('StatusCode', 500))
			{
				// Transaction authorised
				case 0:
					$transauthorised = true;
					break;

				// Card referred (treat as decline)
				case 4:
					$transauthorised = false;
					$reason = JText::_('PLG_REDFORM_WORLDPAY_PAYMENT_REFUSED_CARD_REFERRED');
					break;

				// Transaction declined
				case 5:
					$transauthorised = false;
					$reason = JText::_('PLG_REDFORM_WORLDPAY_PAYMENT_REFUSED_CARD_DECLINED');
					break;

				// Duplicate transaction
				case 20:
					// Need to look at the previous status code to see if the transaction was successful
					if ($input->getInt('PreviousStatusCode', 500) == 0)
					{
						// Transaction authorised
						$transauthorised = true;
					}
					else
					{
						// Transaction not authorised
						$transauthorised = false;
						$reason = JText::_('PLG_REDFORM_WORLDPAY_PAYMENT_REFUSED_DUPLICATE_TRANSACTION');
					}
					break;

				// Error occurred
				case 30:
					$transauthorised = false;
					$reason = JText::_('PLG_REDFORM_WORLDPAY_PAYMENT_REFUSED_EXCEPTION');
					break;

				default:
					$transauthorised = false;
					$reason = JText::sprintf('PLG_REDFORM_WORLDPAY_PAYMENT_REFUSED_UNKOWN_CODE', $input->getInt('StatusCode', 500));
					break;
			}

			if (!$transauthorised)
			{
				$error = JText::sprintf('PLG_REDFORM_WORLDPAY_PAYMENT_REFUSED_KEY_S_REASON_S_MESSAGE_M',
					$submit_key, $reason, $input->get('Message', '', 'string')
				);
				throw new PaymentException($error);
			}

			$details = $this->_getSubmission($submit_key);

			$currency = $details->currency;

			if (strcasecmp($currency, RedformHelperLogCurrency::getIsoCode($input->getInt('CurrencyCode', 0))))
			{
				$error = JText::sprintf('PLG_REDFORM_WORLDPAY_CURRENCY_MISMATCH_EXPECTED_S_RECEIVED_S',
					$submit_key, $currency, RedformHelperLogCurrency::getIsoCode($input->getInt('CurrencyCode', 0))
				);
				throw new PaymentException($error);
			}

			if (round($details->price * 100) != $input->getInt('Amount', 0))
			{
				$error = JText::sprintf('PLG_REDFORM_WORLDPAY_PRICE_MISMATCH_EXPECTED_S_RECEIVED_S',
					$submit_key, $details->price * 100, $input->getInt('Amount', 0)
				);
				throw new PaymentException($error);
			}
			else
			{
				$paid = 1;
			}

			$this->writeTransaction($submit_key, $resp, 'SUCCESS', 1);
		}
		catch (PaymentException $e)
		{
			RedformHelperLog::simpleLog($e->getMessage());
			$this->writeTransaction($submit_key, $e->getMessage() . $resp, 'FAIL', 0);

			return false;
		}

		return $paid;
	}
}
