<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Core
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCoreSubmission
 *
 * @package     Redform.Libraries
 * @subpackage  Core
 * @since       3.0
 */
class RdfCoreSubmission extends JObject
{
	protected $formId;

	protected $formModel;

	protected $submitKey;

	protected $answers;

	/**
	 * Constructor
	 *
	 * @param   int  $formId  form id associated to submission
	 */
	public function __construct($formId = null)
	{
		if ($formId)
		{
			$formId = (int) $formId;
			$this->formId = $formId;
		}
	}

	/**
	 * Set submit key
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return void
	 */
	public function setSubmitKey($submit_key)
	{
		$this->submitKey = $submit_key;
	}

	/**
	 * save data to database
	 *
	 * @param   string  $integration_key  integration key
	 * @param   array   $options          options: skip_captcha, ...
	 * @param   array   $data             form data, leave null to use posted data
	 *
	 * @return boolean|stdclass
	 */
	public function apisaveform($integration_key = '', $options = array(), $data = null)
	{
		$app = JFactory::getApplication();

		$result = new stdclass;
		$result->posts = array();

		// Check the token
		$token = RdfCore::getToken();

		// Get data from post if not specified
		if (!$data)
		{
			$data = array_merge(JRequest::get('post'), JRequest::get('files'));

			if (!isset($data['form_id']))
			{
				$data['form_id'] = $app->input->getInt('form_id', 0);
			}

			if (!isset($data['submit_key']))
			{
				$data['submit_key'] = $app->input->getCmd('submit_key', false);
			}

			if (!isset($data['nbactive']))
			{
				$data['nbactive'] = $app->input->getInt('nbactive', 1);
			}

			if (!isset($data['currency']))
			{
				$data['currency'] = $app->input->getCmd('currency', '');
			}
		}

		if (!isset($data[$token]))
		{
			$this->setError('Form integrity check failed' );

			return false;
		}

		$check_captcha = JFactory::getSession()->get('checkcaptcha' . $data[$token], 0);

		if (!isset($data['submit_key']) || !$data['submit_key'])
		{
			$submit_key = uniqid();
		}
		else
		{
			$submit_key = $data['submit_key'];
		}

		$this->setSubmitKey($submit_key);

		$result->submit_key = $submit_key;

		/* Get the form details */
		$this->formId = $data['form_id'];
		$form = $this->getForm();

		$currency = $data['currency'] ? $data['currency'] : $form->currency;

		/* Load the fields */
		$fieldlist = $this->getfields($form->id);

		// Number of submitted active forms (min is 1)
		$totalforms = isset($data['nbactive']) ? $data['nbactive'] : 1;

		$allanswers = array();

		/* Loop through the different forms */
		for ($signup = 1; $signup <= $totalforms; $signup++)
		{
			// New answers object
			$answers = new RdfAnswers;
			$answers->setFormId($form->id);

			if (isset($data['submitter_id' . $signup]))
			{
				$answers->setSid(intval($data['submitter_id' . $signup]));
			}

			$answers->setFormId($data['form_id']);
			$answers->setSubmitKey($submit_key);
			$answers->setIntegration($integration_key);
			$answers->setCurrency($currency);

			if (isset($options['baseprice']))
			{
				if (is_array($options['baseprice']))
				{
					$answers->initPrice(isset($options['baseprice'][$signup - 1]) ? $options['baseprice'][$signup - 1] : 0);
				}
				else
				{
					$answers->initPrice($options['baseprice']);
				}
			}

			/* Create an array of values to store */
			$postvalues = array();

			// Remove the _X parts, where X is the form (signup) number
			foreach ($data as $key => $value)
			{
				if ((strpos($key, 'field') === 0) && (strpos($key, '_' . $signup, 5) > 0))
				{
					$postvalues[str_replace('_' . $signup, '', $key)] = $value;
				}
				else
				{
					$postvalues[$key] = $value;
				}
			}

			/* Get the raw form data */
			$postvalues['rawformdata'] = serialize($data);

			/* Build up field list */
			foreach ($fieldlist as $field)
			{
				if (isset($postvalues['field' . $field->id]))
				{
					/* Get the answers */
					try
					{
						$clone = clone $field;
						$answers->addPostAnswer($clone, $postvalues['field' . $clone->id]);
					}
					catch (Exception $e)
					{
						$this->setError($e->getMessage());

						return false;
					}
				}
			}

			$allanswers[] = $answers;
		}

		/* End multi-user signup */

		$this->answers = $allanswers;

		// Save to session in case we need to display form again
		$sessiondata = array();

		foreach ($allanswers as $a)
		{
			$sessiondata[] = $a->toSession();
		}

		$app->setUserState('formdata' . $data['form_id'], $sessiondata);

		// Captcha verification
		if ($check_captcha)
		{
			JPluginHelper::importPlugin('redform_captcha');
			$res = true;
			$dispatcher = JDispatcher::getInstance();
			$results = $dispatcher->trigger('onCheckCaptcha', array(&$res));

			if (count($results) && $res == false)
			{
				$this->setError(JText::_('COM_REDFORM_CAPTCHA_WRONG'));

				return false;
			}
		}

		// Savetosession: data is saved to session using the submit key
		if (isset($options['savetosession']))
		{
			$sessiondata = array();

			foreach ($allanswers as $a)
			{
				$sessiondata[] = $a->toSession();
			}

			$app->setUserState($submit_key, $sessiondata);

			return $result;
		}

		// Else save to db !
		foreach ($allanswers as $answers)
		{
			$res = $answers->savedata();

			if (!$res)
			{
				$this->setError(JText::_('COM_REDFORM_SAVE_ANSWERS_FAILED'));

				return false;
			}
			else
			{
				// Delete session data
				$app->setUserState('formdata' . $form->id, null);
				$result->posts[] = array('sid' => $res);
			}

			if ($answers->isNew())
			{
				$this->updateMailingList($answers);
			}
		}

		// Send email to maintainers
		$this->notifymaintainer($allanswers, $answers->isNew());

		/* Send a submission mail to the submitters if set */
		if ($answers->isNew() && $form->submitterinform)
		{
			foreach ($allanswers as $answers)
			{
				$this->notifysubmitter($answers, $form);
			}
		}

		return $result;
	}

	/**
	 * Create a new submission from fields
	 *
	 * @param   array   $fields       RdfRfield fields, with value set
	 * @param   string  $integration  integration key
	 * @param   array   $options      options: baseprice, currency
	 *
	 * @return boolean true on success
	 */
	public function quicksubmit($fields, $integration = null, $options = null)
	{
		$result = new stdclass;
		$result->posts = array();

		$submit_key = uniqid();
		$result->submit_key = $submit_key;

		$form = $this->getForm();

		// New answers object
		$answers = new RdfAnswers;
		$answers->setFormId($form->id);
		$answers->setIntegration($integration);
		$answers->setSubmitKey($submit_key);

		if (isset($options['baseprice']))
		{
			$answers->initPrice($options['baseprice']);
		}

		if (isset($options['currency']))
		{
			$answers->setCurrency($options['currency']);
		}

		/* Build up field list */
		foreach ($fields as $field)
		{
			$answers->addField($field);
		}

		$sid = $answers->savedata();

		$this->updateMailingList($answers);

		// Send email to maintainers
		$this->notifymaintainer(array($answers), true);

		/* Send a submission mail to the submitters if set */
		if ($form->submitterinform)
		{
			$this->notifysubmitter($answers, $form);
		}

		$result->posts[] = array('sid' => $sid);

		return $result;
	}

	/**
	 * Return true if submission has associated price
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return bool
	 */
	public function hasActivePayment($submitKey = null)
	{
		if (!$submitKey)
		{
			$submitKey = $this->submitKey;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('s.price');
		$query->from('#__rwf_submitters AS s');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id');
		$query->join('LEFT', '#__rwf_payment AS p ON s.submit_key = p.submit_key AND p.paid = 1');
		$query->where('s.submit_key = ' . $db->quote($submitKey));
		$query->where('p.id IS NULL');
		$query->where('f.activatepayment = 1');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res ? true : false;
	}

	/**
	 * return form redirect
	 *
	 * @return mixed false if not set, or string
	 */
	public function getFormRedirect()
	{
		$model = $this->getFormModel();

		$redirect = trim($model->getForm()->redirect);

		return $redirect ? $redirect : false;
	}

	/**
	 * Return submission notification text
	 *
	 * @return mixed
	 */
	public function getNotificationText()
	{
		$form = $this->getForm();

		if (empty($this->answers))
		{
			return $form->notificationtext;
		}
		else
		{
			return $this->replaceTags($form->notificationtext, reset($this->answers));
		}
	}

	/**
	 * send email to form maintaineers or/and selected recipients
	 *
	 * @param   array  $allanswers  answers
	 * @param   bool   $new         is new ?
	 *
	 * @return bool
	 */
	public function notifymaintainer($allanswers, $new = true)
	{
		$mainframe = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_redform');
		$form = $this->getForm();

		/* Inform contact person if need */
		// Form recipients
		$recipients = $allanswers[0]->getRecipients();
		$cond_recipients = RdfHelperConditionalrecipients::getRecipients($form, $allanswers[0]);

		if ($cond_recipients)
		{
			foreach ($cond_recipients as $c)
			{
				$recipients[] = $c[0];
			}
		}

		if ($form->contactpersoninform || !empty($recipients))
		{
			// Init mailer
			$mailer = JFactory::getMailer();
			$mailer->isHTML(true);

			if ($form->contactpersoninform)
			{
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
						$mailer->addRecipient($a);
					}
				}
			}

			if (!empty($recipients))
			{
				foreach ($recipients AS $r)
				{
					$mailer->addRecipient($r);
				}
			}

			if (!empty($xref_group_recipients))
			{
				foreach ($xref_group_recipients AS $r)
				{
					$mailer->addRecipient($r);
				}
			}

			// We put the submitter as the email 'from' and reply to.
			$user = JFactory::getUser();

			if ($params->get('allow_email_aliasing', 1))
			{
				if ($user->get('id'))
				{
					$sender = array($user->email, $user->name);
				}
				elseif ($allanswers[0]->getSubmitterEmails())
				{
					if ($allanswers[0]->getFullname())
					{
						$sender = array(reset($allanswers[0]->getSubmitterEmails()), $allanswers[0]->getFullname());
					}
					else
					{
						$sender = array(reset($allanswers[0]->getSubmitterEmails()), null);
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

			$mailer->setSender($sender);
			$mailer->addReplyTo($sender);

			// Set the email subject
			$replaceHelper = new RdfHelperTagsreplace($form, $allanswers[0]);

			if (trim($form->contactpersonemailsubject))
			{
				$subject = $replaceHelper->replace($form->contactpersonemailsubject);
			}
			elseif ($new)
			{
				$subject = $replaceHelper->replace(JText::_('COM_REDFORM_CONTACT_NOTIFICATION_EMAIL_SUBJECT'));
			}
			else
			{
				$subject = $replaceHelper->replace(JText::_('COM_REDFORM_CONTACT_NOTIFICATION_UPDATE_EMAIL_SUBJECT'));
			}

			$mailer->setSubject($subject);

			// Mail body
			$htmlmsg = '<html><head><title></title></title></head><body>';

			if ($new)
			{
				$htmlmsg .= $replaceHelper->replace(JText::_('COM_REDFORM_MAINTAINER_NOTIFICATION_EMAIL_BODY'));
			}
			else
			{
				$htmlmsg .= $replaceHelper->replace(JText::_('COM_REDFORM_MAINTAINER_NOTIFICATION_UPDATE_EMAIL_BODY'));
			}

			/* Add user submitted data if set */
			if ($form->contactpersonfullpost)
			{
				foreach ($allanswers as $answers)
				{
					$rows = $answers->getAnswers();
					$patterns[0] = '/\r\n/';
					$patterns[1] = '/\r/';
					$patterns[2] = '/\n/';
					$replacements[2] = '<br />';
					$replacements[1] = '<br />';
					$replacements[0] = '<br />';

					$htmlmsg .= '<br /><table border="1">';

					foreach ($rows as $key => $answer)
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

							case 'text':
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

							case 'file':
								$userinput = preg_replace($patterns, $replacements, $answer['value']);
								$htmlmsg .= '<tr><td>' . $answer['field'] . '</td><td>';
								$htmlmsg .= ($answer['value'] && file_exists($answer['value'])) ? basename($answer['value']) : '';
								$htmlmsg .= '</td></tr>' . "\n";

								// Attach to mail
								if ($answer['value'] && file_exists($answer['value']))
								{
									$mailer->addAttachment($answer['value']);
								}

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

					if ($p = $answers->getPrice())
					{
						$htmlmsg .= '<tr><td>' . JText::_('COM_REDFORM_TOTAL_PRICE') . '</td><td>';
						$htmlmsg .= $p;
						$htmlmsg .= '</td></tr>' . "\n";
					}

					$htmlmsg .= "</table><br />";
				}
			}

			$htmlmsg .= '</body></html>';
			$mailer->setBody($htmlmsg);

			// Send the mail
			if (!$mailer->Send())
			{
				RdfHelperLog::simpleLog(JText::_('COM_REDFORM_NO_MAIL_SEND') . ' (contactpersoninform): ' . $mailer->error);
			}
		}

		return true;
	}

	/**
	 * Send notification to submitter
	 *
	 * @param   RdfAnswers  $answers  answers
	 * @param   object      $form     form
	 *
	 * @return bool
	 */
	protected function notifysubmitter(RdfAnswers $answers, $form)
	{
		$emails = $answers->getSubmitterEmails();
		$cond_recipients = RdfHelperConditionalrecipients::getRecipients($form, $answers);

		foreach ($emails as $submitter_email)
		{
			$mailer = JFactory::getMailer();
			$mailer->isHTML(true);

			if ($cond_recipients)
			{
				$mailer->From = $cond_recipients[0][0];
				$mailer->FromName = $cond_recipients[0][1];
				$mailer->ClearReplyTos();
				$mailer->addReplyTo($cond_recipients[0]);
			}

			if (JMailHelper::isEmailAddress($submitter_email))
			{
				/* Add the email address */
				$mailer->AddAddress($submitter_email);

				/* Mail submitter */
				$submission_body = $form->submissionbody;
				$submission_body = $this->replaceTags($submission_body, $answers);
				$htmlmsg = '<html><head><title>Welcome</title></title></head><body>' . $submission_body . '</body></html>';
				$mailer->setBody($htmlmsg);

				$subject = $this->replaceTags($form->submissionsubject, $answers);
				$mailer->setSubject($subject);

				/* Send the mail */
				if (!$mailer->Send())
				{
					JError::raiseWarning(0, JText::_('COM_REDFORM_NO_MAIL_SEND') . ' (to submitter)');
					RdfHelperLog::simpleLog(JText::_('COM_REDFORM_NO_MAIL_SEND') . ' (to submitter):' . $mailer->error);
				}
			}
		}

		return true;
	}

	/**
	 * Adds email from answers to mailing list
	 *
	 * @param   RdfAnswers  $answers  answers
	 *
	 * @return bool
	 */
	protected function updateMailingList(RdfAnswers $answers)
	{
		// Mailing lists management
		// Get info from answers
		$fullname  = $answers->getFullname() ? $answers->getFullname() : $answers->getUsername();
		$listnames = $answers->getListNames();

		JPluginHelper::importPlugin('redform_mailing');
		$dispatcher = JDispatcher::getInstance();

		foreach ((array) $listnames as $field_id => $lists)
		{
			$subscriber = new stdclass;
			$subscriber->name  = empty($fullname) ? $lists['email'] : $fullname;
			$subscriber->email = $lists['email'];

			$integration = $this->getMailingList($field_id);

			foreach ((array) $lists['lists'] as $mailinglistname)
			{
				$dispatcher->trigger('subscribe', array($integration, $subscriber, $mailinglistname));
			}
		}

		return true;
	}

	/**
	 * return mailing list integration name associated to field
	 *
	 * @param   int  $field_id  field id
	 *
	 * @return  string mailing list integrationname
	 */
	private function getMailingList($field_id)
	{
		$field = RdfRfieldFactory::getFormField($field_id);

		return $field->getParam('mailinglist');
	}

	/**
	 * Get form info
	 *
	 * @return mixed|object
	 */
	protected function getForm()
	{
		$model = $this->getFormModel();

		return $model->getForm();
	}

	/**
	 * Replace tags
	 *
	 * @param   string      $text     text
	 * @param   RdfAnswers  $answers  answers to use for substitution
	 *
	 * @return mixed
	 */
	private function replaceTags($text, RdfAnswers $answers)
	{
		$form = $this->getForm();
		$replacer = new RdfHelperTagsreplace($form, $answers);
		$text = $replacer->replace($text);

		return $text;
	}

	/**
	 * Get form fields
	 *
	 * @return array
	 */
	protected function getFields()
	{
		$model = $this->getFormModel();

		return $model->getFormFields();
	}

	/**
	 * Return Form model
	 *
	 * @return RdfCoreModelForm
	 */
	protected function getFormModel()
	{
		if (!$this->formModel)
		{
			$this->formModel = new RdfCoreModelForm($this->formId);
		}

		return $this->formModel;
	}
}
