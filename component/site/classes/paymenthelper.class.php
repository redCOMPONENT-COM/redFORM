<?php
/**
 * @package     Redform
 * @subpackage  Payment
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

require_once JPATH_SITE . '/components/com_redform/helpers/currency.php';

/**
 * Payment helper abstract class
 *
 * @package     Redform
 * @subpackage  Payment
 * @since       2.5
 */
abstract class RDFPaymenthelper extends JObject
{
	/**
	 * plugin params
	 * @var JRegistry
	 */
	protected $params = null;

	/**
	 * name of the gateway for logs
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
	 * Submission being processed
	 * @var object
	 */
	protected $submission;

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
	 * returns details about the submission
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return object
	 */
	protected function _getSubmission($submit_key)
	{
		if (!$this->submission)
		{
			// Get price and currency
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select('f.currency, SUM(s.price) AS price, s.id AS sid');
			$query->from('#__rwf_submitters AS s');
			$query->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id');
			$query->where('s.submit_key = ' . $db->Quote($submit_key));
			$query->group('s.submit_key');

			$db->setQuery($query);
			$this->submission = $db->loadObject();
		}

		return $this->submission;
	}

	/**
	 * write transaction to db
	 *
	 * @param   string  $submit_key  submit key
	 * @param   string  $data        data from gateway
	 * @param   string  $status      status (paid, cancelled, ...)
	 * @param   int     $paid        1 for paid
	 *
	 * @return void
	 */
	protected function writeTransaction($submit_key, $data, $status, $paid)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->insert('#__rwf_payment');
		$query->columns(array('date', 'data', 'submit_key', 'status', 'gateway', 'paid'));
		$query->values('NOW(), ' . $db->Quote($data)
						. ', ' . $db->Quote($submit_key)
						. ', ' . $db->Quote($status)
						. ', ' . $db->Quote($this->gateway)
						. ', ' . $db->Quote($paid)
		);

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * returns state url (notify, cancel, etc...)
	 *
	 * @param   string  $state       the state for the url
	 * @param   string  $submit_key  submit key
	 *
	 * @return string
	 */
	protected function getUrl($state, $submit_key)
	{
		$app = JFactory::getApplication();
		$lang = $app->input->get('lang');

		$uri = JURI::getInstance(JURI::root());
		$uri->setVar('option', 'com_redform');
		$uri->setVar('controller', 'payment');
		$uri->setVar('gw', $this->gateway);
		$uri->setVar('key', $submit_key);

		if (JLanguageMultilang::isEnabled() && $lang)
		{
			$uri->setVar('lang', $lang);
		}

		switch ($state)
		{
			case 'processing':
				$uri->setVar('task', 'processing');
				break;
			case 'cancel':
				$uri->setVar('task', 'cancelled');
				break;
			case 'notify':
				$uri->setVar('task', 'notify');
				break;
		}

		return $uri->toString();
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
}

class PaymentException extends Exception {}
