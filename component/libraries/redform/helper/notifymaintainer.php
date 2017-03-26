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

		if ($form->admin_notification_email_mode)
		{
			$subject = $replaceHelper->replace($form->admin_notification_email_subject);
		}
		else
		{
			$subject = $this->answers->isNew() ?
				$replaceHelper->replace(JText::_('COM_REDFORM_CONTACT_NOTIFICATION_EMAIL_SUBJECT')) :
				$replaceHelper->replace(JText::_('COM_REDFORM_CONTACT_NOTIFICATION_UPDATE_EMAIL_SUBJECT'));
		}

		$this->mailer->setSubject($subject);

		// Mail body
		if ($form->admin_notification_email_mode)
		{
			$htmlmsg = $replaceHelper->replace($form->admin_notification_email_body);
		}
		else
		{
			$htmlmsg = $this->answers->isNew() ?
				$replaceHelper->replace(JText::_('COM_REDFORM_MAINTAINER_NOTIFICATION_EMAIL_BODY')) :
				$replaceHelper->replace(JText::_('COM_REDFORM_MAINTAINER_NOTIFICATION_UPDATE_EMAIL_BODY'));
		}

		// Add user submitted data if set
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
		$addresses = RdfHelper::extractEmails($this->getForm()->contactpersonemail);

		foreach ($addresses as $a)
		{
			$this->mailer->addRecipient($a);
		}

		return count($addresses);
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

		foreach ($this->answers->getFields() as $key => $field)
		{
			switch ($field->fieldtype)
			{
				case 'recipients':
				case 'submissionprice':
					break;

				case 'email':
					$htmlmsg .= '<tr><td>' . $field->name . '</td><td>';
					$htmlmsg .= '<a href="mailto:' . $field->value . '">' . $field->value . '</a>';
					$htmlmsg .= '&nbsp;';
					$htmlmsg .= '</td></tr>' . "\n";
					break;

				case 'textfield':
					$htmlmsg .= '<tr><td>' . $field->name . '</td><td>';

					if (strpos($field->value, 'http://') === 0)
					{
						$htmlmsg .= '<a href="' . $field->value . '">' . $field->value . '</a>';
					}
					else
					{
						$htmlmsg .= $field->value;
					}

					$htmlmsg .= '&nbsp;';
					$htmlmsg .= '</td></tr>' . "\n";
					break;

				case 'fileupload':
					$htmlmsg .= '<tr><td>' . $field->name . '</td><td>';
					$htmlmsg .= ($field->value && file_exists($field->value)) ? basename($field->value) : '';
					$htmlmsg .= '</td></tr>' . "\n";
					break;

				default :
					$userinput = preg_replace($patterns, $replacements, $field->getValueAsString("<br>"));
					$htmlmsg .= '<tr><td>' . $field->name . '</td><td>';
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
