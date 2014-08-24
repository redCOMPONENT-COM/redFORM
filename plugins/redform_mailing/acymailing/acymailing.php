<?php
/**
 * @package     Redform.plugins
 * @subpackage  mailing
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die( 'Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Class plgRedform_mailingAcymailing
 *
 * @package     Redform.plugins
 * @subpackage  mailing
 * @since       2.5
 */
class plgRedform_mailingAcymailing extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Add to integration names
	 *
	 * @param   array  &$names  names to add to
	 *
	 * @return bool
	 */
	public function getIntegrationName(&$names)
	{
		$names[] = 'acymailing';

		return true;
	}

	/**
	 * Subscribe to list
	 *
	 * @param   string  $integration  integration name
	 * @param   JUser   $subscriber   subscriber
	 * @param   object  $listname     list name
	 *
	 * @return bool
	 */
	public function subscribe($integration, $subscriber, $listname)
	{
		$app = JFactory::getApplication();

		if (strtolower($integration) != 'acymailing')
		{
			return true;
		}

		$db = JFactory::getDBO();
		$fullname        = $subscriber->name;
		$submitter_email = $subscriber->email;

		$lists = $this->getLists();

		$listid = 0;

		foreach ($lists as $l)
		{
			if ($l->name == $listname)
			{
				$listid = $l->listid;
				break;
			}
		}

		if (!$listid)
		{
			$app->enqueueMessage(JText::_('PLG_REDFORM_MAILING_ACYMAILING_LIST_NOT_FOUND'), 'error');

			return false;
		}

		// First, add user to acymailing
		$myUser = new stdclass;
		$myUser->email = $subscriber->email;
		$myUser->name  = $subscriber->name;

		$subscriberClass = acymailing::get('class.subscriber');

		// This function will return you the ID of the user inserted in the AcyMailing table
		$subid = $subscriberClass->save($myUser);

		// Then add to the mailing list
		$subscribe = array($listid);
		$memberid  = $subid;

		$newSubscription = array();

		if (!empty($subscribe))
		{
			foreach ($subscribe as $listId)
			{
				$newList = null;
				$newList['status'] = 1;
				$newSubscription[$listId] = $newList;
			}
		}

		if (empty($newSubscription))
		{
			// There is nothing to do...
			return;
		}

		$subscriberClass->saveSubscription($subid, $newSubscription);

		return true;
	}

	/**
	 * Get lists
	 *
	 * @return mixed
	 */
	public function getLists()
	{
		$app = JFactory::getApplication();

		include_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';

		$listClass = acymailing::get('class.list');

		$allLists = $listClass->getLists();

		return $allLists;
	}
}
