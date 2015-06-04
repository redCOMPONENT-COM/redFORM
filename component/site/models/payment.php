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

	protected $reference = null;

	protected $form = null;

	protected $submitters = null;

	protected $paymentsDetails = array();

	/**
	 * Cart data from db
	 *
	 * @var object
	 */
	protected $cart;

	/**
	 * contructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 */
	public function __construct($config)
	{
		parent::__construct();

		$this->reference = JFactory::getApplication()->input->get('reference', '');
	}

	/**
	 * Is billing required ?
	 *
	 * @return bool
	 */
	public function isRequiredBilling()
	{
		$cart = $this->getCart();

		$query = $this->_db->getQuery(true);

		$query->select('f.requirebilling')
			->from('#__rwf_cart_item AS ci')
			->join('INNER', '#__rwf_payment_request AS pr ON pr.id = ci.payment_request_id')
			->join('INNER', '#__rwf_submitters AS s ON s.id = pr.submission_id')
			->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id')
			->where('f.requirebilling = 1')
			->where('ci.cart_id = ' . $cart->id);
		$this->_db->setQuery($query);

		return ($this->_db->loadResult() ? true : false);
	}

	/**
	 * Setter
	 *
	 * @param   string  $reference  submit key
	 *
	 * @return object
	 */
	public function setCartReference($reference)
	{
		if (!empty($reference))
		{
			$this->reference = $reference;
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
	 *
	 * @throws Exception
	 */
	public function getPrice()
	{
		$cart = $this->getCart();

		return $cart->price + $cart->vat;
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
		$cart = $this->getCart();

		return $cart->currency;
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
	 * Set payment requests associated to cart as paid
	 *
	 * @return void
	 */
	public function setPaymentRequestAsPaid()
	{
		$cart = $this->getCart();

		$query = $this->_db->getQuery(true);

		$query->update('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_cart_item AS ci on ci.payment_request_id = pr.id')
			->where('ci.cart_id = ' . $cart->id)
			->set('pr.paid = 1');

		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * returns form associated to submit_key
	 *
	 * @return object
	 */
	public function getForm()
	{
		$cart = $this->getCart();

		if (empty($this->form))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('f.*')
				->from('#__rwf_submitters AS s')
				->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id')
				->join('INNER', '#__rwf_payment_request AS pr ON pr.submission_id = s.id')
				->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
				->where('ci.cart_id = ' . $db->quote($cart->id));

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
		$cart = $this->getCart();

		if (empty($this->submitters))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('s.*')
				->from('#__rwf_submitters AS s')
				->join('INNER', '#__rwf_payment_request AS pr ON pr.submission_id = s.id')
				->join('INNER', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr.id')
				->where('ci.cart_id = ' . $db->quote($cart->id));

			$db->setQuery($query);
			$this->submitters = $db->loadObjectList();
		}

		return $this->submitters;
	}

	/**
	 * provides information for process function of helpers (object id, title, etc...)
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	public function getPaymentDetails()
	{
		$cart = $this->getCart();
		$key = $cart->reference;

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

			if (!$cart->currency)
			{
				throw new Exception('Currency must be set in submission for payment - Please contact system administrator', 500);
			}

			$obj = new stdclass;
			$obj->integration = $asub->integration;
			$obj->form = $form->formname;
			$obj->form_id = $form->id;
			$obj->key = $cart->reference;
			$obj->currency = $cart->currency;
			$obj->submit_key = $asub->submit_key;

			// More fields with integration
			$paymentDetailFields = null;
			$dispatcher->trigger('getRFSubmissionPaymentDetailFields', array($asub->integration, $asub->submit_key, &$paymentDetailFields));

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
		$mailer = RdfHelper::getMailer();

		$mailer->From = $mainframe->getCfg('mailfrom');
		$mailer->FromName = $mainframe->getCfg('sitename');
		$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));

		$form = $this->getForm();

		$core = new RdfCore;
		$answers = $core->getAnswers($this->getSubmitKey());
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

		$body = RdfHelper::wrapMailHtmlBody($body, $subject);
		$mailer->MsgHTML($body);

		$contact = $core->getSubmissionContactEmail($this->getSubmitKey(), true);

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
		$mailer = RdfHelper::getMailer();
		$mailer->From = $mainframe->getCfg('mailfrom');
		$mailer->FromName = $mainframe->getCfg('sitename');
		$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));

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
			$answers = $core->getAnswers($this->getSubmitKey());
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
			$body = RdfHelper::wrapMailHtmlBody($body, $subject);

			$mailer->setSubject($subject);
			$mailer->MsgHTML($body);

			if (!$mailer->send())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * check if this has already been paid
	 *
	 * @return boolean
	 */
	public function hasAlreadyPaid()
	{
		$query = $this->_db->getQuery(true);

		$query->select('id')
			->from('#__rwf_cart')
			->where('reference = ' . $this->_db->quote($this->reference))
			->where('paid = 1');

		$this->_db->setQuery($query);

		return $this->_db->loadResult() ? true : false;
	}

	/**
	 * return a new cart for payment
	 *
	 * @param   string  $submitKey  submitkey for which we want a payment
	 *
	 * @return RTable
	 */
	public function getNewCart($submitKey)
	{
		$paymentRequests = $this->getUnpaidSubmitKeyPaymentRequests($submitKey);

		$ids = array();
		$price = 0;
		$vat = 0;
		$currency = '';

		foreach ($paymentRequests as $pr)
		{
			$ids[] = $pr->id;
			$price += $pr->price;
			$vat += $pr->vat;
			$currency = $pr->currency;
		}

		$cart = RTable::getAdminInstance('Cart', array(), 'com_redform');
		$cart->reference = uniqid();
		$cart->created = JFactory::getDate()->toSql();
		$cart->price = $price;
		$cart->vat = $vat;
		$cart->currency = $currency;

		$cart->store();

		foreach ($ids as $id)
		{
			$cartItem = RTable::getAdminInstance('Cartitem', array(), 'com_redform');
			$cartItem->cart_id = $cart->id;
			$cartItem->payment_request_id = $id;
			$cartItem->store();
		}

		return $cart;
	}

	/**
	 * Return unpaid payment requests for submission associated to submit key
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return mixed
	 */
	private function getUnpaidSubmitKeyPaymentRequests($submitKey)
	{
		$query = $this->_db->getQuery(true);

		$query->select('pr.id, pr.price, pr.vat, pr.currency')
			->from('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_submitters AS s ON s.id = pr.submission_id')
			->where('pr.paid = 0')
			->where('s.submit_key = ' . $this->_db->quote($submitKey));

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * get cart data from db
	 *
	 * @return mixed|object
	 */
	public function getCart()
	{
		if (!$this->cart)
		{
			$query = $this->_db->getQuery(true);

			$query->select('*')
				->from('#__rwf_cart')
				->where('reference = ' . $this->_db->quote($this->reference));

			$this->_db->setQuery($query);
			$this->cart = $this->_db->loadObject();
		}

		return $this->cart;
	}

	/**
	 * Return a submit key associated to cart
	 *
	 * @TODO: this is to stay compatible with legacy code from before payment request code
	 *
	 * @return mixed
	 */
	private function getSubmitKey()
	{
		$submitters = $this->getSubmitters();
		$first = reset($submitters);

		return $first->submit_key;
	}
}
