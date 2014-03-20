<?php
/**
 * @version 1.0 $Id: archive.php 217 2009-06-06 20:04:26Z julien $
 * @package Joomla
 * @subpackage redform
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
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Joomla redFORM Component Model
 *
 * @author Julien Vonthron <julien@redweb.dk>
 * @package   redform
 * @since 2.0
 */
class RedformModelPayment extends JModel
{
  /**
   * item id
   *
   * @var int
   */
  var $_id = null;

  /**
   * Project data
   *
   * @var array
   */
  var $_data = null;

	var $_submit_key = null;

  /**
   * Constructor
   *
   * @since 0.1
   */
  function __construct()
  {
    parent::__construct();

    $array = JRequest::getVar('cid', array(0), '', 'array');
    $this->setId((int)$array[0]);
		$this->setSubmitKey(JRequest::getVar('submit_key', ''));
	}

	function setSubmitKey($key)
	{
		if (!empty($key)) {
			$this->_submit_key = $key;
		}
	}

  /**
   * Method to set the item identifier
   *
   * @access  public
   * @param int item identifier
   */
  function setId($id)
  {
    // Set item id and wipe data
    $this->_id    = $id;
    $this->_data  = null;
  }



  /**
   * Method to get an item
   *
   * @since 0.1
   */
  function &getData()
  {
    // Load the item data
    if (!$this->_loadData()) $this->_initData();

    return $this->_data;
  }

	/**
	 * Method to remove an item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__rwf_payment'
				. ' WHERE id IN ( '.$cids.' )';

			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to load content data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT *'.
					' FROM #__rwf_payment ' .
          ' WHERE id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the competition data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$row = & $this->getTable('Payments', 'RedformTable');
			if (!$row->date) {
				$row->date = strftime('%Y-%m-%d %H:%M:%S');
			}
			$this->_data					= $row;
			return (boolean) $this->_data;
		}
		return true;
	}

  /**
   * Method to store the item
   *
   * @access  public
   * @return  false|int id on success
   * @since 1.5
   */
  function store($data)
  {
  	$array = JRequest::getVar('cid', array(0), '', 'array');
  	$cid = intval($array[0]);

		$row = & $this->getTable('Payments', 'RedformTable');

    // Bind the form fields to the items table
    if (!$row->bind($data)) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    $row->id = $cid;

    // Make sure the item is valid
    if (!$row->check()) {
      $this->setError($row->getError());
      return false;
    }

    // Store the item to the database
    if (!$row->store()) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    return $row;
  }

  /**
  * send notification on payment received
  *
  */
  function notifyPaymentReceived()
  {
  	$res = $this->_notifyFormContact();
  	$res = ($this->_notifySubmitter() ? $res : false);
  	return $res;
  }

  /**
   * send email to submitter on payment received
   */
  function _notifySubmitter()
  {
  	$mainframe = &JFactory::getApplication();
  	$mailer = &JFactory::getMailer();
  	$mailer->From = $mainframe->getCfg('mailfrom');
  	$mailer->FromName = $mainframe->getCfg('sitename');
  	$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
  	$mailer->IsHTML(true);

  	$form = $this->getForm();

  	// set the email subject
  	$subject = (empty($form->submitterpaymentnotificationsubject) ? JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_DEFAULT') : $form->submitterpaymentnotificationsubject);
  	$body    = (empty($form->submitterpaymentnotificationbody)    ? JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_DEFAULT') : $form->submitterpaymentnotificationbody);
  	$mailer->setSubject(JText::sprintf($subject, $form->formname));
  	$link = JRoute::_(JURI::root().'administrator/index.php?option=com_redform&view=submitters&form_id='.$form->id);
  	$mailer->setBody(JText::sprintf($body, $form->formname, $link));

  	$core = new RedformCore();
  	$emails = $core->getSubmissionContactEmail($this->_submit_key);

  	if (!$emails) {
  		return false;
  	}

  	foreach ((array) $emails as $sid)
  	{
  		foreach ((array) $sid as $email)
  		{
	  		$mailer->addRecipient($email['email']);
  		}
  	}
		if (!$mailer->send()) {
			return false;
		}
  	return true;
  }

  /**
   * send email to form contact on payment received
   */
  function _notifyFormContact()
  {
  	$mainframe = &JFactory::getApplication();
  	$mailer = &JFactory::getMailer();
  	$mailer->From = $mainframe->getCfg('mailfrom');
  	$mailer->FromName = $mainframe->getCfg('sitename');
  	$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
  	$mailer->IsHTML(true);

  	$form = $this->getForm();
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
  		// set the email subject and body
  		$subject = (empty($form->contactpaymentnotificationsubject) ? JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_SUBJECT_DEFAULT') : $form->contactpaymentnotificationsubject);
  		$body    = (empty($form->contactpaymentnotificationbody)    ? JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_BODY_DEFAULT') : $form->contactpaymentnotificationbody);

  		$mailer->setSubject(JText::sprintf($subject, $form->formname));
  		$link = JRoute::_(JURI::root().'administrator/index.php?option=com_redform&view=submitters&form_id='.$form->id);
  		$mailer->setBody(JText::sprintf($body, $form->formname, $link));

  		if ($mailer->send()) {
  			return true;
  		}
  	}
  	return true;
  }

  /**
  * returns form associated to submit_key
  * @return object
  */
  function getForm()
  {
  	if (empty($this->_submit_key)) {
  	JError::raiseError(0, JText::_('COM_REDFORM_Missing_key'));
  			return false;
  	}

  	if (empty($this->_form))
  	{
  	$query = ' SELECT f.* '
  			       . ' FROM #__rwf_submitters AS s '
  			       . ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
  			       . ' WHERE s.submit_key = '. $this->_db->Quote($this->_submit_key)
  	;
  	$this->_db->setQuery($query, 0, 1);
  	$this->_form = $this->_db->loadObject();
  	}
  	return $this->_form;
  }
}
?>
