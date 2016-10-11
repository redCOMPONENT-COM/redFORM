<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class Rdf answers user
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       3.3.14
 */
class RdfAnswersUser
{
	/**
	 * Creates a joomla user from submitter id
	 *
	 * @param   int  $submitterId  submitter id
	 *
	 * @return bool|JUser
	 *
	 * @throws RdfExceptionCreateuser
	 */
	public function createFromSubmitterId($submitterId)
	{
		jimport('joomla.user.helper');
		jimport('joomla.application.component.helper');

		JPluginHelper::getPlugin('redform');
		$dispatcher = RFactory::getDispatcher();
		$user = null;
		$dispatcher->trigger('onRedformCreateUserFromSubmitterId', array($submitterId, &$user));

		if ($user)
		{
			return $user;
		}

		$rfcore = RdfCore::getInstance();
		$answers = $rfcore->getSidContactEmails($submitterId);

		if (!$answers)
		{
			throw new RdfExceptionCreateuser(JText::_('LIB_REDFORM_CREATE_USER_SID_NOT_FOUND') . ': ' . $submitterId);
		}

		$details = current($answers);

		if (!$details['email'])
		{
			throw new RdfExceptionCreateuser(JText::_('LIB_REDFORM_CREATE_USER_EMAIL_IS_REQUIRED'));
		}

		if ($uid = $this->getUserIdFromEmail($details['email']))
		{
			return JFactory::getUser($uid);
		}

		if (!$details['username'] && !$details['fullname'])
		{
			$username = 'redform user ' . $submitterId;
			$details['fullname'] = $username;
		}
		else
		{
			$username = $details['username'] ? $details['username'] : $details['fullname'];
			$details['fullname'] = $details['fullname'] ? $details['fullname'] : $username;
		}

		$username = $this->getUniqueUsername($username);

		// Get required system objects
		$user = clone JFactory::getUser(0);
		$password = JUserHelper::genRandomPassword();

		$config = JComponentHelper::getParams('com_users');

		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);

		// Set some initial user values
		$user->set('id', 0);
		$user->set('name', $details['fullname']);
		$user->set('username', $username);
		$user->set('email', $details['email']);
		$user->set('groups', array($defaultUserGroup));
		$user->set('password', md5($password));

		if (!$user->save())
		{
			throw new RdfExceptionCreateuser(JText::_($user->getError()));
		}

		// Send email using juser controller
		$this->sendUserCreatedMail($user, $password);

		return $user;
	}

	/**
	 * Creates a joomla user from submitter id
	 *
	 * @param   int  $submitterId  submitter id
	 *
	 * @return bool|JUser
	 *
	 * @throws RdfExceptionCreateuser
	 */
	public function getFromSubmitterId($submitterId)
	{
		JPluginHelper::getPlugin('redform');
		$dispatcher = RFactory::getDispatcher();
		$user = null;
		$dispatcher->trigger('onRedformGetUserFromSubmitterId', array($submitterId, &$user));

		if ($user)
		{
			return $user;
		}

		$rfcore = RdfCore::getInstance();
		$answers = $rfcore->getSidContactEmails($submitterId);

		if (!$answers)
		{
			throw new RdfExceptionCreateuser(JText::_('LIB_REDFORM_CREATE_USER_SID_NOT_FOUND') . ': ' . $submitterId);
		}

		$details = reset($answers);

		if (!$details['email'])
		{
			throw new RdfExceptionCreateuser(JText::_('LIB_REDFORM_CREATE_USER_EMAIL_IS_REQUIRED'));
		}

		if ($uid = $this->getUserIdFromEmail($details['email']))
		{
			return JFactory::getUser($uid);
		}

		return false;
	}

	/**
	 * Returns userid if a user exists
	 *
	 * @param   string  $email  The email to search on
	 *
	 * @return int The user id or 0 if not found
	 */
	protected function getUserIdFromEmail($email)
	{
		// Initialize some variables
		$db = JFactory::getDBO();

		$query = 'SELECT id FROM #__users WHERE email = ' . $db->Quote($email);
		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}

	/**
	 * Make sure username is unique, adding suffix if necessary
	 *
	 * @param   string  $username  the username to check
	 *
	 * @return string
	 */
	protected function getUniqueUsername($username)
	{
		$db = JFactory::getDBO();

		$i = 2;

		while (true)
		{
			$query = 'SELECT id FROM #__users WHERE username = ' . $db->Quote($username);
			$db->setQuery($query, 0, 1);

			if ($db->loadResult())
			{
				// Username exists, add a suffix
				$username = $username . '_' . ($i++);
			}
			else
			{
				break;
			}
		}

		return $username;
	}

	/**
	 * inspired from com_user controller function
	 *
	 * @param   object  $user      user object
	 * @param   string  $password  user password
	 *
	 * @return void
	 */
	protected function sendUserCreatedMail($user, $password)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_user');

		$mainframe = JFactory::getApplication();

		$db = JFactory::getDbo();

		$name = $user->get('name');
		$email = $user->get('email');
		$username = $user->get('username');

		$usersConfig = JComponentHelper::getParams('com_users');
		$sitename = $mainframe->getCfg('sitename');
		$mailfrom = $mainframe->getCfg('mailfrom');
		$fromname = $mainframe->getCfg('fromname');
		$siteURL = JURI::base();

		$subject = JText::sprintf('LIB_REDFORM_CREATE_USER_EMAIL_SUBJECT', $name, $sitename);
		$subject = html_entity_decode($subject, ENT_QUOTES);

		$message = JText::_('LIB_REDFORM_CREATE_USER_INFORM_USERNAME');
		$message = str_replace('[fullname]', $name, $message);
		$message = str_replace('[username]', $username, $message);
		$message = str_replace('[password]', $password, $message);

		$message = html_entity_decode($message, ENT_QUOTES);

		$mail = RdfHelper::getMailer();
		$mail->sendMail($mailfrom, $fromname, $email, $subject, $message);

		// Send notification to all administrators
		$subject2 = JText::sprintf('LIB_REDFORM_CREATE_USER_EMAIL_SUBJECT', $name, $sitename);
		$subject2 = html_entity_decode($subject2, ENT_QUOTES);

		// Get all users that are set to receive system emails
		$query = $db->getQuery(true)
			->select('name, email')
			->from('#__users')
			->where('sendEmail = 1');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$emailBody = JText::sprintf(
				'LIB_REDFORM_CREATE_USER_EMAIL_BODY',
				$name,
				$sitename
			);
			$message2 = html_entity_decode($emailBody, ENT_QUOTES);
			$mail->sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
		}
	}
}
