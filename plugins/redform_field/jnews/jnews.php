<?php
/**
 * @package     Redform.plugins
 * @subpackage  mailing
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Redform\Plugin\AbstractFieldPlugin;

defined('_JEXEC') or die( 'Restricted access');

require_once __DIR__ . '/field/jnews.php';
require_once __DIR__ . '/form/field/jnewslist.php';

/**
 * Class plgRedform_fieldJnews
 *
 * @since       2.5
 */
class plgRedform_fieldJnews extends AbstractFieldPlugin
{
	/**
	 * Add supported field type(s)
	 *
	 * @param   string[]  &$types  types
	 *
	 * @return void
	 */
	public function onRedformGetFieldTypes(&$types)
	{
		$types[] = 'jnews';
	}

	/**
	 * Add supported field type(s) as option(s)
	 *
	 * @param   object[]  &$options  options
	 *
	 * @return void
	 */
	public function onRedformGetFieldTypesOptions(&$options)
	{
		$options[] = \JHtml::_('select.option', 'jnews', JText::_('PLG_REDFORM_MAILING_JNEWS_FIELD_JNEWS'));
	}

	/**
	 * Return an instance of supported types, if matches.
	 *
	 * @param   string     $type       type of field
	 * @param   RdfRfield  &$instance  instance of field
	 *
	 * @return void
	 */
	public function onRedformGetFieldInstance($type, &$instance)
	{
		if ('jnews' === $type)
		{
			$instance = new RdfRfieldJnewslist;
			$instance->setPluginParams($this->params);
		}
	}

	/**
	 * Callback
	 *
	 * @param   RdfAnswers  $answers  answers saved
	 *
	 * @return void
	 *
	 * @since version
	 */
	public function onAfterRedformSubmitterSaved(\RdfAnswers $answers)
	{
		$app = \JFactory::getApplication();

		if (!$answers->isNew())
		{
			return;
		}

		foreach ($answers->getFields() as $field)
		{
			if ($field->fieldtype !== 'jnews')
			{
				continue;
			}

			$emailFieldId = $field->getParam('email_field');

			if (!$submitter_email = $answers->getFieldAnswer($emailFieldId))
			{
				continue;
			}

			$fullname  = $answers->getFullname() ?: ($answers->getUsername() ?: $submitter_email);

			$lists = $field->getValue();

			if (empty($lists))
			{
				continue;
			}

			require_once 'lib/jnews_queue.php';
			require_once 'lib/jnews_subscribers.php';

			$db = \JFactory::getDBO();

			/* jnews is installed, let's add the user */
			$jnewssubscriber = \JTable::getInstance('Jnews_subscribers', 'Table');

			$myid = \JFactory::getUser();

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
						$app->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_ALREADY_REGISTERED'), 'error');
					}
					else
					{
						$app->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_SUBSCRIBE_ERROR') . ' ' . $db->getErrorMsg(), 'error');
					}
				}
			}

			/* Check if the mailinglist exists, add the user to it */
			foreach ($lists as $listId)
			{
				// Add to subscriber list table
				$query = $db->getQuery(true);
				$query->select('list_id');
				$query->from('#__jnews_listssubscribers');
				$query->where('list_id = ' . $listId);
				$query->where('subscriber_id = ' . $jnewssubscriber->id);
				$db->setQuery($query);
				$res = $db->loadResult();

				if ($res)
				{
					// Already susbscribed to this list
					return true;
				}

				$query = $db->getQuery(true)
					->insert('#__jnews_listssubscribers')
					->columns(array('list_id', 'subscriber_id', 'subdate'))
					->values("$listId, " . $jnewssubscriber->id . ", " . $db->q(time()))
				;

				$db->setQuery($query);

				if (!$db->execute())
				{
					$app->enqueueMessage(JText::_('PLG_REDFORM_MAILING_jnews_SUBSCRIBE_ERROR') . ' ' . $db->getErrorMsg(), 'error');
				}
			}

			return true;
		}
	}

	/**
	 * Add to integration names
	 *
	 * @param   array  &$names  names to add to
	 *
	 * @return bool
	 *
	 * @deprecated
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
	 *
	 * @deprecated
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
