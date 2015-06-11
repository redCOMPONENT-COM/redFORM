<?php
/**
 * @package     Redform.Libraries
 * @subpackage  payment
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');

/**
 * Payment helper abstract class
 *
 * @package     Redform.Libraries
 * @subpackage  payment
 * @since       2.5
 */
abstract class RdfPaymentHelper extends JObject
{
	/**
	 * plugin params
	 * @var JRegistry
	 */
	protected $params = null;

	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway;

	/**
	 * Processing notification redirect url
	 * @var string
	 */
	protected $processing_url;

	/**
	 * Cancelled notification redirect url
	 * @var string
	 */
	protected $cancel_url;

	/**
	 * Notify redirect url
	 * @var string
	 */
	protected $notify_url;

	/**
	 * Details of cart being processed
	 * @var RdfCorePaymentCart
	 */
	protected $details;

	/**
	 * Cart being processed
	 * @var RdfCorePaymentCart
	 */
	protected $cart;

	/**
	 * Class contructor
	 *
	 * @param   array  $params  plugin params
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * Display or redirect to the payment page for the gateway
	 *
	 * @param   object  $request     payment request object
	 * @param   string  $return_url  return url for redirection
	 * @param   string  $cancel_url  cancel url for redirection
	 *
	 * @return true on success
	 */
	abstract public function process($request, $return_url = null, $cancel_url = null);

	/**
	 * handle the reception of notification from gateway
	 *
	 * @return bool paid status
	 */
	abstract public function notify();

	/**
	 * returns details about the reference
	 *
	 * @param   string  $cartReference  cart reference
	 *
	 * @return object
	 *
	 * @deprecated use getDetails()
	 */
	protected function _getSubmission($cartReference)
	{
		return $this->getDetails($cartReference);
	}

	/**
	 * returns details about the reference
	 *
	 * @param   string  $cartReference  cart reference
	 *
	 * @return RdfCorePaymentCart
	 */
	protected function getDetails($cartReference)
	{
		if (!$this->details)
		{
			$this->details = $this->getCart($cartReference);
		}

		return $this->details;
	}

	/**
	 * Get cart from reference
	 *
	 * @param   string  $reference  cart reference
	 *
	 * @return RdfCorePaymentCart
	 *
	 * @throws Exception
	 */
	protected function getCart($reference)
	{
		if (!$this->cart || $this->cart->reference != $reference)
		{
			$cart = new RdfCorePaymentCart;
			$cart->loadByReference($reference);

			if (!$cart->id)
			{
				throw new Exception('cart not found');
			}

			$this->cart = $cart;
		}

		return $this->cart;
	}

	/**
	 * write transaction to db
	 *
	 * @param   string  $reference  cart reference
	 * @param   string  $data       data from gateway
	 * @param   string  $status     status (paid, cancelled, ...)
	 * @param   int     $paid       1 for paid
	 *
	 * @return void
	 */
	protected function writeTransaction($reference, $data, $status, $paid)
	{
		$cart = $this->getCart($reference);

		$table = RTable::getAdminInstance('Payment');
		$table->date = JFactory::getDate()->toSql();
		$table->data = $data;
		$table->cart_id = $cart->id;
		$table->status = $status;
		$table->gateway = $this->gateway;
		$table->paid = $paid;

		$table->store();

		// Trigger event for custom handling
		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onPaymentAfterSave', array('com_redform.payment.helper', $table, true));
	}

	/**
	 * returns state url (notify, cancel, etc...)
	 *
	 * @param   string  $state      the state for the url
	 * @param   string  $reference  cart reference
	 *
	 * @return string
	 */
	protected function getUrl($state, $reference)
	{
		$uri = $this->getUri($state, $reference);

		return $uri->toString();
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
		$app = JFactory::getApplication();
		$lang = $app->input->get('lang');

		$uri = JURI::getInstance(JURI::root());
		$uri->setVar('option', 'com_redform');
		$uri->setVar('gw', $this->gateway);
		$uri->setVar('reference', $reference);

		if (JLanguageMultilang::isEnabled() && $lang)
		{
			$uri->setVar('lang', $lang);
		}

		switch ($state)
		{
			case 'processing':
				$uri->setVar('task', 'payment.processing');
				break;
			case 'cancel':
				$uri->setVar('task', 'payment.cancelled');
				break;
			case 'notify':
				$uri->setVar('task', 'payment.notify');
				break;
		}

		return $uri;
	}

	/**
	 * Check if we can use this plugin for given currency
	 *
	 * @param   string  $currency_code  3 letters iso code
	 *
	 * @return true if plugin supports this currency
	 */
	public function currencyIsAllowed($currency_code)
	{
		$allowed = trim($this->params->get('allowed_currencies'));

		if (!$allowed) // Allow everything
		{
			return true;
		}

		// Otherwise returns only currencies specified in allowed_currencies plugin parameters
		$allowed = explode(',', $allowed);
		$allowed = array_map('trim', $allowed);

		if (!in_array($currency_code, $allowed))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if the currency is supported by the gateway (otherwise might require conversion)
	 *
	 * @param   string  $currency_code  3 letters iso code
	 *
	 * @return true if currency is supported
	 */
	protected function currencyIsSupported($currency_code)
	{
		return true;
	}

	/**
	 * Convert price to another currency
	 *
	 * @param   float   $price         price to convert
	 * @param   string  $currencyFrom  currency to convert from
	 * @param   string  $currencyTo    currency to convert to
	 *
	 * @return float converted price
	 */
	protected function convertPrice($price, $currencyFrom, $currencyTo)
	{
		JPluginHelper::importPlugin('currencyconverter');
		$dispatcher = JDispatcher::getInstance();

		$result = false;
		$dispatcher->trigger('onCurrencyConvert', array($price, $currencyFrom, $currencyTo, &$result));

		return $result;
	}

	/**
	 * get price, checking for extra fee
	 *
	 * @param   object  $details  details
	 *
	 * @return float
	 */
	protected function getPrice($details)
	{
		$basePrice = $details->price + $details->vat;

		if ((float) $this->params->get('extrafee'))
		{
			$extraPercentage = (float) $this->params->get('extrafee');
			$price = $basePrice * (1 + $extraPercentage / 100);

			// Trim to precision
			$price = round($basePrice, RHelperCurrency::getPrecision($details->currency));
		}
		else
		{
			$price = $basePrice;
		}

		return $price;
	}
}
