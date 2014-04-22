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

/**
 * @package  RED.redform
 * @since    2.5
 */
class PaymentCustom extends  RdfPaymentHelper
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
		$text = array($this->params->get('instructions'));

		if ($return_url)
		{
			$text[] = '<p>' . JHTML::link($return_url, JText::_('Return')) . '</b>';
		}

		$text[] = '<form class="custom-payment" method="post" action="' . $this->getUrl('notify', $request->key) . '">';
		$text[] = '<button type="submit">' . $this->params->get('confirmButtonLabel', 'Confirm') . '</button>';
		$text[] = '</form>';

		echo implode("\n", $text);

		return true;
	}

	/**
	 * notify
	 *
	 * @return bool|void
	 */
	public function notify()
	{
		$app = JFactory::getApplication();
		$submit_key = $app->input->get('key');

		$paid = $this->params->get('payment_status', 'pending') == 'paid';
		$data = 'tid:' . uniqid();

		if ($paid)
		{
			$this->writeTransaction($submit_key, $data, 'Paid', 1);
		}
		else
		{
			$this->writeTransaction($submit_key, $data, 'Pending', 0);
		}

		return $paid;
	}
}
