<?php
/**
 * @package    Redform.Site
 *
 * @copyright  Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redform Component payment Model
 *
 * @package  Redform.Site
 * @since    2.5
 */
class RedFormModelPayment extends JModelLegacy
{
	protected $gateways = null;

	protected $submitKey = null;

	protected $form = null;

	protected $submitters = null;

	protected $paymentsDetails = array();

	/**
	 * contructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 */
	public function __construct($config)
	{
		parent::__construct();

		$this->setSubmitKey(JRequest::getVar('key', ''));
	}

	/**
	 * Setter
	 *
	 * @param   string  $key  submit key
	 *
	 * @return object
	 */
	public function setSubmitKey($key)
	{
		if (!empty($key))
		{
			$this->submitKey = $key;
		}

		return $this;
	}

	/**
	 * get redform plugin payment gateways, as an array of name and helper class
	 *
	 * @return array
	 */
	public function getGateways()
	{
		if (empty($this->gateways))
		{
			$details = $this->getPaymentDetails();

			JPluginHelper::importPlugin('redform_payment');
			$dispatcher = JDispatcher::getInstance();

			$gateways = array();
			$dispatcher->trigger('onGetGateway', array(&$gateways, $details));
			$this->gateways = $gateways;
		}

		return $this->gateways;
	}

	/**
	 * return gateways as options
	 *
	 * @return array
	 */
	public function getGatewayOptions()
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
	 *
	 * @return float
	 */
	public function getPrice()
	{
		if (empty($this->submitKey))
		{
			JError::raiseError(0, JText::_('COM_REDFORM_Missing_key'));

			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('public')
			->from('#__rwf_submitters')
			->where('submit_key = ' . $db->quote($this->submitKey));

		$db->setQuery($query);
		$res = $db->loadColumn();

		$total = 0.0;

		foreach ($res as $p)
		{
			$total += $p;
		}

		return $total;
	}

	/**
	 * return currency of form associated to this payment
	 *
	 * @return string
	 *
	 * @throws LogicException
	 */
	public function getCurrency()
	{
		if (empty($this->submitKey))
		{
			throw new LogicException(JText::_('COM_REDFORM_Missing_key'), 500);

			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('currency')
			->from('#__rwf_submitters')
			->where('submit_key = ' . $db->quote($this->submitKey));

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * return gateway helper
	 *
	 * @param   string  $name  name
	 *
	 * @return object or false if no corresponding gateway
	 */
	public function getGatewayHelper($name)
	{
		$gw = $this->getGateways();

		foreach ($gw as $g)
		{
			if (strcasecmp($g['name'], $name) == 0)
			{
				return $g['helper'];
			}
		}

		RdfHelperLog::simpleLog('NOTIFICATION GATEWAY NOT FOUND' . ': ' . $name);

		return false;
	}

	/**
	 * returns form associated to submit_key
	 *
	 * @return object
	 */
	public function getForm()
	{
		if (empty($this->submitKey))
		{
			JError::raiseError(0, JText::_('COM_REDFORM_Missing_key'));

			return false;
		}

		if (empty($this->form))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('f.*')
				->from('#__rwf_submitters AS s')
				->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id')
				->where('s.submit_key = ' . $db->quote($this->submitKey));

			$db->setQuery($query);
			$this->form = $db->loadObject();
		}

		return $this->form;
	}

	/**
	 * return submitters
	 *
	 * @return bool|mixed|null
	 */
	public function getSubmitters()
	{
		if (empty($this->submitKey))
		{
			JError::raiseError(0, JText::_('COM_REDFORM_Missing_key'));

			return false;
		}

		if (empty($this->submitters))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('s.*')
				->from('#__rwf_submitters AS s')
				->where('s.submit_key = ' . $db->quote($this->submitKey));

			$db->setQuery($query);
			$this->submitters = $db->loadObjectList();
		}

		return $this->submitters;
	}

	/**
	 * provides information for process function of helpers (object id, title, etc...)
	 *
	 * @param   string  $key  submit key
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	public function getPaymentDetails($key = null)
	{
		if (!$key)
		{
			$key = $this->submitKey;
		}

		if (!isset($this->paymentsDetails[$key]))
		{
			JPluginHelper::importPlugin('redform_integration');
			$dispatcher = JDispatcher::getInstance();

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
			$obj->form = $form->formname;
			$obj->form_id = $form->id;
			$obj->key = $key;
			$obj->currency = $asub->currency;

			// More fields with integration
			$paymentDetailFields = null;
			$dispatcher->trigger('getRFSubmissionPaymentDetailFields', array($asub->integration, $key, &$paymentDetailFields));

			if (!$paymentDetailFields)
			{
				$paymentDetailFields = new stdClass;

				if (JFactory::getApplication()->input->get('paymenttitle'))
				{
					$paymentDetailFields->title = JFactory::getApplication()->input->get('paymenttitle');
				}
				else
				{
					$paymentDetailFields->title = JText::_('COM_REDFORM_Form_submission') . ': ' . $form->formname;
				}

				$paymentDetailFields->adminDesc = $paymentDetailFields->title;
				$paymentDetailFields->uniqueid = $key;
			}

			// Map
			$obj->title = $paymentDetailFields->title;
			$obj->adminDesc = $paymentDetailFields->adminDesc;
			$obj->uniqueid = $paymentDetailFields->uniqueid;
			$this->paymentsDetails[$key] = $obj;
		}

		return $this->paymentsDetails[$key];
	}

	/**
	 * send notification on payment received
	 *
	 * @return bool
	 */
	public function notifyPaymentReceived()
	{
		$res = $this->_notifyFormContact();
		$res = ($this->_notifySubmitter() ? $res : false);

		return $res;
	}

	/**
	 * send email to submitter on payment received
	 *
	 * @return bool
	 */
	private function _notifySubmitter()
	{
		$mainframe = JFactory::getApplication();
		$mailer = JFactory::getMailer();

		$mailer->From = $mainframe->getCfg('mailfrom');
		$mailer->FromName = $mainframe->getCfg('sitename');
		$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
		$mailer->IsHTML(true);

		$form = $this->getForm();

		$core = new RdfCore;
		$answers = $core->getAnswers($this->submitKey);
		$first = $answers->getFirstSubmission();
		$replaceHelper = new RdfHelperTagsreplace($form, $first);

		// Set the email subject
		$subject = (empty($form->submitterpaymentnotificationsubject)
			? JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_DEFAULT')
			: $form->submitterpaymentnotificationsubject);
		$subject = $replaceHelper->replace($subject);
		$mailer->setSubject($subject);

		$body = (empty($form->submitterpaymentnotificationbody)
			? JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_SUBJECT_DEFAULT')
			: $form->submitterpaymentnotificationbody);
		$link = JRoute::_(JURI::root() . 'administrator/index.php?option=com_redform&view=submitters&form_id=' . $form->id);
		$body = $replaceHelper->replace($body, array('[submitters]' => $link));
		$mailer->setBody($body);

		$contact = $core->getSubmissionContactEmail($this->submitKey, true);

		if (!$contact)
		{
			return true;
		}

		$mailer->addRecipient($contact['email']);

		if (!$mailer->send())
		{
			return false;
		}

		return true;
	}

	/**
	 * send email to form contact on payment received
	 *
	 * @return bool
	 */
	private function _notifyFormContact()
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
			$answers = $core->getAnswers($this->submitKey);
			$first = $answers->getFirstSubmission();
			$replaceHelper = new RdfHelperTagsreplace($form, $first);

			// Set the email subject and body
			$subject = (empty($form->contactpaymentnotificationsubject)
				? JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_SUBJECT_DEFAULT')
				: $form->contactpaymentnotificationsubject);
			$subject = $replaceHelper->replace($subject);

			$body = (empty($form->contactpaymentnotificationbody)
				? JText::_('COM_REDFORM_PAYMENT_CONTACT_NOTIFICATION_EMAIL_BODY_DEFAULT')
				: $form->contactpaymentnotificationbody);

			$link = JRoute::_(JURI::root() . 'administrator/index.php?option=com_redform&view=submitters&form_id=' . $form->id);
			$body = $replaceHelper->replace($body, array('[submitters]' => $link));

			$mailer->setSubject($subject);
			$mailer->setBody($body);

			if (!$mailer->send())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * check if this has already be paid
	 *
	 * @return int id of payment
	 */
	public function hasAlreadyPaid()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__rwf_payment')
			->where('submit_key = ' . $db->quote($this->submitKey))
			->where('paid = 1');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}
}
