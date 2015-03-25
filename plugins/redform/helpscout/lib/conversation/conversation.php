<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.helpscout
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * helpscout lib
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.helpscout
 * @since       3.0
 */
class PlghsConversation
{
	/**
	 * @var ApiClient
	 */
	private $client;

	/**
	 * @var PlghsConfigForm
	 */
	private $config;

	/**
	 * @var RdfAnswers
	 */
	private $submission;

	/**
	 * Constructor
	 *
	 * @param   PlghsConfigForm       $config      config
	 * @param   RdfAnswers            $submission  submission
	 * @param   \HelpScout\ApiClient  $client      client
	 */
	public function __construct(PlghsConfigForm $config, RdfAnswers $submission, \HelpScout\ApiClient $client)
	{
		$this->config = $config;
		$this->submission = $submission;
		$this->client = $client;
	}

	/**
	 * Send submission
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @throws \HelpScout\ApiException
	 */
	public function send()
	{
		$mailboxId = $this->config->getMailboxId();

		$email = $this->config->getSubmissionEmail($this->submission);

		if (!JMailHelper::isEmailAddress($email))
		{
			return;
		}

		$subject = $this->config->getSubmissionSubject($this->submission);
		$body = $this->config->getSubmissionBody($this->submission);
		$customer = $this->getCustomer();

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

		if ($assignTo = $this->config->getAssignedTo())
		{
			$assignedTo = new \HelpScout\model\ref\PersonRef;
			$assignedTo->setId($assignTo);
			$assignedTo->setType("user");
			$thread->setAssignedTo($assignedTo);
		}

		if ($ccList = $this->config->getCclist())
		{
			$thread->setCcList($ccList);
		}

		if ($bccList = $this->config->getBcclist())
		{
			$thread->setBccList($bccList);
		}

		if ($tags = $this->config->getTags())
		{
			$conversation->setTags($tags);
		}

		if ($attachments = $this->getAttachments())
		{
			$thread->setAttachments($attachments);
		}

		$conversation->setThreads(array($thread));
		$conversation->setCreatedBy($createdBy);
		$this->getClient()->createConversation($conversation);
	}

	/**
	 * Return Customer
	 *
	 * @return \HelpScout\model\ref\CustomerRef
	 */
	private function getCustomer()
	{
		if ($customer = $this->findCustomer())
		{
			return $customer;
		}
		else
		{
			return $this->createCustomer();
		}
	}

	/**
	 * Try to find existing customer
	 *
	 * @return bool
	 */
	private function findCustomer()
	{
		$client = $this->getClient();
		$email = $this->config->getSubmissionEmail($this->submission);
		$customers = $client->searchCustomersByEmail($email);

		if ($customers->getCount())
		{
			$items = $customers->getItems();

			return $items[0];
		}

		return false;
	}

	/**
	 * Create the customer
	 *
	 * @return \HelpScout\model\Customer
	 */
	private function createCustomer()
	{
		$customer = new \HelpScout\model\Customer;

		if ($val = $this->config->getSubmissionFirstname($this->submission))
		{
			$customer->setFirstName($val);
		}

		if ($val = $this->config->getSubmissionLastname($this->submission))
		{
			$customer->setLastName($val);
		}

		if ($val = $this->config->getSubmissionOrganization($this->submission))
		{
			$customer->setOrganization($val);
		}

		$email = $this->config->getSubmissionEmail($this->submission);
		$emailObj = new \HelpScout\model\customer\EmailEntry;
		$emailObj->setValue($email);

		$customer->setEmails(array($emailObj));
		$this->getClient()->createCustomer($customer);

		return $customer;
	}

	/**
	 * Return api client
	 *
	 * @return \HelpScout\ApiClient
	 */
	private function getClient()
	{
		return $this->client;
	}

	private function getAttachments()
	{
		if (!$attachments = $this->config->getAttachments($this->submission))
		{
			return false;
		}

		$res = array();

		foreach ($attachments as $path)
		{
			$filename = basename($path);
			$filename = substr($filename, strpos($filename, "_") + 1);

			$fileinfo = new finfo(FILEINFO_MIME);
			$mime = $fileinfo->file($path);

			$attachment = new \HelpScout\model\Attachment;
			$attachment->setFileName($filename);
			$attachment->setMimeType($mime);
			$attachment->setData(file_get_contents($path));

			$this->getClient()->createAttachment($attachment);

			$res[] = $attachment;
		}

		return $res;
	}
}
