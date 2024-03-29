<?php
/**
 * @package     Redform
 * @subpackage  Payment.epay
 * @copyright   Copyright (C) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU/GPL, see LICENSE
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
class PlgRedform_PaymentEpay extends RdfPaymentPlugin
{
	protected $gateway = 'epay';

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
		if (!($previousPayment->gateway == $this->gateway && $this->params->get('auto_credit', 0)))
		{
			return true;
		}

		$helper = new PaymentEpayCredit($paymentRequests, $previousPayment, $this->params);

		return $helper->process();
	}
}
