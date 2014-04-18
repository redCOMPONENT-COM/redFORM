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
 * Class RdfHelper
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfHelper
{
	/**
	 * Return array of emails from comma or semicolon separated emails
	 *
	 * @param   string  $string    string to parse
	 * @param   bool    $validate  only return valid emails
	 *
	 * @return array
	 */
	public static function extractEmails($string, $validate = true)
	{
		if (strstr($string, ';'))
		{
			$addresses = explode(";", $string);
		}
		else
		{
			$addresses = explode(",", $string);
		}

		$addresses = array_map('trim', $addresses);

		if (!$validate || !$addresses)
		{
			return $addresses;
		}

		// Make sure values are valid email
		$result = array();

		foreach ($addresses as $a)
		{
			if (JMailHelper::isEmailAddress($a))
			{
				$result[] = $a;
			}
		}

		return $result;
	}
}
