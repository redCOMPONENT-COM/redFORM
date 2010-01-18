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

require_once JPATH_COMPONENT.DS.'classes'.DS.'answers.php';

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
  function &getForm($id)
  {
  	if (!$id) {
  		$id = JRequest::getInt('form_id', $this->_form_id);
  	}
    /* Get the form details */
  	$query = 'SELECT * FROM #__rwf_forms WHERE id = '. $this->_db->Quote($id);
  	$this->_db->setQuery($query, 0, 1);
  	$form = $this->_db->loadObject();
    
    if ($form) {
    	$this->_form = $form;
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
		if (!$submit_key) $submit_key = md5(uniqid());
		
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
		
		if (isset($posted['submit']['cancelreg'])) 
		{
			if ($event_task == 'edit')
			{			
				if (JRequest::getInt('xref', false)) {
					$redirect = 'index.php?option=com_redevent&view=details&xref='.JRequest::getInt('xref');
					$mainframe->redirect($redirect, JText::_('Operation cancelled'));
				}
				else return true;			
			}
			else if ($event_task == 'manageredit')
			{
				if (JRequest::getInt('xref', false)) {
					$redirect = 'index.php?option=com_redevent&view=details&tpl=manage_attendees&xref='.JRequest::getInt('xref');
					$mainframe->redirect($redirect, JText::_('Operation cancelled'));
				}
				else return true;
			}
		}
		
		/* See if we have an event ID */
		if (JRequest::getInt('event_id', false)) {
			$redevent = true;
			$event_id = JRequest::getInt('event_id', 0);
			$posted['xref'] = $event_id;
		}
		else if (JRequest::getInt('competition_id', false)) {
			$redcompetition = true;
			$event_id = JRequest::getInt('competition_id', 0);
			$post['xref'] = $event_id;
		}
		else $post['xref'] = 0;
		
		if ($post['xref'] && $redevent) {
			$event = $this->getEvent($post['xref']);
		}
		else {
			$event = null;
		}
				
		/* Loop through the different forms */
		$totalforms = JRequest::getInt('curform');
		if ($event_task == 'userregister') $totalforms--;
		/* Sign up minimal 1 */
		if ($totalforms == 0) $totalforms = 1;
		
		$allanswers = array();
		for ($signup = 1; $signup <= $totalforms; $signup++) 
		{
			// new answers object
			$answers = new rfanswers();
			$answers->setFormId($form->id);
			$answers->initPrice($event->course_price);
			
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
			
			if ($event_task == 'review' || $event_task == 'edit' || $event_task == 'manageredit')
			{
				if (isset($posted['confirm'][($signup-1)])) 
				{
					// this 'anwers' were already posted
					$answers->setAnswerId($posted['confirm'][($signup-1)]);
					// update answers
					if (!$answers->save($postvalues)) return false;
				}
			}
			else {
				// save answers
				if (!$answers->save($postvalues)) return false;		
			}

			/* Clean up any signups that need to be removed */
			$this->getConfirmAttendees();


			if ( empty($event) || JRequest::getVar('event_task') == 'review' || empty($event->review_message))
			{
				$this->updateMailingList($answers);
			}

			$allanswers[] = $answers;
		} /* End multi-user signup */
		
		// send the notifications mails if not a redevent registration, or if this is the review, or if there is no review
		if ((empty($event) || $event_task == 'review' || empty($event->review_message))
		    && $event_task != 'edit' && $event_task != 'manageredit')
		{
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
				
			/* Inform contact person if need */
			// form recipients
			$recipients = $allanswers[0]->getRecipients();
			
			// in case of an event, xref group recipients
			if ($redevent)
			{
				$query = ' SELECT u.email '
							 . ' FROM #__redevent_event_venue_xref AS x '
							 . ' INNER JOIN #__redevent_groups AS g ON x.groupid = g.id '
							 . ' INNER JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
							 . ' INNER JOIN #__users AS u ON gm.member = u.id '
							 . ' WHERE x.id = '. $this->_db->Quote(JRequest::getInt('xref'))
							 . '   AND gm.receive_registrations = 1 '
							 ;
				$db->setQuery($query);
				$xref_group_recipients = $db->loadResultArray();
			}
			else {				
				$xref_group_recipients = array();
			}
			
			if ($form->contactpersoninform || !empty($recipients) || !empty($xref_group_recipients)) 
			{
			  // init mailer
			  $mailer = &JFactory::getMailer();
			  $mailer->isHTML(true);
			  if ($form->contactpersoninform) {
  			  $mailer->addRecipient($form->contactpersonemail);
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
				/* Get the event details */
				$eventname = '';
				if ($redevent) 
				{
					$q = "SELECT x.id, title, v.venue, x.dates, x.times
							FROM #__redevent_events e
							INNER JOIN #__redevent_event_venue_xref x ON x.eventid = e.id
							INNER JOIN #__redevent_venues as v ON x.venueid = v.id
							WHERE x.id = ".JRequest::getInt('xref');
					$db->setQuery($q);
					$res = $db->loadObject();
					$eventname = $res->title;
					
					$venue = $res->venue;
					
					if ($res->dates && $res->dates != '0000-00-00') {
						$startdate = $res->dates;
					}
					else {
						$startdate = JText::_('Open date');
					}
					
					if ($res->times && $res->times != '00:00:00') {
						$starttime = substr($res->times, 0, 5);
					}
					else {
						$starttime = '';
					}
					
					$tags = array('[formname]', '[eventname]', '[startdate]', '[starttime]', '[venuename]');
					$values = array($form->formname, $eventname, $startdate, $starttime, $venue);
					$mailer->setSubject(str_replace($tags, $values, JText::_('CONTACT_NOTIFICATION_EMAIL_SUBJECT_WITH_EVENT')));
				}
				else {
					$mailer->setSubject(str_replace('[formname]', $form->formname, JText::_('CONTACT_NOTIFICATION_EMAIL_SUBJECT')));
				}
				
				// Mail body
				$htmlmsg = '<html><head><title></title></title></head><body>';
				$htmlmsg .= JText::_('A new submission has been received.');
				$htmlmsg .= $form->notificationtext;
				
				/* Add user submitted data if set */
				if ($form->contactpersonfullpost) 
				{
					if (JRequest::getInt('productid', false)) 
					{
						$productdetails = $this->getProductDetails();
						if (!stristr('http', $productdetails->product_full_image)){
							$productimage = JURI::root().'/components/com_virtuemart/shop_image/product/'.$productdetails->product_full_image;
						}
						else $productimage = $productdetails->product_full_image;
						$htmlmsg .= '<div id="productimage">'.JHTML::_('image', $productimage, $productdetails->product_name).'</div>';
						$htmlmsg .= '<div id="productname">'.$productdetails->product_name.'</div>';
					}
										
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
              if ($answer['type'] != 'recipients') // those are used for the mail recipients
              {
                $userinput = preg_replace($patterns, $replacements, $answer['value']);
                $htmlmsg .= '<tr><td>'.$answer['field'].'</td><td>';
                $htmlmsg .= str_replace('~~~', '<br />', $userinput);
                $htmlmsg .= '&nbsp;';
                $htmlmsg .= '</td></tr>'."\n";
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
			
		/* All is good, check if we have an event in that case redirect to redEVENT */
		if ($redevent) 
		{
			if ($event_task == 'edit')
			{			
				if (JRequest::getInt('xref', false)) {
					$redirect = 'index.php?option=com_redevent&view=details&xref='.JRequest::getInt('xref');
					$mainframe->redirect($redirect, JText::_('Registration updated'));
				}
				else return true;			
			}
			else if ($event_task == 'manageredit')
			{
				if (JRequest::getInt('xref', false)) {
					$redirect = 'index.php?option=com_redevent&view=details&tpl=manage_attendees&xref='.JRequest::getInt('xref');
					$mainframe->redirect($redirect, JText::_('Registration updated'));
				}
				else return true;
			}
			else
			{
				$redirect = 'index.php?option=com_redevent&view=confirmation&task='
						.JRequest::getVar('event_task')
						.'&xref='.JRequest::getInt('xref')
						.'&submit_key='.$submit_key
						.'&form_id='.JRequest::getInt('form_id');
				// go to final if this was the review screen, or if there is no review screen
				if (JRequest::getVar('event_task') == 'review' || empty($event->review_message)) 
				{
					$redirect .= '&page=final';
					$submit = JRequest::getVar('submit');
					if (!is_array($submit)) settype($submit, 'array');
					$arkeys = array_keys($submit);
					$redirect .= '&action='.$arkeys[0];
				}
				else {
					$redirect .= '&page=confirmation&event_task=review';
					if (strtolower(JRequest::getVar('submit')) == strtolower(JText::_('SUBMIT_AND_PRINT'))) $redirect .= '&action=print';
				}
				if ($form->virtuemartactive) {
					$redirect .= '&redformback=1';
				}
				$mainframe->redirect(JRoute::_($redirect, false));
			}
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
    $db = JFactory::getDBO();
     
	 	// mailing lists management
	 	// get info from answers
	 	$fullname = $rfanswers->getFullname();
	 	$submitter_email = $rfanswers->getSubmitterEmail();
	 	$listnames = $rfanswers->getListNames();

	 	foreach ((array) $listnames as $key => $alllistname)
	 	{
	 		foreach ((array) $alllistname as $listkey => $mailinglistname)
	 		{
	 			/* Check if we have a fullname */
	 			if (!isset($fullname)) $fullname = $submitter_email;
	 			/* Check if mailinglist integration is enabled */
	 			if ($submitter_email) {
	 				/* Check to which  mailinglist user should be added */
	 				$q = "SELECT name, value
                  FROM #__rwf_configuration
                  WHERE name in ('use_phplist', 'use_acajoom', 'use_ccnewsletter', 'phplist_path')";
	 				$db->setQuery($q);
	 				$configuration = $db->loadObjectList('name');

	 				/* Add the user to ccNewsletter */
	 				if (isset($configuration['use_ccnewsletter']) && $configuration['use_ccnewsletter']->value) {
	 					/* Check if ccNewsletter is installed */
	 					$q = "SELECT COUNT(id) FROM #__components WHERE link = 'option=com_ccnewsletter'";
	 					$db->setQuery($q);

	 					if ($db->loadResult() > 0) {
	 						/* ccNewsletter is installed, let's add the user */
	 						$this->addTablePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_ccnewsletter' . DS . 'tables' );
	 						$ccsubscriber = $this->getTable('subscriber');
	 						$ccsettings = array('name' => $fullname,
                              'email' => $submitter_email,
                              'plainText' => '0',
                              'enabled' => '1',
                              'sdate' => date('Y-m-d H:i:s'));
	 						$ccsubscriber->bind($ccsettings);
	 						$ccsubscriber->store();
	 					}

	 				}

	 				/* Add the user to Acajoom */
	 				if (isset($configuration['use_acajoom']) && $configuration['use_acajoom']->value) {
	 					/* Check if Acajoom is installed */
	 					$q = "SELECT COUNT(id) FROM #__components WHERE link = 'option=com_acajoom'";
	 					$db->setQuery($q);

	 					if ($db->loadResult() > 0) {
	 						/* Acajoom is installed, let's add the user */
	 						$acajoomsubscriber = $this->getTable('acajoom_subscribers');
	 						$myid = JFactory::getUser();
	 						if (!isset($myid->id)) $myid->id = 0;
	 						$acajoomsettings = array('user_id' => $myid->id,
                              'name' => $fullname,
                              'email' => $submitter_email,
                              'subscribe_date' => date('Y-m-d H:i:s'));
	 						$acajoomsubscriber->bind($acajoomsettings);
	 						if (!$acajoomsubscriber->store()) {
	 							if (stristr($db->getErrorMsg(), 'duplicate entry')) {
	 								$mainframe->enqueueMessage(JText::_('This e-mail address is already signed up for the newsletter'), 'error');
	 							}
	 							else $mainframe->enqueueMessage(JText::_('There was a problem signing up for the newsletter').' '.$db->getErrorMsg(),'error');
	 						}

	 						/* Check if the mailinglist exists, add the user to it */
	 						$list = false;
	 						$q = "SELECT id, acc_id FROM #__acajoom_lists WHERE list_name = ".$db->Quote($mailinglistname)." LIMIT 1";
	 						$db->setQuery($q);
	 						$list = $db->loadObject();

	 						if ($db->getAffectedRows() > 0) {
	 							/* Load the queue table */
	 							$acajoomqueue = $this->getTable('acajoom_queue');

	 							/* Collect subscriber details */
	 							$queue = new stdClass;
	 							$queue->id = 0;
	 							$queue->subscriber_id = $acajoomsubscriber->id;
	 							$queue->list_id = $list->id;
	 							$queue->type = 1;
	 							$queue->mailing_id = 0;
	 							$queue->send_date = '0000-00-00 00:00:00';
	 							$queue->suspend = 0;
	 							$queue->delay = 0;
	 							$queue->acc_level = $list->acc_id;
	 							$queue->issue_nb = 0;
	 							$queue->published = 0;
	 							$queue->params = '';

	 							$acajoomqueue->bind($queue);
	 							$acajoomqueue->store();
	 						}
	 					}
	 				}

	 				/* Add the user to PHPList */
	 				if (isset($configuration['use_phplist']) && $configuration['use_phplist']->value && !empty($mailinglistname)) {
	 					if (JFolder::exists(JPATH_SITE.DS.$configuration['phplist_path']->value)) {
	 						/* Include the PHPList API */
	 						require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'phplistuser.php');
	 						require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'simpleemail.php');
	 						require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'query.php');
	 						require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'errorhandler.php');

	 						/* Get the PHPList path configuration */
	 						PhpListUser::$PHPListPath = JPATH_SITE.DS.$configuration['phplist_path']->value;

	 						$user = new PhpListUser();
	 						$user->set_email($submitter_email);
	 						$listid = $user->getListId($mailinglistname);
	 						$user->addListId($listid);
	 						$user->save();
	 					}
	 				}
	 			}
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
			if (strstr($submission_body, '[info]')) 
			{
				$info = "<table>";
				foreach ($answers->getAnswers() as $answer) {
					$info .= "<tr>";
					$info .= "<th>". $answer['field'] ."</th>";
          $info .= "<td>". $answer['value'] ."</td>";
          $info .= "</tr>";
				}
				$info .= "</table>";
				$submission_body = str_replace('[info]', $info, $submission_body);
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
}
?>