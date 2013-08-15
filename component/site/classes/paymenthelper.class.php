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

/**
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'helpers'.DS.'currency.php');

/**
 * @package  RED.redevent
 * @since    2.5
 */
abstract class RDFPaymenthelper extends JObject {

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

	protected $processing_url;
	protected $cancel_url;
	protected $notify_url;

	/**
	 * @var object
	 */
	protected $submission;

	/**
	 * contructor
	 * @param object plugin params
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 *
	 * @param object $request payment request object
	 * @param string $return_url
	 * @param string $cancel_url
	 */
	abstract public function process($request, $return_url = null, $cancel_url = null);

	/**
	 * handle the reception of notification
	 * @return bool paid status
	 */
	abstract public function notify();

	/**
	 * returns details about the submission
	 * @param string $submit_key
	 * @return Ambigous <mixed, NULL>
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
	 * @param string $submit_key
	 * @param string $data
	 * @param string $status
	 * @param int $paid
	 */
	protected function writeTransaction($submit_key, $data, $status, $paid)
	{
		$db = & JFactory::getDBO();

		// payment was refused
		$query =  ' INSERT INTO #__rwf_payment (`date`, `data`, `submit_key`, `status`, `gateway`, `paid`) '
				. ' VALUES (NOW() '
						. ', '. $db->Quote($data)
						. ', '. $db->Quote($submit_key)
						. ', '. $db->Quote($status)
						. ', '. $db->Quote($this->gateway)
						. ', '. $db->Quote($paid)
						. ') ';
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
}

class PaymentException extends Exception {}
