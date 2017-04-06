<?php
/**
 * @package     Redform
 * @subpackage  Payment.epay
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
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
	 * @param   RdfEntityPaymentrequest  $paymentRequest   payment request to credit
	 * @param   RdfEntityPayment         $previousPayment  a previous payment for same submitter
	 *
	 * @return boolean
	 *
	 * @since __deploy_version__
	 */
	public function onRedformCreditPaymentRequest(RdfEntityPaymentrequest $paymentRequest, RdfEntityPayment $previousPayment)
	{
		if (!$previousPayment->gateway == $this->gateway)
		{
			return true;
		}

		$helper = new PaymentEpayCredit($paymentRequest, $previousPayment, $this->params);

		return $helper->process();
	}
}
