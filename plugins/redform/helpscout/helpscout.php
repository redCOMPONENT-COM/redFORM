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

		$conversation = new PlghsConversation($formConfig, $submission, $this->getClient());
		$conversation->send();
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
			$relPath = trim($this->params->get('configPath'));

			if (!$relPath)
			{
				throw new InvalidArgumentException('Config file path not defined');
			}

			if (substr($relPath, 0, 1) !== "/")
			{
				$relPath = "/" . $relPath;
			}

			if (!file_exists(JPATH_SITE . $relPath))
			{
				throw new InvalidArgumentException('Config file not found: ' . JPATH_SITE . $relPath);
			}

			$data = file_get_contents(JPATH_SITE . $relPath);

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
}
