<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.gaselkmd
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
 * @subpackage  Redform.gaselkmd
 * @since       3.0
 */
class plgRedformGaselkmd extends JPlugin
{
	private $sids;

	/**
	 * @var RdfCoreFormSubmission
	 */
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
	 * Called after a submission was confirmed
	 *
	 * @param   array  $sids  submission id's
	 *
	 * @return void
	 */
	public function onSubmissionConfirmed($sids)
	{
		if (!$sids)
		{
			return;
		}

		$this->sids = $sids;

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
	 * Send submission to gaselkmd
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return void
	 */
	private function sendSubmission($submission)
	{
		if (!$this->isSubmissionSyncEnabled($submission))
		{
			return;
		}

		$inputs = array();

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

		if ($this->params->get('debug', 0))
		{
			RdfHelperLog::simpleLog('gasel kmd sync: ' . print_r($inputs, true));

			return;
		}

		$ch = curl_init();
		$url = "https://minforsyningplugin.kmd.dk/ButtonOrderRategroupsElectricityAndGasRedirect.aspx";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $inputs);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$resp = curl_exec($ch);

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
			$this->answers = $this->getRedFormCore()->getAnswers($this->sids);
		}

		return $this->answers;
	}

	/**
	 * Returns the field name mapped to a redFORM field, if defined
	 * each mapping line has to be formatted this way: <redform field id>;<gaselkmd name>
	 *
	 * @param   RdfRfield  $field  redFORM field
	 *
	 * @return bool|string the gaselkmd field name, or false if not mapped
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

			$lines = explode("\n", $mapping);

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

	/**
	 * Check if we should sync this submission
	 *
	 * @param   RdfAnswers  $submission  submission
	 *
	 * @return bool
	 */
	private function isSubmissionSyncEnabled($submission)
	{
		$formids = explode(',', $this->params->get('formids'));
		JArrayHelper::toInteger($formids);

		return in_array($submission->getFormId(), $formids);
	}
}
