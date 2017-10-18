<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.nikkbconfirmextern
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
 * @subpackage  Redform.nikkbconfirmextern
 * @since       3.0
 */
class plgRedformNikkbconfirmextern extends JPlugin
{
	private $submitKey;

	private $answers;

	private $redFormCore;

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
				echo "<pre>" . print_r($submission, true) . "</pre>"; exit;
			}
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
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
