<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.helpscout
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

RLoader::registerPrefix('Plghs', __DIR__ . '/lib');

include 'HelpScout/ApiClient.php';

use HelpScout\ApiClient;

/**
 * plugin class
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.helpscout
 * @since       3.0
 */
class plgRedformHelpscout extends JPlugin
{
	private $submitKey;

	private $answers;

	private $redFormCore;

	private $config;

	/**
	 * @var ApiClient
	 */
	private $client;

	/**
	 * constructor
	 *
	 * @param   object  $subject  subject
	 * @param   array   $params   params
	 */
	public function __construct($subject, $params)
	{
		parent::__construct($subject, $params);
		$this->loadLanguage();
	}

	/**
	 * Called after a submission
	 *
	 * @param   object  $result  result of submission
	 *
	 * @return void
	 */
	public function onAfterRedformSavedSubmission($result)
	{
		if (!$result || !$result->submit_key)
		{
			return;
		}

		$this->answers = array();
		$this->submitKey = $result->submit_key;

		try
		{
			$submissions = $this->getAnswers();

			foreach ($submissions->getSingleSubmissions() as $submission)
			{
				$this->createConversation($submission);
			}
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage('Helpscout: ' . $e->getMessage(), 'error');
		}
	}

	/**
	 * Send submission to salesforce
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return void
	 */
	private function createConversation($submission)
	{
		$formId = $submission->getFormId();

		if (!$formConfig = $this->getFormConfig($formId))
		{
			return;
		}

		$mailboxId = $formConfig->getMailboxId();

		$email = $formConfig->getSubmissionEmail($submission);

		if (!JMailHelper::isEmailAddress($email))
		{
			return;
		}

		$subject = $formConfig->getSubmissionSubject($submission);
		$body = $formConfig->getSubmissionBody($submission);

		$customer = $this->getCustomer($email);

		$customerRef = new \HelpScout\model\ref\CustomerRef;
		$customerRef->setId($customer->getId());
		$customerRef->setEmail($email);

		$mailbox = new \HelpScout\model\ref\MailboxRef;
		$mailbox->setId($mailboxId);

		$conversation = new \HelpScout\model\Conversation;
		$conversation->setSubject($subject);
		$conversation->setMailbox($mailbox);
		$conversation->setCustomer($customerRef);
		$conversation->setType("email");

		// A conversation must have at least one thread
		$thread = new \HelpScout\model\thread\Customer;
		$thread->setType("customer");
		$thread->setBody($body);
		$thread->setStatus("active");

		// Create by: required
		$createdBy = new \HelpScout\model\ref\PersonRef;
		$createdBy->setId($customer->getId());
		$createdBy->setType("customer");
		$thread->setCreatedBy($createdBy);

		if ($assignTo = $formConfig->getAssignedTo())
		{
			$assignedTo = new \HelpScout\model\ref\PersonRef;
			$assignedTo->setId($assignTo);
			$assignedTo->setType("user");
			$thread->setAssignedTo($assignedTo);
		}

		if ($ccList = $formConfig->getCclist())
		{
			$thread->setCcList($ccList);
		}

		if ($bccList = $formConfig->getBcclist())
		{
			$thread->setBccList($bccList);
		}

		if ($tags = $formConfig->getTags())
		{
			$conversation->setTags($tags);
		}

		$conversation->setThreads(array($thread));
		$conversation->setCreatedBy($createdBy);
		$this->getClient()->createConversation($conversation);
	}

	/**
	 * Return config for form
	 *
	 * @param   int  $formId  form id
	 *
	 * @return PlghsConfigForm
	 */
	private function getFormConfig($formId)
	{
		$config = $this->getConfig();

		return isset($config[$formId]) ? $config[$formId] : false;
	}

	/**
	 * Return global config
	 *
	 * @return array
	 */
	private function getConfig()
	{
		if (!$this->config)
		{
			if (!file_exists($this->params->get('configPath')))
			{
				throw new InvalidArgumentException('Config file not found');
			}

			$data = file_get_contents($this->params->get('configPath'));

			$xml = new SimpleXMLElement($data);

			$this->config = array();

			foreach ($xml->form as $element)
			{
				$formId = (int) $element->id;
				$this->config[$formId] = new PlghsConfigForm($element);
			}
		}

		return $this->config;
	}

	/**
	 * Return api client
	 *
	 * @return ApiClient
	 */
	private function getClient()
	{
		if (!$this->client)
		{
			if (!$key = $this->params->get('apiKey'))
			{
				throw new InvalidArgumentException('Missing api key');
			}

			$this->client = ApiClient::getInstance();
			$this->client->setKey($key);
		}

		return $this->client;
	}

	/**
	 * Return Customer
	 *
	 * @param   string  $email  email
	 *
	 * @return \HelpScout\model\ref\CustomerRef
	 */
	private function getCustomer($email)
	{
		$client = $this->getClient();
		$customers = $client->searchCustomersByEmail($email);

		if ($customers->getCount())
		{
			$items = $customers->getItems();

			return $items[0];
		}
		else
		{
			$customer = new \HelpScout\model\Customer;
			$emailObj = new \HelpScout\model\customer\EmailEntry;
			$emailObj->setValue($email);

			$customer->setEmails(array($emailObj));
			$client->createCustomer($customer);

			return $customer;
		}
	}

	/**
	 * Return answers for sid
	 *
	 * @return RdfCoreFormSubmission
	 */
	private function getAnswers()
	{
		if (!$this->answers)
		{
			$this->answers = $this->getRedFormCore()->getAnswers($this->submitKey);
		}

		return $this->answers;
	}

	/**
	 * Get redformcore
	 *
	 * @return RedFormCore
	 */
	private function getRedFormCore()
	{
		if (!$this->redFormCore)
		{
			$this->redFormCore = new RdfCore;
		}

		return $this->redFormCore;
	}
}
