<?php
/**
 * @package     Redform.plugins
 * @subpackage  field
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Redform\Plugin\AbstractFieldPlugin;

defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/field/ccnewsletter.php';
require_once __DIR__ . '/form/field/ccnewsletterlist.php';

/**
 * Class plgRedform_fieldCcnewsletter
 *
 * @since       2.5
 */
class plgRedform_fieldCcnewsletter extends AbstractFieldPlugin
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
		$types[] = 'ccnewsletter';
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
		$options[] = \JHtml::_('select.option', 'ccnewsletter', JText::_('PLG_REDFORM_FIELD_CCNEWSLETTER_FIELD_CCNEWSLETTER'));
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
		if ('ccnewsletter' === $type)
		{
			$instance = new RdfRfieldCcnewsletterlist;
			$instance->setPluginParams($this->params);
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
		$names[] = 'ccnewsletter';

		return true;
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
		if (!$answers->isNew())
		{
			return;
		}

		foreach ($answers->getFields() as $field)
		{
			if ($field->fieldtype !== 'ccnewsletter')
			{
				continue;
			}

			$emailFieldId = $field->getParam('email_field');

			if (!$email = $answers->getFieldAnswer($emailFieldId))
			{
				continue;
			}

			$groupIds = $field->getValue();

			if (empty($groupIds))
			{
				continue;
			}

			$fullname  = $answers->getFullname() ?: ($answers->getUsername() ?: $email);

			$subscriber = array('name' => $fullname, 'email' => $email);

			if (!$subId = $this->getSubscriber($subscriber))
			{
				continue;
			}

			foreach ($groupIds as $groupId)
			{
				$this->addToGroup($groupId, $subId);
			}
		}
	}

	/**
	 * Get subscriber id
	 *
	 * @param   array  $subscriber  subscriber name and email
	 *
	 * @return bool
	 *
	 * @since version
	 */
	private function getSubscriber($subscriber)
	{
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_ccnewsletter/tables/subscriber.php'))
		{
			JFactory::getApplication()->enqueueMessage('Ccnewsletter not installed, or incompatible version');

			return false;
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_ccnewsletter/tables/subscriber.php';
		$ccsubscriber = JTable::getInstance('subscriber', 'ccNewsletterTable');
		$ccsubscriber->load(array('email' => $subscriber['email']));

		if ($ccsubscriber->id)
		{
			// Already subscribed
			return $ccsubscriber->id;
		}

		$ccsettings = array(
			'name' => $subscriber['name'],
			'email' => $subscriber['email'],
			'enabled' => '1',
			'sdate' => date('Y-m-d H:i:s')
		);
		$ccsubscriber->bind($ccsettings);

		if (!$ccsubscriber->store())
		{
			JFactory::getApplication()->enqueueMessage('Ccnewsletter not installed, or incompatible version');

			return false;
		}

		return $ccsubscriber->id;
	}

	/**
	 * Add subscriber to group
	 *
	 * @param   integer  $groupId  group id
	 * @param   integer  $subId    subscriber id
	 *
	 * @return boolean
	 *
	 * @since version
	 */
	private function addToGroup($groupId, $subId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('subscriber_id')
			->from('#__ccnewsletter_g_to_s')
			->where('subscriber_id = ' . $subId)
			->where('group_id = ' . $groupId);

		$db->setQuery($query);

		if ($res = $db->loadResult())
		{
			return true;
		}

		$query = $db->getQuery(true)
			->insert('#__ccnewsletter_g_to_s')
			->columns(array('subscriber_id', 'group_id'))
			->values("$subId, $groupId");

		$db->setQuery($query);

		return $db->execute();
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
	 * @deprecated it's better to use the acymailing field than integration directly in email field
	 */
	public function subscribe($integration, $subscriber, $listname)
	{
		if (strtolower($integration) != 'ccnewsletter')
		{
			return true;
		}

		$db = JFactory::getDBO();
		$fullname = $subscriber->name;
		$submitter_email = $subscriber->email;

		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_ccnewsletter/tables/subscriber.php'))
		{
			/* ccNewsletter is installed, let's add the user */
			$query = ' SELECT id '
				. ' FROM #__ccnewsletter_subscribers '
				. ' WHERE email = ' . $db->Quote($submitter_email);
			$db->setQuery($query);
			$res = $db->loadResult();

			if ($res)
			{
				// Already subscribed
				return true;
			}

			require_once JPATH_ADMINISTRATOR . '/components/com_ccnewsletter/tables/subscriber.php';
			$ccsubscriber = JTable::getInstance('subscriber', 'RedformTable');
			$ccsettings = array('name' => $fullname,
				'email' => $submitter_email,
				'plainText' => '0',
				'enabled' => '1',
				'sdate' => date('Y-m-d H:i:s'));
			$ccsubscriber->bind($ccsettings);
			$ccsubscriber->store();
		}

		return true;
	}
}
