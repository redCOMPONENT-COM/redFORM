<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Helper for sending maintainer notification email
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       3.0
 */
class RdfHelperNotifymaintainer
{
	/**
	 * @var RdfAnswers
	 */
	private $answers;

	/**
	 * @var JMail
	 */
	private $mailer;

	/**
	 * RdfHelperNotifymaintainer constructor.
	 *
	 * @param   RdfAnswers  $answers  answers
	 */
	public function __construct($answers)
	{
		$this->answers = $answers;
		$this->mailer = RdfHelper::getMailer();
	}

	/**
	 * Send notification
	 *
	 * @return bool true on success
	 */
	public function notify()
	{
		$form = $this->getForm();

		if (!$this->setRecipients())
		{
			return true;
		}

		$this->setSender();

		// Set the email subject
		$replaceHelper = new RdfHelperTagsreplace($form, $this->answers);

		if (trim($form->contactpersonemailsubject))
		{
			$subject = $replaceHelper->replace($form->contactpersonemailsubject);
		}
		elseif ($this->answers->isNew())
		{
			$subject = $replaceHelper->replace(JText::_('COM_REDFORM_CONTACT_NOTIFICATION_EMAIL_SUBJECT'));
		}
		else
		{
			$subject = $replaceHelper->replace(JText::_('COM_REDFORM_CONTACT_NOTIFICATION_UPDATE_EMAIL_SUBJECT'));
		}

		$this->mailer->setSubject($subject);

		// Mail body
		if ($this->answers->isNew())
		{
			$htmlmsg = $replaceHelper->replace(JText::_('COM_REDFORM_MAINTAINER_NOTIFICATION_EMAIL_BODY'));
		}
		else
		{
			$htmlmsg = $replaceHelper->replace(JText::_('COM_REDFORM_MAINTAINER_NOTIFICATION_UPDATE_EMAIL_BODY'));
		}

		/* Add user submitted data if set */
		if ($form->contactpersonfullpost)
		{
			$htmlmsg .= $this->getAnswersHtmlTable();
		}

		$htmlmsg = RdfHelper::wrapMailHtmlBody($htmlmsg, $subject);
		$this->mailer->MsgHTML($htmlmsg);

		$this->attachUploads();

		// Send the mail
		if (!$this->mailer->Send())
		{
			RdfHelperLog::simpleLog(JText::_('COM_REDFORM_NO_MAIL_SEND') . ' (contactpersoninform): ' . $this->mailer->error);

			return false;
		}

		return true;
	}

	/**
	 * Add Form Contact Person Address(es) to mailer
	 *
	 * @return boolean true if has valid recipients
	 */
	private function addFormContactPersonAddress()
	{
		$hasValidRecipients = false;

		$form = $this->getForm();

		if (strstr($form->contactpersonemail, ';'))
		{
			$addresses = explode(";", $form->contactpersonemail);
		}
		else
		{
			$addresses = explode(",", $form->contactpersonemail);
		}

		foreach ($addresses as $a)
		{
			$a = trim($a);

			if (JMailHelper::isEmailAddress($a))
			{
				$hasValidRecipients = true;
				$this->mailer->addRecipient($a);
			}
		}

		return $hasValidRecipients;
	}

	/**
	 * Attach file uploads to mail
	 *
	 * @return void
	 */
	private function attachUploads()
	{
		foreach ($this->answers->getFields() as $field)
		{
			if ($field->fieldtype !== 'fileupload')
			{
				continue;
			}

			// Attach to mail
			$path = $field->getValue();

			if ($path && file_exists($path))
			{
				$this->mailer->addAttachment($path);
			}
		}
	}

	/**
	 * Return answers as simple html table
	 *
	 * @return string
	 *
	 * @todo: chould use a layout
	 */
	private function getAnswersHtmlTable()
	{
		$patterns[0] = '/\r\n/';
		$patterns[1] = '/\r/';
		$patterns[2] = '/\n/';
		$replacements[2] = '<br />';
		$replacements[1] = '<br />';
		$replacements[0] = '<br />';

		$htmlmsg = '<table border="1">';

		foreach ($this->answers->getAnswers() as $key => $answer)
		{
			if (is_array($answer['value']))
			{
				$answer['value'] = implode("<br/>", $answer['value']);
			}

			switch ($answer['type'])
			{
				case 'recipients':
					break;

				case 'email':
					$htmlmsg .= '<tr><td>' . $answer['field'] . '</td><td>';
					$htmlmsg .= '<a href="mailto:' . $answer['value'] . '">' . $answer['value'] . '</a>';
					$htmlmsg .= '&nbsp;';
					$htmlmsg .= '</td></tr>' . "\n";
					break;

				case 'textfield':
					$userinput = preg_replace($patterns, $replacements, $answer['value']);
					$htmlmsg .= '<tr><td>' . $answer['field'] . '</td><td>';

					if (strpos($answer['value'], 'http://') === 0)
					{
						$htmlmsg .= '<a href="' . $answer['value'] . '">' . $answer['value'] . '</a>';
					}
					else
					{
						$htmlmsg .= $answer['value'];
					}

					$htmlmsg .= '&nbsp;';
					$htmlmsg .= '</td></tr>' . "\n";
					break;

				case 'fileupload':
					$htmlmsg .= '<tr><td>' . $answer['field'] . '</td><td>';
					$htmlmsg .= ($answer['value'] && file_exists($answer['value'])) ? basename($answer['value']) : '';
					$htmlmsg .= '</td></tr>' . "\n";

					break;

				default :
					$userinput = preg_replace($patterns, $replacements, $answer['value']);
					$htmlmsg .= '<tr><td>' . $answer['field'] . '</td><td>';
					$htmlmsg .= str_replace('~~~', '<br />', $userinput);
					$htmlmsg .= '&nbsp;';
					$htmlmsg .= '</td></tr>' . "\n";
					break;
			}
		}

		if ($p = $this->answers->getPrice())
		{
			$htmlmsg .= '<tr><td>' . JText::_('COM_REDFORM_TOTAL_PRICE') . '</td><td>';
			$htmlmsg .= $p;
			$htmlmsg .= '</td></tr>' . "\n";
		}

		if ($v = $this->answers->getVat())
		{
			$htmlmsg .= '<tr><td>' . JText::_('COM_REDFORM_VAT') . '</td><td>';
			$htmlmsg .= $v;
			$htmlmsg .= '</td></tr>' . "\n";
		}

		$htmlmsg .= "</table>";

		return $htmlmsg;
	}

	/**
	 * Get form
	 *
	 * @return RdfEntityForm
	 */
	private function getForm()
	{
		return RdfEntityForm::load($this->answers->getFormId());
	}

	/**
	 * Set sender/reply fields
	 *
	 * @return void
	 */
	private function setSender()
	{
		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_redform');

		if ($params->get('allow_email_aliasing', 1))
		{
			if ($emails = $this->answers->getSubmitterEmails())
			{
				$sender = array(reset($emails));

				if ($name = $this->answers->getFullname())
				{
					$sender[] = $name;
				}
				else
				{
					$sender[] = '';
				}
			}
			else
			{
				// Default to site settings
				$sender = array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename'));
			}
		}
		else
		{
			// Default to site settings
			$sender = array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename'));
		}

		$this->mailer->setSender($sender);
		$this->mailer->addReplyTo($sender);
	}

	/**
	 * Set recipients
	 *
	 * @return bool true if there are valid recipients
	 */
	private function setRecipients()
	{
		$hasValidRecipients = false;

		if ($this->getForm()->contactpersoninform)
		{
			$hasValidRecipients = $this->addFormContactPersonAddress();
		}

		$answersRecipients = $this->answers->getRecipients();

		if (!empty($answersRecipients))
		{
			$hasValidRecipients = true;

			foreach ($answersRecipients AS $r)
			{
				$this->mailer->addRecipient($r);
			}
		}

		return $hasValidRecipients;
	}
}
