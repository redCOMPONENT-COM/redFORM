<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.application.component.model' );

/**
 */
class RedformModelSubmitters extends JModel {
	/** @var integer Total entries */
	protected $_total = null;
	
	/** @var integer pagination limit starter */
	protected $_limitstart = null;
	
	/** @var integer pagination limit */
	protected $_limit = null;
	   
	/**
	 * Show all orders for which an invitation to fill in
	 * a testimonal has been sent
	 */
	function getSubmitters() {
		$form_id = JRequest::getVar('form_id', false);
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
		
		$db = JFactory::getDBO();
		
		if ($form_id && $form_id > 0) {
			$query = "SELECT s.submission_date, f.formname, u.*
			FROM ".$db->nameQuote('#__rwf_submitters')." s, ".$db->nameQuote('#__rwf_forms')." f, ".$db->nameQuote('#__rwf_forms_'.$form_id)." u
				WHERE s.form_id = f.id 
				AND s.answer_id = u.id ";
			if ($form_id && $form_id > 0) {
				$query .= "AND s.form_id = ".$form_id." ";
			}
			if ($xref && $xref > 0) {
				$query .= "AND s.xref = ".$xref." ";
			}
			$query .= "ORDER BY s.submission_date DESC";
			
			$db->setQuery($query,  $this->_limitstart, $this->_limit);
			if ($db->getErrorNum() > 0) return array();
			else return $db->loadObjectList();
		}
	}
	
	/**
	 * Show all orders for which an invitation to fill in
	 * a testimonal has been sent
	 */
	function getSubmittersExport() {
		$form_id = JRequest::getVar('form_id', false);
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
		
		$db = JFactory::getDBO();
		$query = "SELECT s.submission_date, f.formname, u.*
			FROM ".$db->nameQuote('#__rwf_submitters')." s, ".$db->nameQuote('#__rwf_forms')." f, ".$db->nameQuote('#__rwf_forms_'.$form_id)." u
				WHERE s.form_id = f.id 
				AND s.answer_id = u.id ";
			if ($form_id && $form_id > 0) {
				$query .= "AND s.form_id = ".$form_id." ";
			}
			if ($xref && $xref > 0) {
				$query .= "AND s.xref = ".$xref." ";
			}
			$query .= "ORDER BY s.submission_date DESC";
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getFields() {
		$db = JFactory::getDBO();
		$form_id = JRequest::getInt('form_id', false);
		$query = "SELECT LOWER(REPLACE(".$db->nameQuote('field').", ' ','')) AS ".$db->nameQuote('field')."
			FROM #__rwf_fields ";
		if ($form_id) $query .= "WHERE form_id = ".$form_id." ";
		$query .= "GROUP BY field
				ORDER BY ordering ";
		$db->setQuery($query);
		return $db->loadResultArray();
	}
	
	function getPagination() {
		global $mainframe, $option;
		
		/* Lets load the pagination if it doesn't already exist */
		jimport('joomla.html.pagination');
		$this->_limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$this->_limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$this->_pagination = new JPagination( $this->getTotal(), $this->_limitstart, $this->_limit );
		return $this->_pagination;
	}
	
	/**
	 * Method to get the total number of testimonial items for the category
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal() {
		$form_id = JRequest::getVar('form_id', false);
		$xref = JRequest::getVar('xref', false);
		$filter = JRequest::getVar('filter', false);
		$db = JFactory::getDBO();
		
		if ($form_id && $form_id > 0) {
			$query = "SELECT s.submission_date, f.formname, u.*
			FROM ".$db->nameQuote('#__rwf_submitters')." s, ".$db->nameQuote('#__rwf_forms')." f, ".$db->nameQuote('#__rwf_forms_'.$form_id)." u
				WHERE s.form_id = f.id 
				AND s.answer_id = u.id ";
			if ($form_id) {
				$query .= "AND s.form_id = ".$form_id." ";
			}
			if ($xref) {
				$query .= "AND s.xref = ".$xref." ";
			}
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0) return 0;
			else return $db->getAffectedRows();
		}
	}
	
	/**
	 * Get the number of people signed up for the newsletter
	 */
	public function getNewsletterSignup() {
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		$query = "SELECT COUNT(id)
				FROM #__rwf_submitters
				WHERE form_id = ".$cid[0]."
				AND submitternewsletter = 1";
		$db->setQuery($query);
		if ($db->getErrorNum() > 0) return 0;
		else return $db->loadResult();
	}
	
	/**
    * Deletes one or more submitters
    */
   function getRemoveSubmitter() 
   {
      global $mainframe;
      $database = JFactory::getDBO();
      $cid = JRequest::getVar('cid');
      JArrayHelper::toInteger( $cid );
	  
      if (!is_array( $cid ) || count( $cid ) < 1) {
         $mainframe->enqueueMessage(JText::_('No submitter found to delete'));
         return false;
      }
      if (count($cid)) {
      	$cids = ' answer_id IN (' . implode( ',', $cid ) .') ';

      	// first, check that there is no eventlist registrations among these 'submitter'
      	$query = ' SELECT COUNT(*) FROM #__rwf_submitters WHERE ' . $cids . ' AND xref > 0 ';
        $database->setQuery( $query );
        $res = $database->loadResult();        
        if ($res) {
        	$msg = JText::_('CANNOT DELETE REDEVENT REGISTRATION');
        	$this->setError($msg);
        	JError::raiseWarning(0, $msg);
        	return false;
        }
      	
      	/* Delete the submitters */
      	$query = ' DELETE FROM #__rwf_submitters '
      	      . ' WHERE ' . $cids
      	      . '	AND form_id = '.JRequest::getInt('form_id');
      	$database->setQuery( $query );
      	
      	if (!$database->query()) {
      		$mainframe->enqueueMessage(JText::_('A problem occured when deleting the submitter'));
      	}
      	else {
      		/* Delete the submitters values */
      		$cids = 'id=' . implode( ' OR id=', $cid );
      		$query = "DELETE from #__rwf_forms_".JRequest::getInt('form_id')."
					WHERE (".$cids.")";
      		$database->setQuery($query);
      		if (!$database->query()) {
      			$mainframe->enqueueMessage(JText::_('A problem occured when deleting the submitter values'));
      		}
      		else {
      			if (count($cid) > 1) $mainframe->enqueueMessage(JText::_('Submitters have been deleted'));
      			else $mainframe->enqueueMessage(JText::_('Submitter has been deleted'));
      		}
      	}
      }
   	}
	
	/**
	 * Get the value for the submitter field
	 */
	private function QuestionValue(&$value_id) {
		$db = JFactory::getDBO();
		$q = "SELECT value
			FROM #__rwf_values
			WHERE id = ".$value_id;
		$db->setQuery($q);
		return $db->loadResult();
	}
	
	/**
	 * Gets the details of a single submitter
	 */
	public function getSubmitter() {
		$form_id = JRequest::getVar('form_id', false);
		$cid = JRequest::getVar('cid');
		$db = JFactory::getDBO();
		if ($form_id && $form_id > 0) {
			$query = "SELECT *
				FROM ".$db->nameQuote('#__rwf_forms_'.$form_id)." f
				LEFT JOIN #__rwf_submitters s
				ON f.id = s.answer_id
				WHERE f.id = ".$cid[0]; 
			$db->setQuery($query);
			return $db->loadObjectList();
		}
	}
	
	/**
	 * Saves an edited submitter
	 */
	public function getSaveSubmitter() {
		global $mainframe;
		
		/* Database connection */
		$db = JFactory::getDBO();
		
		/* Default values */
		$qfields = '';
		$qpart = '';
		$answer = '';
		$return = false;
		/* Check for the submit key */
		$submit_key = JRequest::getVar('submit_key', false);
		if (!$submit_key) $submit_key = md5(uniqid());
		
		/* Get the form details */
		$form = $this->getTable('Redform');
		$form->load(JRequest::getInt('form_id'));
		
		/* Load the fields */
		$q = "SELECT id, LOWER(REPLACE(".$db->nameQuote('field').", ' ','')) AS ".$db->nameQuote('field').", field AS userfield, ordering
			FROM ".$db->nameQuote('#__rwf_fields')."
			WHERE form_id = ".$form->id."
			AND published = 1
			ORDER BY ordering";
		$db->setQuery($q);
		$fieldlist = $db->loadObjectList('id');
		
		/* Load the posted variables */
		$posted = JRequest::get('post');
		
		/* See if we have an event ID */
		if (JRequest::getInt('event_id', false)) {
			$event_id = JRequest::getInt('event_id', 0);
			$posted['event_id'] = $event_id;
		}
		else $posted['event_id'] = 0;
		
		/* Create an array of values to store */
		$postvalues = array();
		$signup = 1;
		foreach ($posted as $key => $value) {
			if ((strpos($key, 'field') === 0) && (strpos($key, '_'.$signup, 5) > 0)) {
				$postvalues[str_replace('_'.$signup, '', $key)] = $value;
			}
		}
		
		/* Some default values needed */
		$postvalues['xref'] = $posted['xref'];
		$postvalues['form_id'] = $posted['form_id'];
		$postvalues['submitternewsletter'] = JRequest::getVar('submitternewsletter', '');
		$postvalues['submit_key'] = $submit_key;
		
		/* Get the raw form data */
		$postvalues['rawformdata'] = serialize($postvalues);
		
		/* Clear the field and answer lists */
		$qfields = '';
		$qpart = '';
		
		/* Build up field list */
		foreach ($fieldlist as $key => $field) {
			if (isset($postvalues['field'.$key])) {
				$qfields .= '`'.$field->field.'`,';
				/* Get the answers */
				if (isset($postvalues['field'.$key]['radio'])) {
					/* Get the real value from the database */
					$q = "SELECT value
						FROM #__rwf_values
						WHERE id = ".$postvalues['field'.$key]['radio'][0];
					$db->setQuery($q);
					$answer = $db->loadResult();
				}
				else if (isset($postvalues['field'.$key]['textarea'])) $answer = $postvalues['field'.$key]['textarea'];
				else if (isset($postvalues['field'.$key]['fullname'])) $answer = $fullname = $postvalues['field'.$key]['fullname'][0];
				else if (isset($postvalues['field'.$key]['username'])) $answer = $postvalues['field'.$key]['username'][0];
				else if (isset($postvalues['field'.$key]['email'])) $answer = $submitter_email = $postvalues['field'.$key]['email'][0];
				else if (isset($postvalues['field'.$key]['text'])) $answer = $postvalues['field'.$key]['text'][0];
				else if (isset($postvalues['field'.$key]['select'])) $answer = $postvalues['field'.$key]['select'][0];
				else if (isset($postvalues['field'.$key]['checkbox'])) {
					$submittervalues = '';
					foreach ($postvalues['field'.$key]['checkbox'] as $key => $submitteranswer) {
						$submittervalues .= $submitteranswer."~~~";
					}
					$answer = substr($submittervalues, 0, -3);
				}
				else if (isset($postvalues['field'.$key]['multiselect'])) {
					$submittervalues = '';
					foreach ($postvalues['field'.$key]['multiselect'] as $key => $submitteranswer) {
						$submittervalues .= $submitteranswer."~~~";
					}
					$answer = substr($submittervalues, 0, -3);
				}
				else $answer = '';
				$qpart .= $db->Quote($answer).',';
			}
		}
		
		if (JRequest::getVar('event_task') == 'review') {
			/* Updating the values */
			$ufields = explode(',', substr($qfields, 0, -1));
			$upart = explode(',', substr($qpart, 0, -1));
			
			$q = "UPDATE ".$db->nameQuote('#__rwf_forms_'.$form->id)."
				SET ";
			foreach ($ufields as $ukey => $field) {
				$q .= trim($field)." = ".trim($upart[$ukey]).", ";
			}
			$q = substr($q, 0, -2)." WHERE ID = ".$posted['submitter_id'];
			$db->setQuery($q);
			$db->query();
		}
		else {
			/* Construct the query */
			$q = "INSERT INTO ".$db->nameQuote('#__rwf_forms_'.$form->id)."
				(".substr($qfields, 0, -1).")
				VALUES (".substr($qpart, 0, -1).")";
			$db->setQuery($q);
			if (!$db->query()) {
				/* We cannot save the answers, do not continue */
				JError::raiseWarning('error', JText::_('Cannot save form answers').' '.$db->getErrorMsg());
				return false;
			}
			$postvalues['answer_id'] = $db->insertid();
			
			/* Store the submitter details */
			$row = $this->getTable('Submitters');
			
			/* Add some settings */
			/* Get activate setting for event */
			$q = "SELECT activate
				FROM #__redevent_events AS e
				LEFT JOIN #__redevent_event_venue_xref AS x
				ON e.id = x.eventid
				WHERE x.id = ".JRequest::getInt('xref');
			$db->setQuery($q);
			$activate = $db->loadResult();
			
			/* Check if the user needs to confirm */
			if ($activate) $postvalues['confirmed'] = 0;
			else {
				/* Automatically confirm user */
				$postvalues['confirmed'] = 1;
				$postvalues['confirmdate'] = gmdate('Y-m-d H:i:s');;
			}
			
			if (!$row->bind($postvalues)) {
				$mainframe->enqueueMessage(JText::_('There was a problem binding the submitter data').': '.$row->getError(), 'error');
				return false;
			}
			/* Set the date */
			$row->submission_date = date('Y-m-d H:i:s' , time());
			
			/* pre-save checks */
			if (!$row->check()) {
				$mainframe->enqueueMessage(JText::_('There was a problem checking the submitter data').': '.$row->getError(), 'error');
				return false;
			}
			
			/* save the changes */
			if (!$row->store()) {
				if (stristr($db->getErrorMsg(), 'Duplicate entry')) $mainframe->enqueueMessage(JText::_('You have already entered this form'), 'error');
				else $mainframe->enqueueMessage(JText::_('There was a problem storing the submitter data').': '.$row->getError(), 'error');
				return false;
			}
		}
		
		if (JRequest::getVar('event_task') == 'review') {
			/* Check if we have a fullname */
			if (!isset($fullname)) $fullname = $submitter_email;
			
			/* Check if mailinglist integration is enabled */
			if ($form->mailinglistactive && $submitter_email && JRequest::getVar('submitternewsletter', false)) {
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
						$q = "SELECT id, acc_id FROM #__acajoom_lists WHERE list_name = ".$db->Quote($form->mailinglistname)." LIMIT 1";
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
				if (isset($configuration['use_phplist']) && $configuration['use_phplist']->value && !empty($form->mailinglistname)) {
					/* Include the PHPList API */
					require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'phplistuser.php');
					require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'simpleemail.php');
					require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'query.php');
					require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'errorhandler.php');
					
					/* Get the PHPList path configuration */
					PhpListUser::$PHPListPath = JPATH_SITE.DS.$configuration['phplist_path']->value;
					
					$user = new PhpListUser();
					$user->set_email($submitter_email);
					$listid = $user->getListId($form->mailinglistname);
					$user->addListId($listid);
					$user->save();
				}
			}
		}
		
		/* Reset the db */
		$db->select($mainframe->getCfg('db'));
		
		if (JRequest::getVar('event_task') == 'review') {
			/* Load the mailer in case we need to inform someone */
			if ($form->submitterinform || $form->contactpersoninform) {
				$this->Mailer();
			}
			
			/* Send a submission mail to the submitter if set */
			if ($form->submitterinform && $submitter_email) {
				/* Add the email address */
				$this->mailer->AddAddress($submitter_email);
				
				/* Mail submitter */
				$htmlmsg = '<html><head><title>Welcome</title></title></head><body>'.$form->submissionbody.'</body></html>';
				$this->mailer->setBody($htmlmsg);
				$this->mailer->setSubject($form->submissionsubject);
				
				/* Send the mail */
				if (!$this->mailer->Send()) {
					JError::raiseWarning(0, JText::_('NO_MAIL_SEND').' '.$this->mailer->error);
				}
				
				/* Clear the mail details */
				$this->mailer->ClearAddresses();
			}
			
			/* Inform contact person if need */
			if ($form->contactpersoninform) {
				$this->mailer->AddAddress($form->contactpersonemail, $fullname);
				/* Get the event name */
				$eventname = '';
				if (JRequest::getInt('xref', false)) {
					$q = "SELECT title
						FROM #__redevent_events e
						LEFT JOIN #__redevent_event_venue_xref x
						ON x.eventid = e.id
						WHERE x.id = ".JRequest::getInt('xref');
					$db->setQuery($q);
					$eventname = $db->loadResult();
				}
				if (JRequest::getInt('xref', false)) {
					$tags = array('[formname]', '[eventname]');
					$values = array($form->formname, $eventname);
					$this->mailer->setSubject(str_replace($tags, $values, JText::_('A new submission for form [formname] and event [eventname]')));
				}
				else {
					$this->mailer->setSubject(str_replace('[formname]', $form->formname, JText::_('A new submission for form [formname]')));
				}
				$htmlmsg = '<html><head><title></title></title></head><body>';
				$htmlmsg .= JText::_('A new submission has been received.');
				/* Add user submitted data if set */
				if ($form->contactpersonfullpost) {
					$q = "SELECT *
						FROM ".$db->nameQuote('#__rwf_forms_'.$form->id)." f
						LEFT JOIN #__rwf_submitters s
						ON s.answer_id = f.id
						WHERE submit_key = ".$db->Quote($submit_key);
					$db->setQuery($q);
					$results = $db->loadObjectList();
					if (is_array($results)) {
						foreach ($results as $rkey => $result) {
							$htmlmsg .= '<br /><table border="1">';
							foreach ($fieldlist as $key => $field) {
								$value = $field->field;
								$htmlmsg .= '<tr><td>'.$field->userfield.'</td><td>';
								$htmlmsg .= str_replace('~~~', '<br />', $results[$rkey]->$value);
								$htmlmsg .= '&nbsp;';
								$htmlmsg .= '</td></tr>'."\n";
							}
							$htmlmsg .= "</table><br />";
						}
					}
				}
				$htmlmsg .= '</body></html>';
				$this->mailer->setBody($htmlmsg);
				$this->mailer->Send();
				$this->mailer->ClearAddresses();
			}
		}
		/* All is good, check if we have an event in that case redirect to redEVENT */
		if (JRequest::getInt('xref', false)) {
			$redirect = 'index.php?option=com_redevent&view=attendees&controller=attendees&task=attendees&action=addattendee&xref='.JRequest::getInt('xref').'&submit_key='.$submit_key.'&form_id='.JRequest::getInt('form_id');
			$mainframe->redirect($redirect);
		}
		else return true;
	}
	
	/**
	 * Get the course title
	 */
	public function getCourseTitle() {
		$db = JFactory::getDBO();
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
		$q = "SELECT CONCAT(e.title, ' ', v.venue, ' ', dates, ' - ', enddates, ' ', times, ' - ', endtimes) AS coursetitle
			FROM #__redevent_event_venue_xref x
			LEFT JOIN #__redevent_events e
			ON e.id = x.eventid
			LEFT JOIN #__redevent_venues v
			ON v.id = x.venueid
			WHERE x.id = ".$xref;
		$db->setQuery($q);
		return $db->loadResult();
	}
	
	/**
	 * Initialise the mailer object to start sending mails
	 */
	 private function Mailer() {
		 global $mainframe;
		jimport('joomla.mail.helper');
		/* Start the mailer object */
		$this->mailer = &JFactory::getMailer();
		$this->mailer->isHTML(true);
		$this->mailer->From = $mainframe->getCfg('mailfrom');
		$this->mailer->FromName = $mainframe->getCfg('sitename');
		$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
	 }
}
?>
