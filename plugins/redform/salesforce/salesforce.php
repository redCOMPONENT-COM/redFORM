<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.salesforce
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redform.Plugin
 * @subpackage  Redform.salesforce
 * @since       3.0
 */
class plgRedformSalesforce extends JPlugin
{
	private $submitKey;

	private $answers;

	private $redFormCore;

	private $mapping;

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
				$this->sendSubmission($submission);
			}
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Send submission to salesforce
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return void
	 */
	private function sendSubmission($submission)
	{
		$inputs = array();

		$inputs['oid'] = $this->params->get('oid');
		$inputs['retURL'] = $this->params->get('retURL');

		if ($this->params->get('debug', 0))
		{
			$inputs['debug'] = 1;
		}

		if ($this->params->get('debugEmail'))
		{
			$inputs['debugEmail'] = $this->params->get('debugEmail');
		}

		$formHasMapping = false;

		foreach ($submission->getFields() AS $field)
		{
			if ($name = $this->mappedName($field))
			{
				$formHasMapping = true;
				$inputs[$name] = $field->getValueAsString();
			}
		}

		// Only post if there is something to post...
		if ($formHasMapping)
		{
			$this->postForm($inputs);
		}
	}

	/**
	 * Post data
	 *
	 * @param   array  $inputs  inputs
	 *
	 * @return void
	 *
	 * @throws RuntimeException
	 */
	private function postForm($inputs)
	{
		$inputs = http_build_query($inputs);

		$ch = curl_init();
		$url = "https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $inputs);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (curl_exec($ch) === false)
		{
			throw new RuntimeException('Curl error: ' . curl_error($ch));
		}

		curl_close($ch);
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
	 * Returns the field name mapped to a redFORM field, if defined
	 * each mapping line has to be formatted this way: <redform field id>;<salesforce name>
	 *
	 * @param   RdfRfield  $field  redFORM field
	 *
	 * @return bool|string the salesforce field name, or false if not mapped
	 *
	 * @throws Exception
	 */
	private function mappedName($field)
	{
		if (!$this->mapping)
		{
			$result = array();
			$mapping = $this->params->get('mapping');

			if (!strstr($mapping, ';'))
			{
				throw new Exception('invalid mapping');
			}

			$lines = preg_split("/\R/", $mapping);

			foreach ($lines as $l)
			{
				if ((!(strpos($l, '#') === 0)) && strstr($l, ';'))
				{
					list($fid, $fname) = explode(";", $l);
					$fid = (int) $fid;
					$fname = trim($fname);

					if ($fid)
					{
						$result[$fid] = $fname;
					}
				}
			}

			$this->mapping = $result;
		}

		return isset($this->mapping[$field->fieldId]) ? $this->mapping[$field->fieldId] : false;
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
