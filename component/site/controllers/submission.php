<?php
/**
 * @package    Redform.Front
 * @copyright  Redform (C) 2008-2014 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Redform Payments Controller
 *
 * @package  Redform.Front
 * @since    3.3.18
 */
class RedformControllerSubmission extends RedformController
{
	/**
	 * Check if user can edit
	 *
	 * @return boolean
	 *
	 * @since 3.3.18
	 */
	public function allowEdit()
	{
		$user = JFactory::getUser();

		if ($user->guest)
		{
			return false;
		}

		$recordId = $this->input->getInt('id');

		$entity = RdfEntitySubmitter::load($recordId);

		if (!$entity->getForm()->params->get('allow_frontend_edit', 0))
		{
			return false;
		}

		if ($entity->user_id != $user->id)
		{
			return false;
		}

		return true;
	}

	/**
	 * Display edit page
	 *
	 * @return void
	 */
	public function edit()
	{
		$recordId = $this->input->getInt('id');

		// Access check.
		if (!$this->allowEdit())
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect($this->getReturnPage());

			return false;
		}

		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=submissionform&layout=edit&id=' . $recordId, false
			)
		);

		return true;
	}

	/**
	 * Method to save a record.
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save()
	{
		// Save redform data
		$rfcore = new RdfCore;

		try
		{
			$rfcore->saveAnswers('');
			$this->setMessage(JText::_('COM_REDFORM_SUBMISSION_SAVE_SUCCESS'));
		}
		catch (RdfExceptionSubmission $e)
		{
			$msg = JText::_('COM_REDFORM_SUBMISSION_SAVE_FAILED');
			$this->setMessage($msg . ' - ' . $e->getMessage(), 'error');
		}

		$this->setRedirect(RdfHelperRoute::getSubmissionRoute($this->input->getInt('submitter_id1')));

		return true;
	}

	/**
	 * Method to cancel a record saving
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function cancel()
	{
		$this->setRedirect(RdfHelperRoute::getSubmissionRoute($this->input->getInt('submitter_id1')));

		return true;
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return  string	The return URL.
	 *
	 * @since   1.6
	 */
	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return)))
		{
			return JUri::base();
		}
		else
		{
			return base64_decode($return);
		}
	}
}
