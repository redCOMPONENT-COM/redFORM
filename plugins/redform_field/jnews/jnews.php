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
 * Class plgRedform_mailingJnews
 *
 * @package     Redform.plugins
 * @subpackage  mailing
 * @since       2.5
 */
class plgRedform_mailingJnews extends JPlugin
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
		$names[] = 'jnews';

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
		if (strtolower($integration) != 'jnews')
		{
			return false;
		}

		if (!JComponentHelper::isEnabled('com_jnews'))
		{
			return false;
		}

		$mainframe = JFactory::getApplication();

		require_once 'lib/jnews_queue.php';
		require_once 'lib/jnews_subscribers.php';

		$db = JFactory::getDBO();
		$fullname = $subscriber->name;
		$submitter_email = $subscriber->email;

		/* jnews is installed, let's add the user */
		$jnewssubscriber = JTable::getInstance('jnews_subscribers', 'Table');

		$myid = JFactory::getUser();

		if (!isset($myid->id))
		{
			$myid->id = 0;
		}

		$jnewssettings = array('user_id' => $myid->id,
			'name' => $fullname,
			'email' => $submitter_email,
			'subscribe_date' => time()
		);

		// Email already registered in jnews ?
		if (!$jnewssubscriber->find($submitter_email))
		{
			$jnewssubscriber->bind($jnewssettings);

			if (!$jnewssubscriber->store())
			{
				if (stristr($db->getErrorMsg(), 'duplicate entry'))
				{
					$mainframe->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_ALREADY_REGISTERED'), 'error');
				}
				else
				{
					$mainframe->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_SUBSCRIBE_ERROR') . ' ' . $db->getErrorMsg(), 'error');
				}
			}
		}

		/* Check if the mailinglist exists, add the user to it */
		$list = false;
		$q = "SELECT id, acc_id FROM #__jnews_lists WHERE list_name = " . $db->Quote($listname);
		$db->setQuery($q);
		$list = $db->loadObject();

		if ($list)
		{
			// Add to subscriber list table
			$query = $db->getQuery(true);
			$query->select('list_id');
			$query->from('#__jnews_listssubscribers');
			$query->where('list_id = ' . $list->id);
			$query->where('subscriber_id = ' . $jnewssubscriber->id);
			$db->setQuery($query);
			$res = $db->loadResult();

			if ($res)
			{
				// Already susbscribed to this list
				return true;
			}

			$query = ' INSERT INTO #__jnews_listssubscribers SET '
				. ' list_id = ' . $list->id
				. ' , subscriber_id = ' . $jnewssubscriber->id
				. ' , subdate = ' . time();
			$db->setQuery($query);

			if (!$db->query())
			{
				$mainframe->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_SUBSCRIBE_ERROR') . ' ' . $db->getErrorMsg(), 'error');
			}
		}

		return true;
	}
}
