<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.gaselsecondaryconfirmemail
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.gaselsecondaryconfirmemail
 * @since       3.0
 */
class plgRedformGaselsecondaryconfirmemail extends JPlugin
{
	private $sids;

	/**
	 * @var RdfCoreFormSubmission
	 */
	private $answers;

	/**
	 * constructor
	 *
	 * @param   object  $subject  subject
	 * @param   array   $params   params
	 */
	public function __construct($subject, $params)
	{
		parent::__construct($subject, $params);
		$this->loadLanguage();
	}

	/**
	 * Called after a submission was confirmed
	 *
	 * @param   array  $sids  submission id's
	 *
	 * @return void
	 */
	public function onSubmissionConfirmed($sids)
	{
		if (!$sids)
		{
			return;
		}

		$this->sids = $sids;

		try
		{
			$submissions = $this->getAnswers();

			foreach ($submissions->getSingleSubmissions() as $submission)
			{
				$this->sendSecondary($submission);
			}
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Send secondary emails to gaselsecondaryconfirmemail
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return void
	 */
	private function sendSecondary($submission)
	{
		$emails = $this->getEmails($submission);

		if (!$emails)
		{
			return;
		}

		$mailer = RdfHelper::getMailer();

		foreach ($emails as $address)
		{
			$mailer->AddAddress($address);
		}

		$subject = $submission->replaceTags($this->params->get('subject'));
		$mailer->setSubject($subject);

		$body = $submission->replaceTags($this->params->get('body'));
		$htmlmsg = RdfHelper::wrapMailHtmlBody($body, $subject);
		$mailer->MsgHTML($htmlmsg);

		/* Send the mail */
		if (!$mailer->Send())
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_REDFORM_NO_MAIL_SEND') . ' (secondary confirmation notification)', 'notice');
			RdfHelperLog::simpleLog(JText::_('COM_REDFORM_NO_MAIL_SEND') . ' (secondary confirmation notification):' . $mailer->error);
		}
	}

	/**
	 * Return emails
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return array
	 */
	private function getEmails($submission)
	{
		$ids = $this->params->get('emailIds');
		$emails = array();

		foreach ($submission->getFields() as $field)
		{
			if (in_array($field->field_id, $ids))
			{
				$email = $field->getValue();

				if (JMailHelper::isEmailAddress($email))
				{
					$emails[] = $email;
				}
			}
		}

		return $emails;
	}

	/**
	 * Return answers for sid
	 *
	 * @return RdfCoreFormSubmission
	 */
	private function getAnswers()
	{
		if (!$this->answers)
		{
			$rdfCore = new RdfCore;
			$this->answers = $rdfCore->getAnswers($this->sids);
		}

		return $this->answers;
	}

}
