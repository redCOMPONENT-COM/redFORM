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

require_once RDF_PATH_SITE.DS.'classes'.DS.'answers.php';

/**
 */
class RedformModelRedform extends JModel {
	
	var $_form_id = 0;
	
  var $_event = null;
  
  var $_form = null;
  
  var $_fields = null;
		
  var $mailer = null;
  
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
  	if (!$form_id) {
  		$form_id = $this->_form_id;
  	}

  	$db = & $this->_db;
  	
  	/* Load the fields */
  	$q = "SELECT id, field, fieldtype, ordering
        FROM ".$db->nameQuote('#__rwf_fields')."
        WHERE form_id = ".$form_id."
        AND published = 1
        ORDER BY ordering";
  	$db->setQuery($q);
  	$fields = $db->loadObjectList('id');
  	
  	foreach ($fields as $k =>$field)
  	{
  		$query = ' SELECT id, value, price FROM #__rwf_values WHERE field_id = '. $this->_db->Quote($field->id);
  		$this->_db->setQuery($query);
  		$fields[$k]->values = $this->_db->loadObjectList();
  	}
  	return $fields;
  }

	function getFormFields() 
	{		
		if (!$this->_form_id) {
			$this->setError(JText::_('COM_REDFORM_FORM_ID_MISSING'));
			return false;
		}
		$q = ' SELECT f.id, f.field, f.validate, f.tooltip, f.redmember_field, f.fieldtype, f.params, f.readonly, f.default, m.listnames '
		   . ' FROM #__rwf_fields AS f '
		   . ' LEFT JOIN #__rwf_mailinglists AS m ON f.id = m.field_id '
		   . ' WHERE f.published = 1 '
		   . ' AND f.form_id = '.$this->_form_id
		   . ' ORDER BY f.ordering'
		   ;
		$this->_db->setQuery($q);
		$fields = $this->_db->loadObjectList();
		
		foreach ($fields as $k => $field)
		{
			$paramsdefs = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redform' . DS . 'models' . DS . 'field_'.$field->fieldtype.'.xml';
			if (!empty($field->params) && file_exists($paramsdefs))
			{
				$fields[$k]->parameters = new JParameter( $field->params, $paramsdefs );
			}
			else {
				$fields[$k]->parameters = new JRegistry();
			}
		}
		return $fields;
	}
	
	function getFormValues($field_id) 
	{		
		$q = " SELECT id, value, label, field_id, price 
			FROM #__rwf_values
			WHERE published = 1
			AND field_id = ".$field_id."
			ORDER BY ordering";
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
	}
	
	/**
	 * Save a form
	 *
	 * @todo take field names from fields table
	 */
	function saveform() 
	{
		$mainframe = & JFactory::getApplication();
		$db = & $this->_db;
		
		/* Default values */
		$answer  = '';
		$return  = false;
		$redcompetition = false;
		$redevent = false;
		
		$event_task = JRequest::getVar('event_task');
		
		/* Check for the submit key */
		$submit_key = JRequest::getVar('submit_key', false);
		if (!$submit_key) $submit_key = uniqid();
		
		/* Get the form details */
		$form = $this->getForm(JRequest::getInt('form_id'));
		
		if ($form->captchaactive) 
		{
			/* Check if Captcha is correct */
			$word = JRequest::getVar('captchaword', false, '', 'CMD');
			$return = $mainframe->triggerEvent('onCaptcha_confirm', array($word, $return));
			
			if (!$return[0]) {
				$this->setError(JText::_('CAPTCHA_WRONG'));
	      $mainframe->enqueueMessage(JText::_('CAPTCHA_WRONG'));
	      return false;				
			}
		}
			
		/* Load the fields */
		$fieldlist = $this->getfields($form->id);
			
		/* Load the posted variables */
		$post = JRequest::get('post');
		$files = JRequest::get('files');
		$posted = array_merge($post, $files);
				
		if (JRequest::getInt('competition_id', false)) {
			$redcompetition = true;
			$event_id = JRequest::getInt('competition_id', 0);
			$post['xref'] = $event_id;
		}
		else $post['xref'] = 0;
		
		$event = null;
				
		/* Loop through the different forms */
		$totalforms = JRequest::getInt('curform');
		//if ($event_task == 'userregister') $totalforms--;
		/* Sign up minimal 1 */
		if ($totalforms == 0) $totalforms = 1;
		
		$allanswers = array();
		for ($signup = 1; $signup <= $totalforms; $signup++) 
		{
			// new answers object
			$answers = new rfanswers();
			$answers->setFormId($form->id);
			if ($event) {
				$answers->initPrice($event->course_price);
			}
			
			/* Create an array of values to store */
			$postvalues = array();
			// remove the _X parts, where X is the form (signup) number
			foreach ($posted as $key => $value) {
				if ((strpos($key, 'field') === 0) && (strpos($key, '_'.$signup, 5) > 0)) {
					$postvalues[str_replace('_'.$signup, '', $key)] = $value;
				}
			}

			/* Some default values needed */
			if (isset($post['xref'])) {
				$postvalues['xref'] = $post['xref'];
			}
			else {
				$postvalues['xref'] = 0;
			}
			$postvalues['form_id'] = $post['form_id'];
			$postvalues['submitternewsletter'] = JRequest::getVar('submitternewsletter', '');
			$postvalues['submit_key'] = $submit_key;
			
			if ($redcompetition)
			{
				$postvalues['integration'] = 'redcompetition';
			}
				
			/* Get the raw form data */
			$postvalues['rawformdata'] = serialize($posted);
			
			/* Build up field list */
			foreach ($fieldlist as $key => $field)
			{
				if (isset($postvalues['field'.$key]))
				{
					/* Get the answers */
					$answers->addPostAnswer($field, $postvalues['field'.$key]);
				}
			}
			
			// save answers
			if (!$answers->save($postvalues)) return false;		

			$this->updateMailingList($answers);

			$allanswers[] = $answers;
		} /* End multi-user signup */
		
		/* Load the mailer in case we need to inform someone */
		if ($form->submitterinform || $form->contactpersoninform) {
			$this->Mailer();
		}

		/* Send a submission mail to the submitters if set */
		if ($form->submitterinform) 
		{
			foreach ($allanswers as $answers)
			{
				$this->notifysubmitter($answers, $form);
			}
		}				
		
		// send email to miantainers
		if ($answers->isNew()) {
			$this->notifymaintainer($allanswers);
		}
					
		if ($form->activatepayment)
		{
			$redirect = 'index.php?option=com_redform&controller=payment&task=select&key='.$submit_key;
			$mainframe->redirect(JRoute::_($redirect, false));			
		}
			
		/* All is good, check if we have an competition in that case redirect to redCOMPETITION */
		if ($redcompetition)
		{
			$redirect = 'index.php?option=com_redcompetition&task='.JRequest::getVar('competition_task').'&competition_id='.JRequest::getInt('competition_id').'&submitter_id='.$allanswers[0]->getAnswerId().'&form_id='.JRequest::getInt('form_id');
			$mainframe->redirect(JRoute::_($redirect, false));
		}
		return array($form->submitnotification, $form->notificationtext);
	}
	
	/**
	 * Initialise the mailer object to start sending mails
	 */
	 private function Mailer() 
	 {
  		jimport('joomla.mail.helper');
	 	 if (empty($this->mailer))
	 	 {
			 $mainframe = & JFactory::getApplication();
			 jimport('joomla.mail.helper');
			 /* Start the mailer object */
			 $this->mailer = &JFactory::getMailer();
			 $this->mailer->isHTML(true);
			 $this->mailer->From = $mainframe->getCfg('mailfrom');
			 $this->mailer->FromName = $mainframe->getCfg('sitename');
			 $this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
	 	 }
	 	 return $this->mailer;
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
	 * See which attendees should be removed
	 */
	private function getConfirmAttendees() {
		$db = JFactory::getDBO();
		$attendees = JRequest::getVar('confirm', false);
		
		if ($attendees) {
			/* Get the ID's of setup attendees */
			$q = "SELECT id, answer_id, form_id
				FROM #__rwf_submitters
				WHERE submit_key = ".$db->Quote(JRequest::getVar('submit_key'));
			$db->setQuery($q);
			$attendees_ids = $db->loadObjectList();
			
			/* Check for attendees to be removed */
			foreach ($attendees_ids as $key => $attendee) {
				if (in_array($attendee->answer_id, $attendees)) {
					unset($attendees_ids[$key]);
				}
			}
			
			/* Remove the leftovers from the database */
			foreach ($attendees_ids as $key => $attendee) {
				$q = "DELETE FROM #__rwf_forms_".$attendee->form_id."
					WHERE id = ".$attendee->answer_id;
				$db->setQuery($q);
				$db->query();
				
				$q = "DELETE FROM #__rwf_submitters
					WHERE id = ".$attendee->id;
				$db->setQuery($q);
				$db->query();
			}
		}
		return true;
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
	
	function notifysubmitter($answers, $form)
	{
		$submitter_email = $answers->getSubmitterEmail();
		
		$mailer = & $this->Mailer();
		
		if (JMailHelper::isEmailAddress($submitter_email))
		{
			/* Add the email address */
			$mailer->AddAddress($submitter_email);
	
			/* Mail submitter */
			$submission_body = $form->submissionbody;
			if (strstr($submission_body, '[answers]')) 
			{
				$info = "<table>";
				foreach ($answers->getAnswers() as $answer) {
					$info .= "<tr>";
					$info .= "<th>". $answer['field'] ."</th>";
					if ($answer['type'] == 'file') {
          	$info .= "<td>". basename($answer['value']) ."</td>";
					}
					else {
          	$info .= "<td>". $answer['value'] ."</td>";
					}
          $info .= "</tr>";
				}
				$info .= "</table>";
				$submission_body = str_replace('[answers]', $info, $submission_body);
			}
			$htmlmsg = '<html><head><title>Welcome</title></title></head><body>'. $submission_body .'</body></html>';
			$mailer->setBody($htmlmsg);
			$mailer->setSubject($form->submissionsubject);
	
			/* Send the mail */
			if (!$mailer->Send()) {
				JError::raiseWarning(0, JText::_('NO_MAIL_SEND').' (to submitter)');
				RedformHelperLog::simpleLog(JText::_('NO_MAIL_SEND').' (to submitter):'.$mailer->error);
			}
			/* Clear the mail details */
			$mailer->ClearAddresses();
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
	 * Save a form
	 *
	 * @todo take field names from fields table
	 */
	function apisaveform($integration_key = '', $options = array(), $data = null) 
	{
		$mainframe = & JFactory::getApplication();
		$db = & $this->_db;
		
		$result = new stdclass(); // create a new class for this ?
		$result->posts = array();
		
		/* Default values */
		$answer  = '';
		$return  = false;
		$redcompetition = false;
		$redevent = false;
				
		/* Check for the submit key */
		$submit_key = JRequest::getVar('submit_key', false);
		if (!$submit_key) {
			$check_captcha = true;
			$submit_key = uniqid();
		}
		
		$result->submit_key = $submit_key;
		
		/* Get the form details */
		$form = $this->getForm(JRequest::getInt('form_id'));
		
		if ($form->captchaactive && $check_captcha) 
		{
			/* Check if Captcha is correct */
			$word = JRequest::getVar('captchaword', false, '', 'CMD');
			$return = $mainframe->triggerEvent('onCaptcha_confirm', array($word, $return));
			
			if (!$return[0]) {
				$this->setError(JText::_('CAPTCHA_WRONG'));
	      return false;				
			}
		}
			
		/* Load the fields */
		$fieldlist = $this->getfields($form->id);
			
		/* Load the posted variables */
		$post = $data ? $data : JRequest::get('post');
		$files = JRequest::get('files');
		$posted = array_merge($post, $files);
				
		// number of submitted active forms (min is 1)
		$totalforms = JRequest::getInt('curform') ? JRequest::getInt('curform') : 1;
		
		$allanswers = array();
		/* Loop through the different forms */
		for ($signup = 1; $signup <= $totalforms; $signup++) 
		{
			// new answers object
			$answers = new rfanswers();
			$answers->setFormId($form->id);
			if (isset($options['baseprice'])) {
				$answers->initPrice($options['baseprice']);
			}
			
			/* Create an array of values to store */
			$postvalues = array();
			// remove the _X parts, where X is the form (signup) number
			foreach ($posted as $key => $value) {
				if ((strpos($key, 'field') === 0) && (strpos($key, '_'.$signup, 5) > 0)) {
					$postvalues[str_replace('_'.$signup, '', $key)] = $value;
				}
			}
			if (isset($posted['submitter_id'.$signup])) {
				$postvalues['sid'] = intval($post['submitter_id'.$signup]);
			}

			$postvalues['form_id'] = $post['form_id'];
			$postvalues['submitternewsletter'] = JRequest::getVar('submitternewsletter', '');
			$postvalues['submit_key'] = $submit_key;
			
			$postvalues['integration'] = $integration_key;
				
			/* Get the raw form data */
			$postvalues['rawformdata'] = serialize($posted);
			
			/* Build up field list */
			foreach ($fieldlist as $key => $field)
			{
				if (isset($postvalues['field'.$key]))
				{
					/* Get the answers */
					$answers->addPostAnswer($field, $postvalues['field'.$key]);
				}
			}
			
			if (!isset($options['savetosession']))
			{
      	$res = $answers->savedata($postvalues);
	      if (!$res) {
	      	$this->setError(JText::_('REDFORM_SAVE_ANSWERS_FAILED'));
	      	return false;
	      }
	      else {
	      	$result->posts[] = array('sid' => $res);
	      }
	      
				if ($answers->isNew())
				{
					$this->updateMailingList($answers);
				}
			}			

			$allanswers[] = $answers;
		} /* End multi-user signup */
		
		// save to session if specified
		if (isset($options['savetosession'])) 
		{
			$sessiondata = array();
			foreach ($allanswers as $a) 
			{
				$sessiondata[] = $a->toSession();	
			}
			$mainframe->setUserState($submit_key, $sessiondata);
			return $result;
		}
		
		// send email to miantainers
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
	 * send email to form maintaineers or/and selected recipients
	 * @param array $allanswers
	 */
	function notifymaintainer($allanswers, $new = true)
	{
		$mainframe = &JFactory::getApplication();
		$form = $this->getForm();
						
		/* Inform contact person if need */
		// form recipients
		$recipients = $allanswers[0]->getRecipients();

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
			if ($user->get('id')) {
				$sender = array($user->email, $user->name);
			}
			else if ($allanswers[0]->getSubmitterEmail())
			{
				if ($allanswers[0]->getFullname()) {
					$sender = array($allanswers[0]->getSubmitterEmail(), $allanswers[0]->getFullname());
				}
				else {
					$sender = $allanswers[0]->getSubmitterEmail();
				}
			}
			else { // default to site settings
				$sender = array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename'));
			}
			$mailer->setSender($sender);
			$mailer->addReplyTo($sender);

			// set the email subject
			if ($new) {
				$mailer->setSubject(str_replace('[formname]', $form->formname, JText::_('CONTACT_NOTIFICATION_EMAIL_SUBJECT')));
			}
			else {
				$mailer->setSubject(str_replace('[formname]', $form->formname, JText::_('COM_REDFORM_CONTACT_NOTIFICATION_UPDATE_EMAIL_SUBJECT')));
			}
			
			// Mail body
			$htmlmsg = '<html><head><title></title></title></head><body>';
			if ($new) {
				$htmlmsg .= JText::sprintf('REDFORM_MAINTAINER_NOTIFICATION_EMAIL_BODY', $form->formname);
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
							default :
								$userinput = preg_replace($patterns, $replacements, $answer['value']);
								$htmlmsg .= '<tr><td>'.$answer['field'].'</td><td>';
								$htmlmsg .= str_replace('~~~', '<br />', $userinput);
								$htmlmsg .= '&nbsp;';
								$htmlmsg .= '</td></tr>'."\n";
								break;
						}
					}
					$htmlmsg .= "</table><br />";
				}
			}
			$htmlmsg .= '</body></html>';
			$mailer->setBody($htmlmsg);

			// send the mail
			if (!$mailer->Send()) {
				RedformHelperLog::simpleLog(JText::_('NO_MAIL_SEND').' (contactpersoninform): '.$mailer->error);;
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
				JErrorRaiseWarning(0, JText::_('Wrong parameters for redformcore getSidsAnswers'));
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
			Jerror::raiseWarning(0, JText::_('No submission for these sids'));
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
		return $form->notificationtext;		
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
}
?>