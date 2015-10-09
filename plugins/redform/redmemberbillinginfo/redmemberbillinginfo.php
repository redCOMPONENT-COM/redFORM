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
	 * @param   string               $reference  carat reference
	 * @param   RedformTableBilling  $table      table data to prefill
	 * @param   boolean              $prefilled  did this plugin prefilled the info
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
			$table->fullname = $this->replaceFields($field, $rmUser);
		}
		else
		{
			$table->fullname = $rmUser->name;
		}

		if ($field = $this->params->get('company'))
		{
			$table->company = $this->replaceFields($field, $rmUser);
		}

		if ($field = $this->params->get('vatnumber'))
		{
			$table->vatnumber = $this->replaceFields($field, $rmUser);
		}

		if ($field = $this->params->get('address'))
		{
			$table->address = $this->replaceFields($field, $rmUser);
		}

		if ($field = $this->params->get('city'))
		{
			$table->city = $this->replaceFields($field, $rmUser);
		}

		if ($field = $this->params->get('zipcode'))
		{
			$table->zipcode = $this->replaceFields($field, $rmUser);
		}

		if ($field = $this->params->get('phone'))
		{
			$table->phone = $this->replaceFields($field, $rmUser);
		}

		if ($field = $this->params->get('email'))
		{
			$table->email = $this->replaceFields($field, $rmUser);
		}
		else
		{
			$table->email = $rmUser->email;
		}

		if ($field = $this->params->get('country'))
		{
			$table->country = $this->replaceFields($field, $rmUser);
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
