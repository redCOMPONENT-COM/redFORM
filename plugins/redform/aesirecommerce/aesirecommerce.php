<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.aesirmember
 *
 * @copyright   Copyright (C) 2018 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * plugin class
 *
 * @since       3.0
 */
class PlgRedformAesirecommerce extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since 3.3.23
	 */
	protected $autoloadLanguage = true;

	/**
	 * Contains the current AEC user's object
	 *
	 * @var object
	 * @since 3.3.23
	 */
	private static $aecUser;

	/**
	 * Contains the current user's company object
	 *
	 * @var object
	 * @since 3.3.23
	 */
	private static $userCompany;

	/**
	 * Add aesir field integration
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 *
	 * @return boolean
	 *
	 * @since 3.3.23
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		if ('com_redform.edit.field.field' != $form->getName())
		{
			return true;
		}

		$field = simplexml_load_file(__DIR__ . '/field.xml');

		$form->setField($field, 'params');
	}

	/**
	 * Event for getting a default value from integration
	 *
	 * @param   RdfRfield  $field    The field in question
	 * @param   null       $default  The default value to be added
	 *
	 * @return boolean|void
	 *
	 * @since 3.3.23
	 */
	public function onRedformFieldLookupDefaultValue(RdfRfield $field, &$default)
	{
		$customFieldId = $field->getParam('aesir_ec_field');

		if (!$customFieldId || !class_exists('RedshopbHelperUser'))
		{
			return true;
		}

		$type = $field->getParam('aesir_ec_field');

		if (!$type)
		{
			return true;
		}

		$joomlaUser = JFactory::getUser();

		if (!($joomlaUser instanceof JUser) || !$joomlaUser->id)
		{
			return true;
		}

		$aecUser = $this->getAecUser($joomlaUser->id);

		if (!$aecUser)
		{
			return true;
		}

		switch ($type)
		{
			case 'company':
				$userCompany = $this->getCompany($aecUser->id);

				if (!$userCompany || $userCompany->b2c == 1)
				{
					return true;
				}

				$default = $userCompany->name;
			break;

			case 'name':
				$default  = $aecUser->name ?: '';
			break;

			default:
				return true;
		}
	}

	/**
	 * Method for getting the current Aesir E-Commerce user
	 *
	 * @param   integer  $joomlaUserId  The currently logged user's Joomla ID
	 *
	 * @return object
	 *
	 * @since 3.3.23
	 */
	private function getAecUser($joomlaUserId)
	{
		$instance = self::$aecUser;

		if (!$instance)
		{
			$instance = RedshopbHelperUser::getUser($joomlaUserId, 'joomla');
			self::$aecUser = $instance;
		}

		return $instance;
	}

	/**
	 * Method for getting the current user's company
	 *
	 * @param   integer  $aecUserId  The currently logged user's Aeisr E-Commerce ID
	 *
	 * @return object
	 *
	 * @since 3.3.23
	 */
	private function getCompany($aecUserId)
	{
		$instance = self::$userCompany;

		if (!$instance)
		{
			$instance          = RedshopbHelperUser::getUserCompany($aecUserId);
			self::$userCompany = $instance;
		}

		return $instance;
	}
}
