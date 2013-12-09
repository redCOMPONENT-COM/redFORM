<?php
/**
 * @package     Redform.Backend
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Acl helper.
 *
 * @package     Redform.Backend
 * @subpackage  Helpers
 * @since       1.0
 */
final class RedformHelpersAcl
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   string  $section    The section.
	 * @param   mixed   $assetName  The asset name.
	 *
	 * @return  JObject
	 */
	public static function getActions($section = 'component', $assetName = 'com_redform')
	{
		$user = JFactory::getUser();
		$result	= new JObject;
		$actions = JAccess::getActions('com_redform', $section);

		foreach ($actions as $action)
		{
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Gets a list of the actions that can be performed to an asset ID
	 *
	 * @param   int     $assetID  The asset ID.
	 * @param   string  $section  The section.
	 *
	 * @return  JObject
	 */
	public static function getActionsAsset($assetID, $section = 'component')
	{
		$result	= new JObject;

		/** @var JTableAsset $assetTable */
		$assetTable = JTable::getInstance('Asset');

		if ($assetTable->load($assetID))
		{
			$result = self::getActions($section, $assetTable->name);
		}

		return $result;
	}
}
