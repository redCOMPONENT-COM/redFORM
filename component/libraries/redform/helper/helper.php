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
	 * Mailer object
	 *
	 * @var    JMail
	 */
	public static $mailer = null;

	/**
	 * Get global parameters
	 *
	 * @return \Joomla\Registry\Registry|JRegistry
	 */
	public static function getConfig()
	{
		static $config;

		if (empty($config))
		{
			$config = JComponentHelper::getParams('com_redform');

			// See if there are any plugins that wish to alter the configuration (client specific demands !)
			JPluginHelper::importPlugin('redform');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onGetRedformConfig', array(&$config));
		}

		return $config;
	}

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
	 * @return boolean
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
		if (!self::$mailer)
		{
			self::$mailer = self::createMailer();
		}

		$mailer = clone self::$mailer;

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
	 *
	 * @deprecated use RdfHelperMailer::wrapMailHtmlBody()
	 */
	public static function wrapMailHtmlBody($body, $subject)
	{
		return RdfHelperMailer::wrapMailHtmlBody($body, $subject);
	}

	/**
	 * Formats a price according to settings
	 *
	 * @param   float   $price         price
	 * @param   string  $currencyCode  iso3 currency code
	 * @param   string  $format        sprintf format, with 1st argument the currency, 2nd the value
	 *
	 * @return string
	 */
	public static function formatPrice($price, $currencyCode, $format = "%1s %2s")
	{
		$params = JComponentHelper::getParams('com_redform');

		if (RHelperCurrency::isValid($currencyCode))
		{
			$precision = RHelperCurrency::getPrecision($currencyCode);
		}
		else
		{
			$precision = 2;
		}

		return sprintf(
			$format,
			$currencyCode,
			number_format(
				$price,
				$precision,
				$params->get('decimalseparator', '.'),
				$params->get('thousandseparator', ' ')
			)
		);
	}

	/**
	 * Return fields by sections
	 *
	 * @param   RdfRfield[]  $fields  fields
	 *
	 * @return array
	 */
	public static function sortFieldBySection($fields)
	{
		$sections = array_map(
			function ($item)
			{
				return $item->section_id;
			},
			$fields
		);
		$sections = array_values(array_unique(array_filter($sections)));

		$sortedSections = array();

		foreach ($sections as $section)
		{
			$sortedSections[$section] = new stdClass;
			$sortedSections[$section]->id = $section;
			$sortedSections[$section]->fields = array();
		}

		foreach ($fields as $f)
		{
			if ($f->section_id)
			{
				$sortedSections[$f->section_id]->fields[] = $f;
			}
			else
			{
				// Might happen in integration that the section is not set...
				$sortedSections[$sections[0]]->fields[] = $f;
			}
		}

		return $sortedSections;
	}

	/**
	 * Look for a field by field id in array of form fields
	 *
	 * @param   RdfRfield[]  $fields   fields
	 * @param   int          $fieldId  field id
	 *
	 * @return RdfRfield
	 */
	public static function findFormFieldByFieldId($fields, $fieldId)
	{
		foreach ($fields as $field)
		{
			if ($field->field_id == $fieldId)
			{
				return $field;
			}
		}

		return false;
	}

	/**
	 * Create a mailer object
	 *
	 * @return  JMail object
	 *
	 * @see     JMail
	 * @since   11.1
	 */
	protected static function createMailer()
	{
		$conf = JFactory::getConfig();

		$smtpauth = ($conf->get('smtpauth') == 0) ? null : 1;
		$smtpuser = $conf->get('smtpuser');
		$smtppass = $conf->get('smtppass');
		$smtphost = $conf->get('smtphost');
		$smtpsecure = $conf->get('smtpsecure');
		$smtpport = $conf->get('smtpport');
		$mailfrom = $conf->get('mailfrom');
		$fromname = $conf->get('fromname');
		$mailer = $conf->get('mailer');

		// Create a JMail object
		$mail = RdfHelperMailer::getInstance();

		// Set default sender without Reply-to
		$mail->SetFrom(JMailHelper::cleanLine($mailfrom), JMailHelper::cleanLine($fromname), 0);

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp':
				$mail->useSmtp($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
				break;

			case 'sendmail':
				$mail->IsSendmail();
				break;

			default:
				$mail->IsMail();
				break;
		}

		return $mail;
	}

	/**
	 * Get user timezone
	 *
	 * @param   JUser  $user  user
	 *
	 * @return DateTimeZone
	 *
	 * @since 3.3.18
	 */
	public static function getUserTimeZone($user = null)
	{
		$user = $user ?: JFactory::getUser();
		$timezone = $user->getParam('timezone', JFactory::getApplication()->get('offset', 'GMT'));

		return new DateTimeZone($timezone);
	}

	/**
	 * Get a date to user timezone
	 *
	 * @param   mixed  $date  date string or JDate
	 * @param   JUser  $user  user
	 *
	 * @return JDate
	 *
	 * @since  3.3.19
	 */
	public static function getDateToUserTimezone($date, $user= null)
	{
		$timezone = static::getUserTimeZone($user);

		if (is_string($date))
		{
			$date = JFactory::getDate($date, 'UTC');
		}

		return $date->setTimezone($timezone);
	}

	/**
	 * return the code for tags display
	 *
	 * @param   JFormField  $field  field to use tag for, allows filtering
	 *
	 * @return html
	 */
	public static function getTagsEditorInsertModal(JFormField $field)
	{
		return RdfLayoutHelper::render('redform.modal.tags', compact('field'));
	}
}
