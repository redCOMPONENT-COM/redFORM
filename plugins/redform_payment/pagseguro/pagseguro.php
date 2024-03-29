<?php
/**
 * @package     Redform
 * @subpackage  Payment.pagseguro
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Pagseguro payment plugin
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class plgRedform_PaymentPagseguro extends RdfPaymentPlugin
{
	protected $gateway = 'pagseguro';
}
