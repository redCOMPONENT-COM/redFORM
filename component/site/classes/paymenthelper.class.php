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
	 * contructor
	 * @param object plgin params
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
	 * returns details about the submissoin
	 * @param string $submit_key
	 * @return Ambigous <mixed, NULL>
	 */
	protected function _getSubmission($submit_key)
	{
		// get price and currency
		$db  = JFactory::getDBO();
	
		$query = ' SELECT f.currency, SUM(s.price) AS price, s.id AS sid '
				. ' FROM #__rwf_submitters AS s '
						. ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
								. ' WHERE s.submit_key = '. $db->Quote($submit_key)
								. ' GROUP BY s.submit_key'
										;
		$db->setQuery($query);
		$res = $db->loadObject();
		return $res;
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
	
	protected function getUrl($state, $submit_key)
	{
		$uri = JURI::getInstance(JURI::root());
		$uri->setVar('option', 'com_redform');
		$uri->setVar('controller', 'payment');
		$uri->setVar('gw', $this->gateway);
		$uri->setVar('key', $submit_key);
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
