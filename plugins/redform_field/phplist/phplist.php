<?php
/**
 * @package     Redform.plugins
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Redform\Plugin\AbstractFieldPlugin;

defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/field/phplist.php';

/**
 * Class plgRedform_fieldPhplist
 *
 * @since       2.5
 */
class plgRedform_fieldPhplist extends AbstractFieldPlugin
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
		$types[] = 'phplist';
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
		$options[] = \JHtml::_('select.option', 'phplist', JText::_('PLG_REDFORM_FIELD_PHPLIST_FIELD_PHPLIST'));
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
		if ('phplist' === $type)
		{
			$instance = new RdfRfieldPhplist;
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

		$app = \JFactory::getApplication();

		foreach ($answers->getFields() as $field)
		{
			if ($field->fieldtype !== 'phplist')
			{
				continue;
			}

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

			$path = $this->params->get('phplist_path');

			if (!JFolder::exists(JPATH_SITE . '/' . $path))
			{
				$app->enqueueMessage(JText::_('PLG_REDFORM_MAILING_ACYMAILING_LIST_NOT_FOUND'), 'error');

				return;
			}

			/* Include the PHPList API */
			require_once 'phplist/phplistuser.php';
			require_once 'phplist/simpleemail.php';
			require_once 'phplist/query.php';
			require_once 'phplist/errorhandler.php';

			/* Get the PHPList path configuration */
			\PhpListUser::$PHPListPath = JPATH_SITE . '/' . $path;

			foreach ($lists as $listname)
			{
				$user = new \PhpListUser;
				$user->set_email($email);
				$listid = $user->getListId($listname);
				$user->addListId($listid);
				$user->save();
			}
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
		$names[] = 'Phplist';

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
		if (strtolower($integration) != 'phplist')
		{
			return true;
		}

		$db = JFactory::getDBO();
		$fullname = $subscriber->name;
		$submitter_email = $subscriber->email;

		$path = $this->params->get('phplist_path');

		if (JFolder::exists(JPATH_SITE . '/' . $path))
		{
			/* Include the PHPList API */
			require_once 'phplist/phplistuser.php';
			require_once 'phplist/simpleemail.php';
			require_once 'phplist/query.php';
			require_once 'phplist/errorhandler.php';

			/* Get the PHPList path configuration */
			PhpListUser::$PHPListPath = JPATH_SITE . '/' . $path;

			$user = new PhpListUser;
			$user->set_email($submitter_email);
			$listid = $user->getListId($listname);
			$user->addListId($listid);
			$user->save();
		}

		return true;
	}
}
