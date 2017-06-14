<?php
/**
 * @package     Redform.plugins
 * @subpackage  payment
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Custom payment plugin
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class plgRedform_PaymentCustom extends RdfPaymentPlugin
{
	protected $gateway = 'custom';

	public function onBeforeSendPaymentNotificationSubmitter(RdfHelperMailer &$mailer, RdfEntityCart $cart, &$send)
	{
		if ($cart->getPayment()->gateway != $this->gateway)
		{
			return true;
		}

		if ($this->params->get('payment_status') == 'pending' || $this->params->get('disable_submitter_payment_notification'))
		{
			$send = false;
		}

		return true;
	}
}
