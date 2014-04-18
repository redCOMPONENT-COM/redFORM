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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redform Component payment Model
 *
 * @package Joomla
 * @subpackage redform
 * @since		0.9
 */
class RedFormModelPayment extends JModelLegacy
{
	protected $_gateways = null;

	protected $_submit_key = null;

	protected $_form = null;

	protected $_submitters = null;

	protected $_gatewayhelper = null;

	protected $paymentsDetails = array();

	function __construct($config)
	{
		parent::__construct();

		$this->setSubmitKey(JRequest::getVar('key', ''));
	}

	function setSubmitKey($key)
	{
		if (!empty($key)) {
			$this->_submit_key = $key;
		}
	}

	/**
	 * get redform plugin payment gateways, as an array of name and helper class
	 * @return array
	 */
	function getGateways()
	{
		if (empty($this->_gateways))
		{
			$details = $this->getPaymentDetails();

			JPluginHelper::importPlugin('redform_payment');
			$dispatcher = JDispatcher::getInstance();

			$gateways = array();
			$results = $dispatcher->trigger('onGetGateway', array(&$gateways, $details));
			$this->_gateways = $gateways;
		}

		return $this->_gateways;
	}

	/**
	 * return gateways as options
	 * @return array
	 */
	function getGatewayOptions()
	{
		$details = $this->getPaymentDetails();

		$gw = $this->getGateways();

		$options = array();

		foreach ($gw as $g)
		{
			if (isset($g['label']))
			{
				$label = $g['label'];
			}
			else
			{
				$label = $g['name'];
			}

			$options[] = JHTML::_('select.option', $g['name'], $label);
		}

		// Filter gateways through plugins
		JPluginHelper::importPlugin('redform_payment');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onFilterGateways', array(&$options, $details));

		return $options;
	}

	/**
	 * return total price for submissions associated to submit _key
	 * @return float
	 */
	function getPrice()
	{
		if (empty($this->_submit_key)) {
			JError::raiseError(0, JText::_('COM_REDFORM_Missing_key'));
			return false;
		}

		$query = ' SELECT price FROM #__rwf_submitters WHERE submit_key = '. $this->_db->Quote($this->_submit_key)
		            ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadColumn();
		$total = 0.0;
		foreach ($res as $p) {
			$total += $p;
		}
		return $total;
	}

	/**
	 * return currency of form associated to this payment
	 * @return unknown_type
	 */
	function getCurrency()
	{
		if (empty($this->_submit_key))
		{
			throw new LogicException(JText::_('COM_REDFORM_Missing_key'), 500);
			return false;
		}

		$query = ' SELECT currency FROM #__rwf_submitters WHERE submit_key = '. $this->_db->Quote($this->_submit_key)
		;
		$this->_db->setQuery($query);

		return $this->_db->loadResult();
	}

	/**
	 * return gateway helper
	 * @param string name
	 * @return object or false if no corresponding gateway
	 */
	function getGatewayHelper($name)
	{
		$gw = $this->getGateways();
		foreach ($gw as $g)
		{
			if (strcasecmp($g['name'], $name) == 0) {
				return $g['helper'];
			}
		}
		RdfHelperLog::simpleLog('NOTIFICATION GATEWAY NOT FOUND'.': '.$name);

		return false;
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

	function getSubmitters()
	{
		if (empty($this->_submit_key)) {
			JError::raiseError(0, JText::_('COM_REDFORM_Missing_key'));
			return false;
		}

		if (empty($this->_submitters))
		{
			$query = ' SELECT s.* '
			       . ' FROM #__rwf_submitters AS s '
			       . ' WHERE s.submit_key = '. $this->_db->Quote($this->_submit_key)
			            ;
			$this->_db->setQuery($query);
			$this->_submitters = $this->_db->loadObjectList();
		}
		return $this->_submitters;
	}

	/**
	 * provides information for process function of helpers (object id, title, etc...)
	 * @param $key
	 * @return unknown_type
	 */
	function getPaymentDetails($key = null)
	{
		if (!$key)
		{
			$key = $this->_submit_key;
		}

		if (!isset($this->paymentsDetails[$key]))
		{
			$submitters = $this->getSubmitters();

			if (!count($submitters))
			{
				return false;
			}

			$asub = current($submitters);
			$form = $this->getForm();

			if (!$asub->currency)
			{
				throw new Exception('Currency must be set in submission for payment - Please contact system administrator', 500);
			}

			$obj = new stdclass;
			$obj->integration = $asub->integration;
			$obj->form        = $form->formname ;
			$obj->form_id     = $form->id;
			$obj->key         = $key;
			$obj->currency    = $asub->currency;

			switch ($asub->integration)
			{
				case 'redevent':
					$event = $this->getEventAttendee($key);

					$obj->title = JText::_('COM_REDFORM_Event_registration').': '.$event->title.' @ '.$event->venue. ', '. strftime('%x', strtotime($event->dates)).' '.($event->times && $event->times != '00:00:00' ? $event->times : '');
					$obj->uniqueid = $event->uniqueid;
					break;

				default:
					if (JFactory::getApplication()->input->get('paymenttitle'))
					{
						$obj->title = JFactory::getApplication()->input->get('paymenttitle');
					}
					else
					{
						$obj->title = JText::_('COM_REDFORM_Form_submission').': '.$form->formname;
					}

					$obj->uniqueid = $key;
					break;
			}
			$this->paymentsDetails[$key] = $obj;
		}

		return $this->paymentsDetails[$key];
	}

	function getEventAttendee($key)
	{
		$query = ' SELECT r.id as attendee_id, e.course_code, r.xref, v.venue, x.*, '
		              . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', e.title, x.title) ELSE e.title END as title '
		              . ' FROM #__redevent_register AS r '
		              . ' INNER JOIN #__redevent_event_venue_xref AS x on x.id = r.xref '
		              . ' INNER JOIN #__redevent_events AS e on e.id = x.eventid '
		              . ' INNER JOIN #__redevent_venues AS v on v.id = x.venueid '
		              . ' WHERE r.submit_key = '. $this->_db->Quote($key)
		              ;
		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadObject();

		if ($res)
		{
			$res->uniqueid = $res->course_code . '-' . $res->xref . '-' . $res->attendee_id;
		}

		return $res;
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
		$mainframe = JFactory::getApplication();
		$mailer = JFactory::getMailer();

		$mailer->From = $mainframe->getCfg('mailfrom');
		$mailer->FromName = $mainframe->getCfg('sitename');
		$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
		$mailer->IsHTML(true);

		$form = $this->getForm();

		$core = new RdfCore;
		$answers = $core->getAnswers($this->_submit_key);
		$answersArray = get_object_vars(reset($answers)->fields);
		$replaceHelper = new RdfHelperTagsreplace($form, $answersArray);

		// set the email subject
		$subject = (empty($form->submitterpaymentnotificationsubject) ? JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_DEFAULT') : $form->submitterpaymentnotificationsubject);
		$subject = $replaceHelper->replace($subject);
		$mailer->setSubject($subject);

		$body    = (empty($form->submitterpaymentnotificationbody)    ? JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_DEFAULT') : $form->submitterpaymentnotificationbody);
		$link = JRoute::_(JURI::root().'administrator/index.php?option=com_redform&view=submitters&form_id='.$form->id);
		$body = $replaceHelper->replace($body, array('[submitters]' => $link));
		$mailer->setBody($body);

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
		$mainframe = JFactory::getApplication();
		$mailer = JFactory::getMailer();
		$mailer->From = $mainframe->getCfg('mailfrom');
		$mailer->FromName = $mainframe->getCfg('sitename');
		$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
		$mailer->IsHTML(true);

		$form = $this->getForm();

		if ($form->contactpersoninform)
		{
			$addresses = RdfHelper::extractEmails($form->contactpersonemail, true);

			if (!$addresses)
			{
				return true;
			}

			foreach ($addresses as $a)
			{
				$mailer->addRecipient($a);
			}

			$core = new RdfCore;
			$answers = $core->getAnswers($this->_submit_key);
			$answersArray = get_object_vars(reset($answers)->fields);
			$replaceHelper = new RdfHelperTagsreplace($form, $answersArray);

			// set the email subject and body
			$subject = (empty($form->contactpaymentnotificationsubject) ? JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_SUBJECT_DEFAULT') : $form->contactpaymentnotificationsubject);
			$subject = $replaceHelper->replace($subject);

			$body    = (empty($form->contactpaymentnotificationbody)    ? JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_BODY_DEFAULT') : $form->contactpaymentnotificationbody);

			$link = JRoute::_(JURI::root().'administrator/index.php?option=com_redform&view=submitters&form_id='.$form->id);
			$body = $replaceHelper->replace($body, array('[submitters]' => $link));

			$mailer->setSubject($subject);
			$mailer->setBody($body);

			if ($mailer->send())
			{
				return true;
			}
		}
		return true;
	}

	/**
	* check if this has already be paid
	*
	* @return int id of payment
	*/
	function hasAlreadyPaid()
	{
		$query = ' SELECT id'
		       . ' FROM #__rwf_payment '
		       . ' WHERE submit_key = ' . $this->_db->Quote($this->_submit_key)
		       . '   AND paid = 1 '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		return $res;
	}
}
