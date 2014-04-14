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
 * Class RedformHelperRoute
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RDFHelperRoute
{
	public static function getPaymentRoute($submit_key)
	{
		$parts = array( "option" => "com_redform",
			"controller"   => 'payment',
			"task"   => 'select',
			"key"   => $submit_key,
		);

		return self::buildUrl($parts);
	}

	public static function getPaymentProcessRoute($submit_key, $gateway)
	{
		$parts = array( "option" => "com_redform",
			"controller"   => 'payment',
			"task"   => 'process',
			"gw"   => $gateway,
			"key"   => $submit_key,
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
