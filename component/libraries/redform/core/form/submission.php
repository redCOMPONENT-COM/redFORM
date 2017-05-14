<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCoreFormSubmission
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfCoreFormSubmission
{
	protected $formId;

	protected $moduleId;

	protected $formModel;

	protected $submitKey;

	protected $answers;

	/**
	 * Constructor
	 *
	 * @param   int  $formId  form id associated to submission
	 */
	public function __construct($formId = null)
	{
		if ($formId)
		{
			$formId = (int) $formId;
			$this->formId = $formId;
		}
	}

	/**
	 * Getter
	 *
	 * @param   string  $name  property to get
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'submit_key':
				return $this->submitKey;

			case 'module_id':
				return $this->moduleId;

			case 'posts':
				$posts = array();

				foreach ($this->answers AS $answer)
				{
					$posts[] = array('sid' => $answer->sid);
				}

				return $posts;
		}

		throw new RuntimeException('Unaccessible or undefined property: ' . $name);
	}

	/**
	 * Set submit key
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return void
	 */
	public function setSubmitKey($submit_key)
	{
		$this->submitKey = $submit_key;
	}

	/**
	 * save data to database
	 *
	 * @param   string  $integration_key  integration key
	 * @param   array   $options          options: skip_captcha, ...
	 * @param   array   $formData         form data, leave null to use posted data
	 *
	 * @return RdfCoreFormSubmission
	 *
	 * @throws RdfExceptionSubmission
	 */
	public function apisaveform($integration_key = '', $options = array(), $formData = array())
	{
		$app = JFactory::getApplication();

		// Check the token
		$token = RdfCore::getToken();

		// Get data from post if not specified
		$data = $formData;
		$data['form_id']    = isset($data['form_id']) ? $data['form_id'] : $app->input->getInt('form_id', 0);
		$data['module_id']  = isset($data['module_id']) ? $data['module_id'] : $app->input->getInt('module_id', 0);
		$data['submit_key'] = isset($data['submit_key']) ? $data['submit_key'] : $app->input->getCmd('submit_key', false);
		$data['nbactive']   = isset($data['nbactive']) ? $data['nbactive'] : $app->input->getInt('nbactive', 1);
		$data['currency']   = isset($data['currency']) ? $data['currency'] : $app->input->getCmd('currency', '');
		$data[$token]       = isset($data[$token]) ? $data[$token] : $app->input->getCmd($token, '');

		if (empty($data['submit_key']))
		{
			$submit_key = $this->submitKey ?: uniqid();
		}
		else
		{
			$submit_key = $data['submit_key'];
		}

		$this->setSubmitKey($submit_key);

		if (isset($data['module_id']))
		{
			$this->moduleId = $data['module_id'];
		}

		// Get the form details
		$this->formId = $data['form_id'];
		$form = $this->getForm();

		$currency = $data['currency'] ? $data['currency'] : $form->currency;

		// Load the fields
		$fieldlist = $this->getfields($form->id);

		// Number of submitted active forms (min is 1)
		$totalforms = isset($data['nbactive']) ? $data['nbactive'] : 1;

		$allanswers = array();

		// Loop through the different forms
		for ($signup = 1; $signup <= $totalforms; $signup++)
		{
			// New answers object
			$answers = new RdfAnswers;
			$answers->setFormId($form->id);

			$submitterId = isset($data['submitter_id' . $signup])
				? (int) $data['submitter_id' . $signup]
				: $app->input->getInt('submitter_id' . $signup, 0);

			if ($submitterId)
			{
				$answers->setSid($submitterId);
			}

			$answers->setFormId($data['form_id']);
			$answers->setSubmitKey($submit_key);
			$answers->setIntegration($integration_key);
			$answers->setCurrency($currency);

			if (isset($options['baseprice']))
			{
				if (is_array($options['baseprice']))
				{
					$answers->initPrice(
						isset($options['baseprice'][$signup - 1]) ? $options['baseprice'][$signup - 1] : 0,
						isset($options['basevat'][$signup - 1]) ? $options['basevat'][$signup - 1] : 0
					);
				}
				else
				{
					$answers->initPrice($options['baseprice'], isset($options['basevat']) ? $options['basevat'] : 0);
				}
			}

			// Create an array of values to store
			$postvalues = array();

			if ($formData)
			{
				// Remove the _X parts, where X is the form (signup) number
				foreach ($formData as $key => $value)
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
			}

			// Build up field list
			foreach ($fieldlist as $field)
			{
				$clone = clone $field;

				// Get the answers
				if (isset($postvalues['field' . $field->id]))
				{
					$clone->setValue($postvalues['field' . $clone->id]);
					$answers->addField($clone);
				}
				else
				{
					$clone->getValueFromPost($signup);
					$answers->addField($clone);
				}
			}

			if (isset($options['extrafields'][$signup]))
			{
				foreach ($options['extrafields'][$signup] as $field)
				{
					$answers->addField($field);
				}
			}

			$allanswers[] = $answers;
		}

		$this->answers = $allanswers;

		// Save to session in case we need to display form again
		$sessiondata = array();

		foreach ($allanswers as $a)
		{
			$sessiondata[] = $a->toSession();
		}

		$app->setUserState('formdata' . $data['form_id'], $sessiondata);

		// Captcha verification
		if (!isset($data[$token]))
		{
			throw new RdfExceptionSubmission('Form integrity check failed');
		}

		$check_captcha = JFactory::getSession()->get('checkcaptcha' . $data[$token], 0);

		if ($check_captcha)
		{
			JPluginHelper::importPlugin('redform_captcha');
			$res = true;
			$dispatcher = JDispatcher::getInstance();
			$results = $dispatcher->trigger('onCheckCaptcha', array(&$res));

			if (count($results) && $res == false)
			{
				throw new RdfExceptionSubmission(JText::_('COM_REDFORM_CAPTCHA_WRONG'));
			}
		}

		// Save to session: data is saved to session using the submit key
		if (isset($options['savetosession']))
		{
			$sessiondata = array();

			foreach ($allanswers as $a)
			{
				$sessiondata[] = $a->toSession();
			}

			$app->setUserState($submit_key, $sessiondata);

			return $this;
		}

		// Else save to db !
		foreach ($allanswers as $answers)
		{
			$res = $answers->savedata();

			if (!$res)
			{
				throw new RdfExceptionSubmission(JText::_('COM_REDFORM_SAVE_ANSWERS_FAILED'));
			}
			else
			{
				// Delete session data
				$app->setUserState('formdata' . $form->id, null);
			}

			if ($answers->isNew())
			{
				$this->updateMailingList($answers);
			}

			// Send email to maintainers
			$this->notifymaintainer($answers);
		}

		// Send a submission mail to the submitters if set
		if ($answers->isNew() && $form->submitterinform)
		{
			foreach ($allanswers as $answers)
			{
				$this->notifysubmitter($answers);
			}
		}

		return $this;
	}

	/**
	 * Create a new submission from fields
	 *
	 * @param   array   $fields       RdfRfield fields, with value set
	 * @param   string  $integration  integration key
	 * @param   array   $options      options: baseprice, currency
	 *
	 * @return boolean true on success
	 */
	public function quicksubmit($fields, $integration = null, $options = null)
	{
		$submit_key = uniqid();
		$this->submit_key = $submit_key;

		$form = $this->getForm();

		// New answers object
		$answers = new RdfAnswers;
		$answers->setFormId($form->id);
		$answers->setIntegration($integration);
		$answers->setSubmitKey($submit_key);

		if (isset($options['baseprice']))
		{
			$answers->initPrice($options['baseprice'], isset($options['basevat']) ? $options['basevat'] : 0);
		}

		if (isset($options['currency']))
		{
			$answers->setCurrency($options['currency']);
		}

		// Build up field list
		foreach ($fields as $field)
		{
			$answers->addField($field);
		}

		if (isset($options['extrafields'][0]))
		{
			foreach ($options['extrafields'][0] as $field)
			{
				$answers->addField($field);
			}
		}

		if (!$answers->savedata(false))
		{
			throw new RuntimeException('redFORM quicksubmit data save failed');
		}

		$this->updateMailingList($answers);

		// Send email to maintainers
		$this->notifymaintainer($answers);

		// Send a submission mail to the submitters if set
		if ($form->submitterinform)
		{
			$this->notifysubmitter($answers);
		}

		$this->answers = array($answers);

		return $this;
	}

	/**
	 * Return true if submission has associated price
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return boolean
	 */
	public function hasActivePayment($submitKey = null)
	{
		if (!$submitKey)
		{
			$submitKey = $this->submitKey;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('pr.id');
		$query->from('#__rwf_submitters AS s');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id');
		$query->join('INNER', '#__rwf_payment_request AS pr ON pr.submission_id = s.id');
		$query->where('s.submit_key = ' . $db->quote($submitKey));
		$query->where('pr.paid = 0');
		$query->where('f.activatepayment = 1');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res ? true : false;
	}

	/**
	 * return form redirect
	 *
	 * @return mixed false if not set, or string
	 */
	public function getFormRedirect()
	{
		$model = $this->getFormModel();

		$redirect = trim($model->getForm()->redirect);

		return $redirect ? $redirect : false;
	}

	/**
	 * send email to form maintaineers or/and selected recipients
	 *
	 * @param   RdfAnswers  $answers  answers
	 *
	 * @return boolean
	 */
	public function notifymaintainer($answers)
	{
		$helper = new RdfHelperNotifymaintainer($answers);

		return $helper->notify();
	}

	/**
	 * Get cart reference for the submission
	 *
	 * @return mixed
	 */
	public function getCartReference()
	{
		if (!$this->submitKey)
		{
			throw new RuntimeException('missing submit key');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('c.reference')
			->from('#__rwf_cart AS c')
			->join('INNER', '#__rwf_cart_item AS ci ON ci.cart_id = c.id')
			->join('INNER', '#__rwf_payment_request AS pr ON pr.id = ci.payment_request_id')
			->join('INNER', '#__rwf_submitters AS s ON s.id = pr.submission_id')
			->where('s.submit_key = ' . $db->q($this->submitKey));

		$db->setQuery($query);

		if ($res = $db->loadResult())
		{
			return $res;
		}
		elseif ($this->hasActivePayment())
		{
			$cart = new RdfCorePaymentCart;
			$cart->getNewCart($this->submitKey);

			return $cart->reference;
		}

		// No cart
		return false;
	}

	/**
	 * Return full submission data, optionally only for specified sids
	 *
	 * @param   array  $sids  array of sid to restrict to
	 *
	 * @return RdfCoreFormSubmission
	 */
	public function load($sids = null)
	{
		if (!$this->answers)
		{
			if (!$sids)
			{
				$sids = $this->getSids($this->submitKey);
			}

			foreach ($sids as $sid)
			{
				$answers = $this->getSidAnswers($sid);
				$this->answers[] = $answers;
			}
		}

		return $this;
	}

	/**
	 * Get sids from submit key
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return mixed
	 */
	public function getSids($submitKey)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__rwf_submitters');
		$query->where('submit_key = ' . $db->quote($submitKey));

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Get RdfAnswers for submission
	 *
	 * @return array
	 */
	public function getSingleSubmissions()
	{
		return $this->answers;
	}

	/**
	 * Get RdfAnswers for index
	 *
	 * @param   int  $index  index
	 *
	 * @return RdfAnswers
	 */
	public function getSingleSubmission($index = 0)
	{
		if (count($this->answers) > $index)
		{
			return $this->answers[$index];
		}

		return $this->answers;
	}

	/**
	 * Get RdfAnswers for first sid
	 *
	 * @return RdfAnswers
	 */
	public function getFirstSubmission()
	{
		return $this->getSingleSubmission(0);
	}

	/**
	 * Get RdfAnswers for sid
	 *
	 * @param   int  $sid  submitter id
	 *
	 * @return RdfAnswers
	 */
	public function getSubmissionBySid($sid)
	{
		if (!empty($this->answers))
		{
			foreach ($this->answers as $rdfanswers)
			{
				if ($rdfanswers->sid == $sid)
				{
					return $rdfanswers;
				}
			}
		}

		return false;
	}

	/**
	 * Add a single submission
	 *
	 * @param   RdfAnswers  $answers  answers
	 *
	 * @return void
	 */
	public function addSubSubmission(RdfAnswers $answers)
	{
		$this->answers[] = $answers;
	}

	/**
	 * Return submission associated to single sid
	 *
	 * @param   int  $sid  submitter id
	 *
	 * @return RdfAnswers
	 */
	protected function getSidAnswers($sid)
	{
		$db = JFactory::getDbo();

		$formId = $this->getForm()->id;

		// Get data
		$query = $db->getQuery(true)
			->select('s.id as sid, f.*, s.price')
			->select('CASE WHEN (s.currency) THEN s.currency ELSE fo.currency END as currency')
			->from('#__rwf_forms_' . $formId . ' AS f')
			->join('INNER', '#__rwf_submitters AS s on s.answer_id = f.id')
			->join('INNER', '#__rwf_forms AS fo on fo.id = s.form_id')
			->where('s.id = ' . (int) $sid);
		$db->setQuery($query);
		$submissionsData = $db->loadObject();

		$fields = $this->getFormModel()->getFormFields();

		$answers = new RdfAnswers;
		$answers->setSubmitKey($this->submitKey);
		$answers->setSid($sid);
		$answers->setFormId($formId);
		$answers->setCurrency($submissionsData->currency);

		foreach ($fields as $field)
		{
			if (isset($submissionsData->{'field_' . $field->field_id}))
			{
				$field->setValueFromDatabase($submissionsData->{'field_' . $field->field_id});
			}

			$answers->addField($field);
		}

		return $answers;
	}

	/**
	 * Send notification to submitter
	 *
	 * @param   RdfAnswers  $answers  answers
	 *
	 * @return boolean
	 */
	protected function notifysubmitter(RdfAnswers $answers)
	{
		return $answers->sendSubmitterNotification();
	}

	/**
	 * Adds email from answers to mailing list
	 *
	 * @param   RdfAnswers  $answers  answers
	 *
	 * @return boolean
	 *
	 * @deprecated it's better to use the dedicated fields than integration directly in email field
	 */
	protected function updateMailingList(RdfAnswers $answers)
	{
		// Mailing lists management
		// Get info from answers
		$fullname  = $answers->getFullname() ? $answers->getFullname() : $answers->getUsername();
		$listnames = $answers->getListNames();

		JPluginHelper::importPlugin('redform_mailing');
		$dispatcher = JDispatcher::getInstance();

		foreach ((array) $listnames as $field_id => $lists)
		{
			// Make sure there is an associated email
			if (!$lists['email'])
			{
				$emails = $answers->getSubmitterEmails();
				$email = reset($emails);
			}
			else
			{
				$email = $lists['email'];
			}

			$subscriber = new stdclass;
			$subscriber->name  = empty($fullname) ? $email : $fullname;
			$subscriber->email = $email;

			$integration = $this->getMailingList($field_id);

			foreach ((array) $lists['lists'] as $mailinglistname)
			{
				$dispatcher->trigger('subscribe', array($integration, $subscriber, $mailinglistname));
			}
		}

		return true;
	}

	/**
	 * return mailing list integration name associated to field
	 *
	 * @param   int  $field_id  field id
	 *
	 * @return  string mailing list integrationname
	 */
	private function getMailingList($field_id)
	{
		$field = RdfRfieldFactory::getFormField($field_id);

		return $field->getParam('mailinglist');
	}

	/**
	 * Get form info
	 *
	 * @return mixed|object
	 */
	protected function getForm()
	{
		$model = $this->getFormModel();

		return $model->getForm();
	}

	/**
	 * Get form fields
	 *
	 * @return array
	 */
	protected function getFields()
	{
		$model = $this->getFormModel();

		return $model->getFormFields();
	}

	/**
	 * Return Form model
	 *
	 * @return RdfCoreModelForm
	 */
	protected function getFormModel()
	{
		if (!$this->formModel)
		{
			$this->formModel = new RdfCoreModelForm($this->formId);
		}

		return $this->formModel;
	}
}
