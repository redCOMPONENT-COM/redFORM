<?php
/**
 * @copyright Copyright (C) 2008-2013 redCOMPONENT.com. All rights reserved.
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

require_once (JPATH_SITE . '/components/com_redform/classes/paymenthelper.class.php');

/**
 * @package  RED.redform
 * @since    2.5
 */
class PaymentCustom extends  RDFPaymenthelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'custom';

	protected $params = null;

	/**
	 * contructor
	 * @param object plgin params
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * sends the payment request associated to sumbit_key to the payment service
	 * @param string $submit_key
	 */
	public function process($request, $return_url = null, $cancel_url = null)
	{
		$text = $this->params->get('instructions');
		if ($return_url) {
			echo '<p>'.JHTML::link($return_url, JText::_('Return')).'</b>';
		}
		echo $text;
	}

  public function writeTransaction($submit_key, $data, $status, $paid)
  {
    $db = & JFactory::getDBO();

    // payment was refused
    $query =  ' INSERT INTO #__rwf_payment (`date`, `data`, `submit_key`, `status`, `gateway`, `paid`) '
				    . ' VALUES (NOW() '
				    . ', '. $db->Quote($data)
				    . ', '. $db->Quote($submit_key)
				    . ', '. $db->Quote($status)
				    . ', '. $db->Quote('custom')
				    . ', '. $db->Quote($paid)
				    . ') ';
    $db->setQuery($query);
    $db->query();
  }

	/**
	 * notify
	 *
	 * @return bool|void
	 */
	public function notify()
	{
		// Not going to happen here...
	}
}
