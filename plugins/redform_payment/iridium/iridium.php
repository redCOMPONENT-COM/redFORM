<?php
/**
 * @package     Redform
 * @subpackage  Payment.iridium
 * @copyright   Copyright (C) 2008-2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

// Register library prefix
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Iridium payment plugin
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class plgRedform_PaymentIridium extends RdfPaymentPlugin
{
	protected $gateway = 'iridium';
}
