<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * redFORM model
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.html.parameter');

require_once RDF_PATH_SITE.DS.'classes'.DS.'answers.php';

/**
 */
class RedformModelRedform extends JModel {

	protected  $_form_id = 0;

	protected $_event = null;

	protected $_form = null;

	protected $_fields = null;

	protected $_answers = null;

	protected $mailer = null;

	protected $submit_key = null;

	function __construct()
	{
		parent::__construct();

		$this->setFormId(JRequest::getInt('form_id'));
	}

	/**
	 * Method to set the form identifier
	 *
	 * @access  public
	 * @param int event identifier
	 */
	function setFormId($id)
	{
		// Set event id and wipe data
		$this->_form_id = $id;
		$this->_form    = null;
		$this->_fields  = null;
	}

	public function setSubmitKey($key)
	{
		$this->submit_key = $key;
	}

	public function getSubmitKey()
	{
		return $this->submit_key;
	}

	/**
	 * returns form object
	 *
	 * @param int form id
	 * @return object form
	 */
	function &getForm($id=0)
	{
		if ($id) {
			$this->setFormId($id);
		}

		if (empty($this->_form))
		{
			/* Get the form details */
			$query = 'SELECT * FROM #__rwf_forms WHERE id = '. $this->_db->Quote($this->_form_id);
			$this->_db->setQuery($query, 0, 1);
			$this->_form = $this->_db->loadObject();
		}

		return $this->_form;
	}

	/**
	 * get the form fields name
	 *
	 * @param int $form_id
	 */
	function getfields($form_id = 0)
	{
		if (!$form_id)
		{
			$form_id = $this->_form_id;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__rwf_fields');
		$query->where('form_id = ' . $form_id);
		$query->order('ordering');

		$db->setQuery($query);
		$ids = $db->loadColumn();

		$fields = array();

		foreach ($ids as $fieldId)
		{
			$fields[] = RedformRfieldFactory::getField($fieldId);
		}

		return $fields;
	}

	function getFormFields()
	{
		if (!$this->_form_id)
		{
			$this->setError(JText::_('COM_REDFORM_FORM_ID_MISSING'));
			return false;
		}

		return $this->getfields($this->_form_id);
	}

	/**
	 * Initialise a mailer object to start sending mails
	 */
	private function Mailer()
	{
		$mainframe = & JFactory::getApplication();
		jimport('joomla.mail.helper');
		/* Start the mailer object */
		$mailer = &JFactory::getMailer();
		$mailer->isHTML(true);
		$mailer->From = $mainframe->getCfg('mailfrom');
		$mailer->FromName = $mainframe->getCfg('sitename');
		$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
		return $mailer;
	}

	/**
	 * Get the VirtueMart settings
	 */
	public function getVmSettings()
	{
		$db = JFactory::getDBO();
		$q = "SELECT virtuemartactive, vmproductid, vmitemid
		FROM #__rwf_forms
		WHERE id = ".JRequest::getInt('form_id');
		$db->setQuery($q);
		return $db->loadObject();
	}

	/**
	 * Retrieve the product details
	 */
	public function getProductDetails() {
		$db = JFactory::getDBO();
		$q = "SELECT product_full_image, product_name FROM #__vm_product WHERE product_id = ".JRequest::getInt('productid');
		$db->setQuery($q);
		return $db->loadObject();
	}

	/**
	 * returns event associated to xref
	 *
	 * @param int $xref
	 * @return object
	 */
	function getEvent($xref)
	{
		$db = JFactory::getDBO();
		$query = ' SELECT e.*, x.* '
		. ' FROM #__redevent_event_venue_xref AS x '
		. ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
		. ' WHERE x.id = '. $db->Quote((int) $xref)
		;
		$db->setQuery($query);
		return $db->loadObject();
	}

	/**
	 * Adds email from answers to mailing list
	 *
	 * @param rfanswers object
	 */
	function updateMailingList($rfanswers)
	{
		// mailing lists management
		// get info from answers
		$fullname  = $rfanswers->getFullname() ? $rfanswers->getFullname() : $rfanswers->getUsername();
		$listnames = $rfanswers->getListNames();

		JPluginHelper::importPlugin( 'redform_mailing' );
		$dispatcher =& JDispatcher::getInstance();

		foreach ((array) $listnames as $field_id => $lists)
		{
			$subscriber = new stdclass();
			$subscriber->name  = empty($fullname) ? $lists['email'] : $fullname;
			$subscriber->email = $lists['email'];

			$integration = $this->getMailingList($field_id);

			foreach ((array) $lists['lists'] as $listkey => $mailinglistname)
			{
				$results = $dispatcher->trigger( 'subscribe', array( $integration, $subscriber, $mailinglistname ) );
			}
		}
	}

	function notifysubmitter(rfanswers $answers, $form)
	{
		$emails = $answers->getSubmitterEmails();
		$cond_recipients = RedformCore::getConditionalRecipients($form, $answers);

		foreach ($emails as $submitter_email)
		{
			$mailer = & $this->Mailer();

			if ($cond_recipients)
			{
				$mailer->From = $cond_recipients[0][0];
				$mailer->FromName =  $cond_recipients[0][1];
				$mailer->ClearReplyTos();
				$mailer->addReplyTo($cond_recipients[0]);
			}

			if (JMailHelper::isEmailAddress($submitter_email))
			{
				/* Add the email address */
				$mailer->AddAddress($submitter_email);

				/* Mail submitter */
				$submission_body = $form->submissionbody;
				$submission_body = $this->_replaceTags($submission_body, $answers);

				if (strstr($submission_body, '[answers]'))
				{
					$info = "<table>";
					foreach ($answers->getAnswers() as $answer)
					{
						if (is_array($answer['value']))
						{
							$value = explode('<br>', $answer['value']);
						}
						else
						{
							$value = $answer['value'];
						}

						$info .= "<tr>";
						$info .= "<th>". $answer['field'] ."</th>";
						if ($answer['type'] == 'file') {
							$info .= "<td>". basename($value) ."</td>";
						}
						else {
							$info .= "<td>". $value ."</td>";
						}
						$info .= "</tr>";
					}

					if ($p = $answers->getPrice())
					{
						$info .= '<tr><th>'.JText::_('COM_REDFORM_TOTAL_PRICE').'</th><td>';
						$info .= $p;
						$info .= '</td></tr>'."\n";
					}

					$info .= "</table>";
					$submission_body = str_replace('[answers]', $info, $submission_body);
				}
				$htmlmsg = '<html><head><title>Welcome</title></title></head><body>'. $submission_body .'</body></html>';
				$mailer->setBody($htmlmsg);
				$mailer->setSubject($form->submissionsubject);

				/* Send the mail */
				if (!$mailer->Send()) {
					JError::raiseWarning(0, JText::_('COM_REDFORM_NO_MAIL_SEND').' (to submitter)');
					RedformHelperLog::simpleLog(JText::_('COM_REDFORM_NO_MAIL_SEND').' (to submitter):'.$mailer->error);
				}
			}
		}
	}

	function getFieldsValues()
	{
		$query = ' SELECT v.id, v.value, v.field_id, v.fieldtype '
		. '      , m.listnames '
		. '      , f.field, f.validate, f.unique, f.tooltip '
		. ' FROM #__rwf_values AS v '
		. ' INNER JOIN #__rwf_fields AS f ON v.field_id = f.id '
		. ' INNER JOIN #__rwf_forms AS fo ON fo.id = f.form_id '
		. ' LEFT JOIN  #__rwf_mailinglists AS m ON v.id = f.field_id '
		. ' WHERE v.published = 1 AND f.published = 1 AND fo.published = 1 '
		. '   AND fo.id = '.$this->_db->Quote($this->_form_id)
		. ' ORDER BY f.ordering, v.ordering '
		;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	function getFieldsInfo($fields_ids)
	{
		$quoted = array();
		foreach ($fields_ids as $id) {
			$quoted[] = $this->_db->Quote($id);
		}
		$query = ' SELECT f.id, f.field, f.validate, f.unique, f.tooltip, f.form_id '
		. ' FROM #__rwf_fields AS f '
		. ' INNER JOIN #__rwf_forms AS fo ON fo.id = f.form_id '
		. ' LEFT JOIN  #__rwf_mailinglists AS m ON v.id = m.id '
		. ' WHERE f.published = 1 AND fo.published = 1 '
		. '   AND f.id IN ('.implode(',', $quoted) .')'
		. ' ORDER BY f.ordering '
		;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
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
		$db = $this->_db;

		$result = new stdclass(); // create a new class for this ?
		$result->posts = array();

		// Check the token
		$token = JSession::getFormToken();

		// Get data from post if not specified
		if (!$data)
		{
			$data = array_merge(JRequest::get('post'), JRequest::get('files'));

			if (!isset($data['form_id']))
			{
				$data['form_id'] = $app->input->getInt('form_id');
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
			$this->setError('Form integrity check failed');
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
		$form = $this->getForm($data['form_id']);

		$currency = $data['currency'] ? $data['currency'] : $form->currency;

		/* Load the fields */
		$fieldlist = $this->getfields($form->id);

		// number of submitted active forms (min is 1)
		$totalforms = isset($data['nbactive']) ? $data['nbactive'] : 1;

		$allanswers = array();

		/* Loop through the different forms */
		for ($signup = 1; $signup <= $totalforms; $signup++)
		{
			// new answers object
			$answers = new rfanswers();
			$answers->setFormId($form->id);

			if (isset($data['submitter_id' . $signup]))
			{
				$answers->setSid(intval($data['submitter_id'.$signup]));
			}

			$answers->setFormId($data['form_id']);
			$answers->setSubmitKey($submit_key);
			$answers->setIntegration($integration_key);
			$answers->setCurrency($currency);

			if (isset($options['baseprice']))
			{
				if (is_array($options['baseprice']))
				{
					$answers->initPrice(isset($options['baseprice'][$signup-1]) ? $options['baseprice'][$signup-1] : 0);
				}
				else
				{
					$answers->initPrice($options['baseprice']);
				}
			}

			/* Create an array of values to store */
			$postvalues = array();

			// remove the _X parts, where X is the form (signup) number
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
						$clone = clone($field);
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

		$this->_answers = $allanswers;

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
				// Save to session
				$sessiondata = array();

				foreach ($allanswers as $a)
				{
					$sessiondata[] = $a->toSession();
				}

				$app->setUserState($submit_key, $sessiondata);

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
				$app->setUserState('formdata'.$form->id, null);
				$result->posts[] = array('sid' => $res);
			}

			if ($answers->isNew())
			{
				$this->updateMailingList($answers);
			}
		}

		// send email to maintainers
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
	 * @param   array   $fields       RedformRfield fields, with value set
	 * @param   string  $integration  integration key
	 * @param   array   $options      options: baseprice, currency
	 *
	 * @return boolean true on success
	 */
	public function quicksubmit($fields, $integration = null, $options = null)
	{
		$result = new stdclass();
		$result->posts = array();

		$submit_key = uniqid();
		$result->submit_key = $submit_key;

		$form = $this->getForm();

		// New answers object
		$answers = new rfanswers();
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

		// send email to maintainers
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
	 * send email to form maintaineers or/and selected recipients
	 * @param array $allanswers
	 */
	function notifymaintainer($allanswers, $new = true)
	{
		$mainframe = &JFactory::getApplication();
		$params = JComponentHelper::getParams('com_redform');
		$form = $this->getForm();

		/* Inform contact person if need */
		// form recipients
		$recipients = $allanswers[0]->getRecipients();
		$cond_recipients = RedformCore::getConditionalRecipients($form, $allanswers[0]);
		if ($cond_recipients)
		{
			foreach ($cond_recipients as $c) {
				$recipients[] = $c[0];
			}
		}

		if ($form->contactpersoninform || !empty($recipients))
		{
			// init mailer
			$mailer = &JFactory::getMailer();
			$mailer->isHTML(true);
			if ($form->contactpersoninform)
			{
				if (strstr($form->contactpersonemail, ';')) {
					$addresses = explode(";", $form->contactpersonemail);
				}
				else {
					$addresses = explode(",", $form->contactpersonemail);
				}
				foreach ($addresses as $a)
				{
					$a = trim($a);
					if (JMailHelper::isEmailAddress($a)) {
						$mailer->addRecipient($a);
					}
				}
			}

			if (!empty($recipients))
			{
				foreach ($recipients AS $r) {
					$mailer->addRecipient($r);
				}
			}
			if (!empty($xref_group_recipients))
			{
				foreach ($xref_group_recipients AS $r) {
					$mailer->addRecipient($r);
				}
			}

			// we put the submitter as the email 'from' and reply to.
			$user = & JFactory::getUser();
			if ($params->get('allow_email_aliasing', 1))
			{
				if ($user->get('id')) {
					$sender = array($user->email, $user->name);
				}
				else if ($allanswers[0]->getSubmitterEmails())
				{
					if ($allanswers[0]->getFullname()) {
						$sender = array(reset($allanswers[0]->getSubmitterEmails()), $allanswers[0]->getFullname());
					}
					else {
						$sender = array(reset($allanswers[0]->getSubmitterEmails()), null);
					}
				}
				else { // default to site settings
					$sender = array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename'));
				}
			}
			else { // default to site settings
				$sender = array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename'));
			}
			$mailer->setSender($sender);
			$mailer->addReplyTo($sender);

			// set the email subject
			$replaceHelper = new RedformHelperTagsreplace($form, $allanswers[0]->getAnswersByFieldId());

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
			if ($new) {
				$htmlmsg .= JText::sprintf('COM_REDFORM_MAINTAINER_NOTIFICATION_EMAIL_BODY', $form->formname);
			}
			else {
				$htmlmsg .= JText::sprintf('COM_REDFORM_MAINTAINER_NOTIFICATION_UPDATE_EMAIL_BODY', $form->formname);
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
								$htmlmsg .= '<tr><td>'.$answer['field'].'</td><td>';
								$htmlmsg .= '<a href="mailto:'.$answer['value'].'">'.$answer['value'].'</a>';
								$htmlmsg .= '&nbsp;';
								$htmlmsg .= '</td></tr>'."\n";
								break;
							case 'text':
								$userinput = preg_replace($patterns, $replacements, $answer['value']);
								$htmlmsg .= '<tr><td>'.$answer['field'].'</td><td>';
								if (strpos($answer['value'], 'http://') === 0) {
									$htmlmsg .= '<a href="'.$answer['value'].'">'.$answer['value'].'</a>';
								}
								else {
									$htmlmsg .= $answer['value'];
								}
								$htmlmsg .= '&nbsp;';
								$htmlmsg .= '</td></tr>'."\n";
								break;
							case 'file':
								$userinput = preg_replace($patterns, $replacements, $answer['value']);
								$htmlmsg .= '<tr><td>'.$answer['field'].'</td><td>';
								$htmlmsg .= ($answer['value'] && file_exists($answer['value'])) ? basename($answer['value']) : '';
								$htmlmsg .= '</td></tr>'."\n";
								// attach to mail
								if ($answer['value'] && file_exists($answer['value'])) {
									$mailer->addAttachment($answer['value']);
								}
								break;
							default :
								$userinput = preg_replace($patterns, $replacements, $answer['value']);
								$htmlmsg .= '<tr><td>'.$answer['field'].'</td><td>';
								$htmlmsg .= str_replace('~~~', '<br />', $userinput);
								$htmlmsg .= '&nbsp;';
								$htmlmsg .= '</td></tr>'."\n";
								break;
						}
					}
					if ($p = $answers->getPrice())
					{
						$htmlmsg .= '<tr><td>'.JText::_('COM_REDFORM_TOTAL_PRICE').'</td><td>';
						$htmlmsg .= $p;
						$htmlmsg .= '</td></tr>'."\n";
					}
					$htmlmsg .= "</table><br />";
				}
			}
			$htmlmsg .= '</body></html>';
			$mailer->setBody($htmlmsg);

			// send the mail
			if (!$mailer->Send()) {
				RedformHelperLog::simpleLog(JText::_('COM_REDFORM_NO_MAIL_SEND').' (contactpersoninform): '.$mailer->error);
			}
		}
	}

	/**
	 * return answers of specified sids
	 *
	 * @param array int $sids
	 * @return array
	 */
	function getSidsAnswers($sids)
	{
		if (empty($sids)) {
			return false;
		}

		if (!is_array($sids))
		{
			if (is_int($sids))
			{
				$ids = $sids;
			}
			else {
				JErrorRaiseWarning(0, JText::_('COM_REDFORM_WRONG_PARAMETERS_FOR_REDFORMCORE_GETSIDSANSWERS'));
				return false;
			}
		}
		else {
			$ids = implode(',', $sids);
		}

		// we need the form_id...
		$query = ' SELECT s.form_id '
		. ' FROM #__rwf_submitters AS s '
		. ' WHERE s.id IN ('.$ids.')'
		;
		$this->_db->setQuery($query, 0, 1);
		$form_id = $this->_db->loadResult();

		if (!$form_id) {
			Jerror::raiseWarning(0, JText::_('COM_REDFORM_No_submission_for_these_sids'));
			return false;
		}

		$query = ' SELECT s.id as sid, f.*, s.price, p.paid, p.status '
		. ' FROM #__rwf_forms_'.$form_id.' AS f '
		. ' INNER JOIN #__rwf_submitters AS s on s.answer_id = f.id '
		. ' LEFT JOIN #__rwf_payment AS p on s.submit_key = p.submit_key '
		. ' WHERE s.id IN ('.$ids.')';
		;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList('sid');

		$answers = array();
		foreach ($res as $k =>$r)
		{
			$answers[$k] = $r;
		}
		return $answers;
	}

	function getRedirect()
	{
		$form = $this->getForm();

		if (!empty($form->redirect)) {
			return $form->redirect;
		}
		return false;
	}

	function getNotificationText()
	{
		$form = $this->getForm();

		if (empty($this->_answers)) {
			return $form->notificationtext;
		}
		else {
			return $this->_replaceTags($form->notificationtext, reset($this->_answers));
		}
	}

	/**
	 * return mailing list instegration name associated to field
	 * @param int field id
	 * @return string mailing list integrationname
	 */
	function getMailingList($field_id)
	{
		$query = ' SELECT mailinglist '
		. ' FROM #__rwf_mailinglists  '
		. ' WHERE field_id = ' . $this->_db->Quote($field_id)
		;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		return $res;
	}

	function _replaceTags($text, rfanswers $answers)
	{
		// Price
		if (strstr($text, '[totalprice]'))
		{
			$text = str_replace('[totalprice]', $answers->getPrice(), $text);
		}

		$matches = array();
		if (!preg_match_all('(\[answer_[0-9]+\])', $text, $matches)) {
			return $text;
		}

		foreach ($matches[0] as $tag)
		{
			// get field id from tag
			$id = substr($tag, 8, -1);

			foreach ($answers->getAnswers() as $field)
			{
				if ($field['field_id'] == $id)
				{
					$text = str_replace($tag, $field['value'], $text);
					break;
				}
			}
		}

		return $text;
	}

	function hasActivePayment($key)
	{
		$query = ' SELECT s.price '
		. ' FROM #__rwf_submitters AS s '
		. ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
		. ' LEFT JOIN #__rwf_payment AS p ON s.submit_key = p.submit_key AND p.paid = 1 '
		. ' WHERE s.submit_key = ' . $this->_db->Quote($key)
		. '   AND p.id IS NULL '
		. '   AND f.activatepayment = 1 '
		;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		if (!$res) {
			return false;
		}
		else {
			return true;
		}
	}
}
