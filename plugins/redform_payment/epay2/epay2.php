<?php
/**
 * @package     Redform
 * @subpackage  Payment.epay
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

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

require_once __DIR__ . '/../epay/helpers/credit.php';

/**
 * Epay payment plugin
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class PlgRedform_PaymentEpay2 extends RdfPaymentPlugin
{
	protected $gateway = 'epay2';

	/**
	 * Callback handler to credit a payment
	 *
	 * @param   RdfEntityPaymentrequest[]  $paymentRequests  payment request to credit
	 * @param   RdfEntityPayment           $previousPayment  a previous payment for same submitter
	 *
	 * @return boolean
	 *
	 * @since 3.3.18
	 */
	public function onRedformCreditPaymentRequests($paymentRequests, RdfEntityPayment $previousPayment)
	{
		if (!$previousPayment->gateway == $this->gateway && $this->params->get('auto_credit', 0))
		{
			return true;
		}

		$helper = new PaymentEpayCredit($paymentRequests, $previousPayment, $this->params);

		return $helper->process();
	}
}
