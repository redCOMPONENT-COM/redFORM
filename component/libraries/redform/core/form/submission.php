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
	/**
	 * @var string
	 */
	protected $submitKey;

	/**
	 * Array of RdfAnswers
	 * @var array
	 */
	protected $sidSubmissions = array();

	/**
	 * Set submit key
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return void
	 */
	public function setSubmitKey($submitKey)
	{
		$this->submitKey = $submitKey;
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
		$this->sidSubmissions[] = $answers;
	}

	/**
	 * Get RdfAnswers for submission
	 *
	 * @return array
	 */
	public function getSingleSubmissions()
	{
		return $this->sidSubmissions;
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
		if (count($this->sidSubmissions) > $index)
		{
			return $this->sidSubmissions[$index];
		}

		return $this->sidSubmissions;
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
		foreach ($this->sidSubmissions as $rdfanswers)
		{
			if ($rdfanswers->sid == $sid)
			{
				return $rdfanswers;
			}
		}

		return false;
	}
}
