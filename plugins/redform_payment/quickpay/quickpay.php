<?php
/**
 * @package     Redform
 * @subpackage  Payment.custom
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

require_once 'vendor/autoload.php';

/**
 * Quickpay payment plugin
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class plgRedform_PaymentQuickpay extends RdfPaymentPlugin
{
	protected $gateway = 'quickpay';
}
