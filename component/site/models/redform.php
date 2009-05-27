<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * redFORM model
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 */
class RedformModelRedform extends JModel {
	function __construct() {
		parent::__construct();
	}

	/**
	 * Save a form
	 *
	 * @todo take field names from fields table
	 */
	function getSaveForm() {
		global $mainframe;
		
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
		
		if ($form->captchaactive) {
			/* Check if Captcha is correct */
			$word = JRequest::getVar('captchaword', false, '', 'CMD');
			$return = $mainframe->triggerEvent('onCaptcha_confirm', array($word, $return));
			
			if (!$return[0]) {
	      $mainframe->enqueueMessage(JText::_('CAPTCHA_WRONG'));
	      return false;				
			}
		}

			/* Load the configuration */
			$db = JFactory::getDBO();
			
			/* Get the file path */
			$query = "SELECT value
					FROM #__rwf_configuration
					WHERE name = ".$db->Quote('filelist_path');
			$db->setQuery($query);
			$filepath = $db->loadResult();
			
			/* Load the fields */
			$q = "SELECT id, LOWER(REPLACE(".$db->nameQuote('field').", ' ','')) AS ".$db->nameQuote('field').", field AS userfield, ordering
				FROM ".$db->nameQuote('#__rwf_fields')."
				WHERE form_id = ".$form->id."
				AND published = 1
				ORDER BY ordering";
			$db->setQuery($q);
			$fieldlist = $db->loadObjectList('id');
			
			/* Load the posted variables */
			$post = JRequest::get('post');
			$files = JRequest::get('files');
			$posted = array_merge($post, $files);
			
			/* See if we have an event ID */
			if (JRequest::getInt('event_id', false)) {
				$event_id = JRequest::getInt('event_id', 0);
				$posted['event_id'] = $event_id;
			}
			else if (JRequest::getInt('competition_id', false)) {
				$event_id = JRequest::getInt('competition_id', 0);
				$post['event_id'] = $event_id;
			}
			else $post['event_id'] = 0;
			
			/* Loop through the different forms */
			$totalforms = JRequest::getInt('curform');
			if (JRequest::getVar('event_task') == 'userregister') $totalforms--;
			/* Sign up minimal 1 */
			if ($totalforms == 0) $totalforms++; 
			
			for ($signup = 1; $signup <= $totalforms; $signup++) {
				/* Create an array of values to store */
				$postvalues = array();
				foreach ($posted as $key => $value) {
					if ((strpos($key, 'field') === 0) && (strpos($key, '_'.$signup, 5) > 0)) {
						$postvalues[str_replace('_'.$signup, '', $key)] = $value;
					}
				}
				
				/* Some default values needed */
				if (isset($post['xref'])) $postvalues['xref'] = $post['xref'];
				else $postvalues['xref'] = 0;
				$postvalues['form_id'] = $post['form_id'];
				$postvalues['submitternewsletter'] = JRequest::getVar('submitternewsletter', '');
				$postvalues['submit_key'] = $submit_key;
				
				/* Get the raw form data */
				$postvalues['rawformdata'] = serialize($postvalues);
				
				/* Clear the field and answer lists */
				$qfields = '';
				$qpart = '';
				$listnames = array();
				
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
						else if (isset($postvalues['field'.$key]['email'])) {
							$answer = $submitter_email = $postvalues['field'.$key]['email'][0];
							if (array_key_exists('listnames', $postvalues['field'.$key]['email'])) {
								$listnames[] = $postvalues['field'.$key]['email']['listnames'];
							}
						}
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
						else if (isset($postvalues['field'.$key]['name']['fileupload'])) {
							$answer = '';
							/* Check if the folder exists */
							jimport('joomla.filesystem.folder');
							jimport('joomla.filesystem.file');
							$fullpath = $filepath.DS.'redform_'.$form->id;
							if (!JFolder::exists($fullpath)) {
								if (!JFolder::create($fullpath)) {
									JError::raiseWarning(0, JText::_('CANNOT_CREATE_FOLDER').' '.$fullpath);
									$status = false;
								}
							}
							clearstatcache();
							if (JFolder::exists($fullpath)) {
								if (JFile::exists($fullpath.DS.basename($postvalues['field'.$key]['name']['fileupload'][0]))) {
									JError::raiseWarning(0, JText::_('FILENAME_ALREADY_EXISTS').': '.basename($postvalues['field'.$key]['name']['fileupload'][0]));
									return false;
								}
								else {
									/* Start processing uploaded file */
									if (is_uploaded_file($postvalues['field'.$key]['tmp_name']['fileupload'][0])) {
										if (JFolder::exists($fullpath) && is_writable($fullpath)) {
											if (move_uploaded_file($postvalues['field'.$key]['tmp_name']['fileupload'][0], $fullpath.DS.basename($postvalues['field'.$key]['name']['fileupload'][0]))) {
												$answer = $fullpath.DS.basename($postvalues['field'.$key]['name']['fileupload'][0]);
											}
											else {
												JError::raiseWarning(0, JText::_('CANNOT_UPLOAD_FILE'));
												return false;
											}
										}
										else {
											JError::raiseWarning(0, JText::_('FOLDER_DOES_NOT_EXIST'));
											return false;
										}
									}
								}
							}
							else {
								JError::raiseWarning(0, JText::_('FOLDER_DOES_NOT_EXIST'));
								return false;
							}
						}
						else $answer = '';
						$qpart .= $db->Quote($answer).',';
					}
				}
				if (JRequest::getVar('event_task') == 'review') {
					if (isset($posted['confirm'][($signup-1)])) {
						/* Updating the values */
						$ufields = explode(',', substr($qfields, 0, -1));
						$upart = explode(',', substr($qpart, 0, -1));
						
						$q = "UPDATE ".$db->nameQuote('#__rwf_forms_'.$form->id)."
							SET ";
						foreach ($ufields as $ukey => $field) {
							$q .= trim($field)." = ".trim($upart[$ukey]).", ";
						}
						$q = substr($q, 0, -2)." WHERE ID = ".$posted['confirm'][($signup-1)];
						$db->setQuery($q);
						$db->query();
					}
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
					
					if ($postvalues['xref'] > 0) {
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
							$postvalues['confirmdate'] = gmdate('Y-m-d H:i:s');
						}
					}
					else {
						/* Automatically confirm user */
						$postvalues['confirmed'] = 1;
						$postvalues['confirmdate'] = gmdate('Y-m-d H:i:s');
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
				
				/* Clean up any signups that need to be removed */
				$this->getConfirmAttendees();
				
				if (JRequest::getVar('event_task', 'review') == 'review' && count($listnames) > 0) {
					exit();
					foreach ($listnames as $key => $alllistname) {
						foreach ($alllistname as $listkey => $mailinglistname) {
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
				
				/* Reset the db */
				$db->select($mainframe->getCfg('db'));
			} /* End multi-user signup */
			
			if (JRequest::getVar('event_task', 'review') == 'review') {
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
					if (!isset($fullname)) $fullname = $submitter_email;
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
						if (JRequest::getInt('productid', false)) {
							$productdetails = $this->getProductDetails();
							if (!stristr('http', $productdetails->product_full_image)){ 
								$productimage = JURI::root().'/components/com_virtuemart/shop_image/product/'.$productdetails->product_full_image;
							}
							else $productimage = $productdetails->product_full_image;
							$htmlmsg .= '<div id="productimage">'.JHTML::_('image', $productimage, $productdetails->product_name).'</div>';
							$htmlmsg .= '<div id="productname">'.$productdetails->product_name.'</div>';
						}
						$q = "SELECT *
							FROM ".$db->nameQuote('#__rwf_forms_'.$form->id)." f
							LEFT JOIN #__rwf_submitters s
							ON s.answer_id = f.id
							WHERE submit_key = ".$db->Quote($submit_key);
						$db->setQuery($q);
						$results = $db->loadObjectList();
						if (is_array($results)) {
							$patterns[0] = '/\r\n/';
							$patterns[1] = '/\r/';
							$patterns[2] = '/\n/';
							$replacements[2] = '<br />';
							$replacements[1] = '<br />';
							$replacements[0] = '<br />';
							foreach ($results as $rkey => $result) {
								$htmlmsg .= '<br /><table border="1">';
								foreach ($fieldlist as $key => $field) {
									$value = $field->field;
									$userinput = preg_replace($patterns, $replacements, $results[$rkey]->$value);
									$htmlmsg .= '<tr><td>'.$field->userfield.'</td><td>';
									$htmlmsg .= str_replace('~~~', '<br />', $userinput);
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
				$redirect = 'index.php?option=com_redevent&task='.JRequest::getVar('event_task').'&xref='.JRequest::getInt('xref').'&submit_key='.$submit_key.'&form_id='.JRequest::getInt('form_id');
				if (JRequest::getVar('event_task') == 'review') {
					$redirect .= '&view=confirmation&page=final';
					$arkeys = array_keys(JRequest::getVar('submit'));
					$redirect .= '&action='.$arkeys[0];
				}
				else {
					$redirect .= '&view=confirmation&page=confirmation&event_task=review';
					if (strtolower(JRequest::getVar('submit')) == strtolower(JText::_('SUBMIT_AND_PRINT'))) $redirect .= '&action=print';
				}
				if ($form->virtuemartactive) {
					$redirect .= '&redformback=1';
				}
				$mainframe->redirect($redirect);
			}
			
			/* All is good, check if we have an competition in that case redirect to redCOMPETITION */
			if (JRequest::getVar('competition_id', false)) {
				$redirect = 'index.php?option=com_redcompetition&task='.JRequest::getVar('competition_task').'&competition_id='.JRequest::getInt('competition_id').'&submitter_id='.$post['answer_id'].'&form_id='.JRequest::getInt('form_id');
				$mainframe->redirect($redirect);
			}
			return array($form->submitnotification, $form->notificationtext);
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
	 
	 /**
	  * Get the VirtueMart settings
	  */
	 public function getVmSettings() {
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
}
?>