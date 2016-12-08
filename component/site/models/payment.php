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
	 * Cart entity
	 *
	 * @var RdfEntityCart
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
			->join('LEFT', '#__rwf_billinginfo AS b ON b.cart_id = ci.cart_id')
			->where('f.requirebilling = 1')
			->where('ci.cart_id = ' . $cart->id)
			->where('b.id IS NULL');
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
			$helper = new RdfCorePaymentGateway($details);

			$this->gateways = $helper->getGateways();
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
		$helper = new RdfCorePaymentGateway($details);

		return $helper->getOptions();
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

		return $cart->getForm();
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
	 * @return RdfPaymentInfo
	 *
	 * @throws Exception
	 */
	public function getPaymentDetails()
	{
		$cart = $this->getCart();
		$key = $cart->reference;

		if (!isset($this->paymentsDetails[$key]))
		{
			$this->paymentsDetails[$key] = new RdfPaymentInfo($cart);
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
			? JText::_('COM_REDFORM_PAYMENT_SUBMITTER_NOTIFICATION_EMAIL_BODY_DEFAULT')
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
		$doSend = true;

		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeSendPaymentNotificationSubmitter', array(&$mailer, $this->getCart(), &$doSend));

		if (!$doSend)
		{
			return true;
		}

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
	 *
	 * @throws Exception
	 */
	public function hasAlreadyPaid()
	{
		if (!$this->reference)
		{
			throw new Exception('Missing reference');
		}

		$query = $this->_db->getQuery(true);

		$query->select('pr.id')
			->from('#__rwf_payment_request AS pr')
			->join('INNER', '#__rwf_cart_item AS ci on ci.payment_request_id = pr.id')
			->join('INNER', '#__rwf_cart AS c ON c.id = ci.cart_id')
			->where('c.reference = ' . $this->_db->quote($this->reference))
			->where('pr.paid = 1');

		$this->_db->setQuery($query);

		return $this->_db->loadResult() ? true : false;
	}

	/**
	 * Get Cart for submission
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return RdfEntityCart
	 */
	public function getSubmissionCart($submitKey)
	{
		$helper = new RdfCorePaymentCart;
		$cart = $helper->getSubmissionCart($submitKey);

		return $cart;
	}

	/**
	 * return a new cart for payment
	 *
	 * @param   string  $submitKey  submitkey for which we want a payment
	 *
	 * @return RdfCorePaymentCart
	 */
	public function getNewCart($submitKey)
	{
		$cart = new RdfCorePaymentCart;
		$cart->getNewCart($submitKey);

		return $cart;
	}

	/**
	 * get cart data from db
	 *
	 * @return RdfEntityCart
	 */
	public function getCart()
	{
		if (!$this->cart)
		{
			$this->cart = RdfEntityCart::getInstance();
			$this->cart->loadByReference($this->reference);
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
