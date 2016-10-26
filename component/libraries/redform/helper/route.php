<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfHelperRoute
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfHelperRoute
{
	/**
	 * Route to payment
	 *
	 * @param   string  $submit_key  submit keys
	 *
	 * @return string
	 */
	public static function getPaymentRoute($submit_key)
	{
		$parts = array( "option" => "com_redform",
			"task"   => 'payment.select',
			"key"   => $submit_key,
		);

		return self::buildUrl($parts);
	}

	/**
	 * Route to payment
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return string
	 */
	public static function getPaymentCartRoute($cartId)
	{
		$parts = array( "option" => "com_redform",
			"task"   => 'payment.select',
			"cart_id"   => $cartId,
		);

		return self::buildUrl($parts);
	}

	/**
	 * Route to payment process
	 *
	 * @param   string  $cartReference  submit keys
	 * @param   string  $gateway        gateway name
	 *
	 * @return string
	 */
	public static function getPaymentProcessRoute($cartReference, $gateway)
	{
		$parts = array( "option" => "com_redform",
			"task"   => 'payment.process',
			"gw"   => $gateway,
			"reference"   => $cartReference,
		);

		return self::buildUrl($parts);
	}

	/**
	 * Returns the route from parts
	 *
	 * @param   array  $parts  segments of the route
	 *
	 * @return string
	 */
	protected static function buildUrl($parts)
	{
		return 'index.php?' . JURI::buildQuery($parts);
	}
}
