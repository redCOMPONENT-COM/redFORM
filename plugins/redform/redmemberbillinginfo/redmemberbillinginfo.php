<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.economic
 *
 * @copyright   Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

RLoader::registerPrefix('Redmember', JPATH_SITE . '/libraries/redmember');

/**
 * Class plgRedformEconomic
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.economic
 * @since       3.0
 */
class plgRedformRedmemberbillinginfo extends JPlugin
{
	private $db = null;

	/**
	 * @var RedformeconomicSoapClient
	 */
	private $client = null;

	/**
	 * @var object
	 */
	private $cart = null;

	/**
	 * constructor
	 *
	 * @param   object  $subject  subject
	 * @param   array   $params   params
	 */
	public function __construct($subject, $params)
	{
		parent::__construct($subject, $params);
		$this->loadLanguage();

		$this->db = JFactory::getDbo();
	}

	/**
	 * Prefills redFORM billing form
	 *
	 * @param   string               $reference   carat reference
	 * @param   RedformTableBilling  &$table      table data to prefill
	 * @param   boolean              &$prefilled  did this plugin prefilled the info
	 *
	 * @return true on success
	 */
	public function onRedformPrefillBilling($reference, &$table, &$prefilled)
	{
		if (!class_exists('RedmemberApi'))
		{
			return false;
		}

		$user = JFactory::getUser();
		$rmUser = RedmemberApi::getUser($user->id);

		if ($field = $this->params->get('fullname'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->fullname = $rmValue ?: $table->fullname;
		}
		else
		{
			$table->fullname = $rmUser->name ?: $table->fullname;
		}

		if ($field = $this->params->get('company'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->company = $rmValue ?: $table->company;
		}

		if ($field = $this->params->get('vatnumber'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->vatnumber = $rmValue ?: $table->vatnumber;
		}

		if ($field = $this->params->get('address'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->address = $rmValue ?: $table->address;
		}

		if ($field = $this->params->get('city'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->city = $rmValue ?: $table->city;
		}

		if ($field = $this->params->get('zipcode'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->zipcode = $rmValue ?: $table->zipcode;
		}

		if ($field = $this->params->get('phone'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->phone = $rmValue ?: $table->phone;
		}

		if ($field = $this->params->get('email'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->email = $rmValue ?: $table->email;
		}
		else
		{
			$table->email = $rmUser->email ?: $table->email;
		}

		if ($field = $this->params->get('country'))
		{
			$rmValue = $this->replaceFields($field, $rmUser);
			$table->country = $rmValue ?: $table->country;
		}

		$prefilled = true;

		return true;
	}

	/**
	 * Replace {fields} tags in the text
	 *
	 * @param   string         $text    text to perform replace to
	 * @param   RedmemberUser  $rmUser  redMEMBER user object
	 *
	 * @return mixed
	 */
	private function replaceFields($text, $rmUser)
	{
		if (!preg_match_all('/{([^}]+)}/', $text, $matches))
		{
			return $text;
		}

		if ($organizations = $rmUser->getOrganizations())
		{
			$organization = reset($organizations);
			$organization = RedmemberApi::getOrganization($organization['organization_id']);
		}
		else
		{
			$organization = false;
		}

		foreach ($matches[1] as $k => $match)
		{
			if ($match == 'organization' && $organization)
			{
				$text = str_replace($matches[0][$k], $organization->name, $text);
			}
			elseif (strstr($match, 'organization_') && $organization)
			{
				$text = str_replace($matches[0][$k], $organization->{$match}, $text);
			}
			else
			{
				$text = str_replace($matches[0][$k], $rmUser->{$match}, $text);
			}
		}

		return $text;
	}
}
