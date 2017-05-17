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

require_once __DIR__ . '/field/acymailing.php';
require_once __DIR__ . '/form/field/acymailinglist.php';

/**
 * Class plgRedform_mailingAcymailing
 *
 * @since       2.5
 */
class plgRedform_fieldAcymailing extends AbstractFieldPlugin
{
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
		$names[] = 'acymailing';

		return true;
	}

	/**
	 * Add supported field type(s)
	 *
	 * @param   string[]  &$types  types
	 *
	 * @return void
	 */
	public function onRedformGetFieldTypes(&$types)
	{
		$types[] = 'acymailing';
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
		$options[] = \JHtml::_('select.option', 'acymailing', JText::_('PLG_REDFORM_MAILING_ACYMAILING_FIELD_ACYMAILING'));
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
		if ('acymailing' === $type)
		{
			$instance = new RdfRfieldAcymailinglist;
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
		if (!$answers->isNew())
		{
			return;
		}

		foreach ($answers->getFields() as $field)
		{
			if ($field->fieldtype !== 'acymailing')
			{
				continue;
			}

			include_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';

			$emailFieldId = $field->getParam('email_field');

			if (!$email = $answers->getFieldAnswer($emailFieldId))
			{
				continue;
			}

			$lists = $field->getValue();

			if (empty($lists))
			{
				continue;
			}

			$fullname  = $answers->getFullname() ?: ($answers->getUsername() ?: $email);
			$subid = $this->getSubId($email, $fullname);

			$subscriberClass = acymailing_get('class.subscriber');

			$newSubscription = array();

			foreach ($lists as $listId)
			{
				// Add to the mailing list
				$newSubscription[$listId] = array('status' => 1);
			}

			$subscriberClass->saveSubscription($subid, $newSubscription);
		}
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
		$app = \JFactory::getApplication();

		if (strtolower($integration) != 'acymailing')
		{
			return true;
		}

		$db = \JFactory::getDBO();
		$fullname        = $subscriber->name;
		$submitter_email = $subscriber->email;

		$lists = $this->getLists();

		$listid = 0;

		foreach ($lists as $l)
		{
			if ((is_numeric($listname) && $listname == $l->listid)
				|| $l->name == $listname)
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

		$subid = $this->getSubId($subscriber->email, $subscriber->name);

		// Then add to the mailing list
		$subscribe = array($listid);

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

		$subscriberClass = acymailing_get('class.subscriber');
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
		$app = \JFactory::getApplication();

		include_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';

		$listClass = acymailing_get('class.list');

		$allLists = $listClass->getLists();

		return $allLists;
	}

	private function getSubId($email, $name)
	{
		$myUser = new \Stdclass;
		$myUser->email = $email;
		$myUser->name  = $name;

		$subscriberClass = acymailing_get('class.subscriber');

		// This function will return you the ID of the user inserted in the AcyMailing table
		$subid = $subscriberClass->save($myUser);

		return $subid;
	}
}
