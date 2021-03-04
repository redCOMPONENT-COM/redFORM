<?php
/**
 * @package     Redform.plugins
 * @subpackage  mailing
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
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
	 * @var boolean
	 */
	private $isLegacyLibrary = false;

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

			$emailFieldId = $field->getParam('email_field');
			$email        = $answers->getFieldAnswer($emailFieldId);

			if (!$email)
			{
				continue;
			}

			$lists = $field->getValue();

			if (empty($lists))
			{
				continue;
			}

			$fullname = $answers->getFullname() ?: ($answers->getUsername() ?: $email);

			$this->loadLibrary();

			$subid = $this->getSubId($email, $fullname);

			if ($this->isLegacyLibrary)
			{
				$subscriberClass = acymailing_get('class.subscriber');
				$newSubscription = array();

				foreach ($lists as $listId)
				{
					// Add to the mailing list
					$newSubscription[$listId] = array('status' => 1);
				}

				$subscriberClass->saveSubscription($subid, $newSubscription);
			}
			else
			{
				$userClass = acym_get('class.user');
				$userClass->subscribe($subid, $lists);
			}
		}
	}

	/**
	 * Subscribe to list
	 *
	 * @param   string  $integration  integration name
	 * @param   JUser   $subscriber   subscriber
	 * @param   object  $listname     list name
	 *
	 * @return boolean
	 *
	 * @deprecated it's better to use the acymailing field than integration directly in email field
	 *
	 * @throws Exception
	 */
	public function subscribe($integration, $subscriber, $listname)
	{
		$app = \JFactory::getApplication();

		if (strtolower($integration) != 'acymailing')
		{
			return true;
		}

		$lists = $this->getLists();

		$listid = 0;

		foreach ($lists as $l)
		{
			$lId = !empty($l->id) ? $l->id : $l->listid;

			if ((is_numeric($listname) && $listname == $lId)
				|| $l->name == $listname)
			{
				$listid = $lId;
				break;
			}
		}

		if (!$listid)
		{
			$app->enqueueMessage(JText::_('PLG_REDFORM_MAILING_ACYMAILING_LIST_NOT_FOUND'), 'error');

			return false;
		}

		$this->loadLibrary();

		$subid = $this->getSubId($subscriber->email, $subscriber->name);

		if ($this->isLegacyLibrary)
		{
			$subscriberClass = acymailing_get('class.subscriber');
			$subscriberClass->saveSubscription($subid, [$listid => ['status' => 1]]);
		}
		else
		{
			$userClass = acym_get('class.user');
			$userClass->subscribe($subid, [$listid]);
		}

		return true;
	}

	/**
	 * Get lists
	 *
	 * @return mixed
	 */
	public function getLists()
	{
		$this->loadLibrary();

		if ($this->isLegacyLibrary)
		{
			$listClass = acymailing_get('class.list');

			return $listClass->getLists();
		}

		$listClass = acym_get('class.list');

		return $listClass->getAll();
	}

	/**
	 * Get Id of acymailing user
	 *
	 * @param   string  $email  email
	 * @param   string  $name   name
	 *
	 * @return integer
	 */
	private function getSubId($email, $name)
	{
		$myUser = new \Stdclass;
		$myUser->email = $email;
		$myUser->name  = $name;

		if ($this->isLegacyLibrary)
		{
			$userClass = acymailing_get('class.subscriber');
		}
		else
		{
			$userClass = acym_get('class.user');
		}

		// This function will return you the ID of the user inserted in the AcyMailing table
		$subid = $userClass->save($myUser);

		return $subid;
	}

	/**
	 * Load acymailing library
	 *
	 * @return void
	 *
	 * @throws RuntimeException
	 */
	private function loadLibrary()
	{
		if (include_once(JPATH_ADMINISTRATOR . '/components/com_acym/helpers/helper.php'))
		{
			$this->isLegacyLibrary = false;
		}
		elseif (include_once(JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'))
		{
			$this->isLegacyLibrary = true;
		}
		else
		{
			throw new RuntimeException('Acymailing not installed, or incompatible version');
		}
	}
}
