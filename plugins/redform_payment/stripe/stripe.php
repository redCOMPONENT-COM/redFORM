<?php
/**
 * @package     Redform
 * @subpackage  Payment.stripe
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Stripe payment plugin
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class PlgRedform_PaymentStripe extends RdfPaymentPlugin
{
	protected $gateway = 'stripe';
}
