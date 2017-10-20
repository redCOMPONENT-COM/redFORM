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
	const SELF_REGISTERING_FORM_ID = 91;

	const KURSUS_TYPE_FIELD_ID = 1138;

	const KURSUS_TYPE_FIELD_VALUE_EXTERN = 1;

	private $submitKey;

	private $answers;

	private $redFormCore;

	/**
	 * Called after a submission
	 *
	 * @param   RdfCoreFormSubmission  $result  result of submission
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

			foreach ($submissions->getSingleSubmissions() as $answers)
			{
				if ($answers->getFormId() == self::SELF_REGISTERING_FORM_ID
					&& $answers->getFieldAnswer(self::KURSUS_TYPE_FIELD_ID) == self::KURSUS_TYPE_FIELD_VALUE_EXTERN)
				{
					$this->confirm($answers);
				}
			}
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Confirm submitter
	 *
	 * @param   RdfAnswers  $answers  answers
	 *
	 * @return void
	 *
	 * @since __deploy_version__
	 */
	private function confirm(RdfAnswers $answers)
	{
		$model = RModel::getAdminInstance('Submitter');
		$sid = array($answers->getSid());
		$model->confirm($sid);
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
