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

	/**
	 * Check if a date is valid and not null
	 *
	 * @param   string  $date  date string to check
	 *
	 * @return bool
	 */
	public static function isNonNullDate($date)
	{
		if (!$date)
		{
			return false;
		}

		if ($date == '0000-00-00 00:00:00'
			|| $date == '0000-00-00 00:00'
			|| $date == '0000-00-00')
		{
			return false;
		}

		if (!strtotime($date))
		{
			return false;
		}

		return true;
	}

	/**
	 * Return mailer
	 *
	 * @return JMail
	 */
	public static function getMailer()
	{
		$mailer = JFactory::getMailer();
		$params = JComponentHelper::getParams('com_redform');

		if ($encoding = $params->get('email_encoding', ''))
		{
			$mailer->Encoding = $encoding;
		}

		if ($params->get('dkim_enable', 0))
		{
			if ($params->get('dkim_selector'))
			{
				$mailer->DKIM_selector = $params->get('dkim_selector');
			}

			if ($params->get('dkim_identity'))
			{
				$mailer->DKIM_identity = $params->get('dkim_identity');
			}

			if ($params->get('dkim_passphrase'))
			{
				$mailer->DKIM_passphrase = $params->get('dkim_passphrase');
			}

			if ($params->get('dkim_domain'))
			{
				$mailer->DKIM_domain = $params->get('dkim_domain');
			}

			if ($params->get('dkim_private'))
			{
				$mailer->DKIM_private = $params->get('dkim_private');
			}
		}

		return $mailer;
	}

	/**
	 * Wrap email content in proper html
	 *
	 * @param   string  $body     content of body tag
	 * @param   string  $subject  subject of the email
	 *
	 * @return string
	 */
	public static function wrapMailHtmlBody($body, $subject)
	{
		return RdfHelperLayout::render('email.bodywrapper',
			array('body' => $body, 'subject' => $subject),
			'',
			array('client' => 0, 'component' => 'com_redform')
		);
	}
}
