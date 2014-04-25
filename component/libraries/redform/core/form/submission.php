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

	public function setSubmitKey($submitKey)
	{
		$this->submitKey = $submitKey;
	}

	public function addSubSubmission(RdfAnswers $answers)
	{
		$this->sidSubmissions[] = $answers;
	}

	public function getSingleSubmissions()
	{
		return $this->sidSubmissions;
	}

	public function getSingleSubmission($index = 0)
	{
		if (count($this->sidSubmissions) > $index)
		{
			return $this->sidSubmissions[$index];
		}

		return $this->sidSubmissions;
	}

	public function getFirstSubmission()
	{
		return $this->getSingleSubmission(0);
	}
}
