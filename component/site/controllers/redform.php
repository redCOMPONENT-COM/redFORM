<?php
/**
 * @package    Redform.Front
 * @copyright  Redform (C) 2008-2014 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Redform Controller
 *
 * @package  Redform.Front
 * @since    2.5
 */
class RedformControllerRedform extends RedformController
{
	/**
	 * save the posted form data.
	 *
	 * @return void
	 */
	public function save()
	{
		$app = JFactory::getApplication();

		$formId = $app->input->getInt('form_id', 0);
		$referer = $app->input->get('referer', '', 'base64');
		$referer = $referer ? base64_decode($referer) : 'index.php';

		$model = new RdfCoreFormSubmission($formId);
		$form = RdfEntityForm::load($formId);

		try
		{
			$result = $model->apisaveform();
		}
		catch (Exception $e)
		{
			$msg = JText::_('COM_REDFORM_SORRY_THERE_WAS_A_PROBLEM_WITH_YOUR_SUBMISSION') . ': ' . $e->getMessage();
			$this->setRedirect($referer, $msg, 'error');

			return;
		}

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterRedformSavedSubmission', array(&$result));

		if ($url = $model->hasActivePayment())
		{
			$url = 'index.php?option=com_redform&task=payment.select&key=' . $result->submit_key;
			$this->setRedirect($url);
			$this->redirect();
		}

		if ($url = $model->getFormRedirect())
		{
			$this->setRedirect($url);
		}
		elseif ($form->submitnotification)
		{
			$this->setRedirect('index.php?option=com_redform&view=notification&submitKey=' . $result->submit_key);
		}
		else
		{
			$this->setRedirect($referer, JText::_('COM_REDFORM_DEFAULT_SUBMISSION_SUCCESS_MESSAGE'));
		}

		$this->redirect();
	}

	/**
	 * Confirm submission
	 *
	 * @return void
	 */
	public function confirm()
	{
		$input = JFactory::getApplication()->input;
		$type = $input->get('type');

		// If no type, then it's regular email confirmation
		if (!$type)
		{
			return $this->emailConfirm();
		}

		// Else it should be handled by plugins
		$updatedIds = array();

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onConfirm', array($type, &$updatedIds));

		$input->set('view', 'confirm');
		$input->set('updatedIds', $updatedIds);

		parent::display();
	}

	/**
	 * Prefill a form and redirect
	 *
	 * @return void
	 */
	public function prefill()
	{
		$fields = $this->input->get('fields', null, 'array');
		$formId = $this->input->getInt('formId');
		$return = $this->input->getString('return');

		JFactory::getApplication()->setUserState('formdata' . $formId, array((object) $fields));
		$this->setRedirect($return);
		$this->redirect();
	}

	/**
	 * Confirm submission by email
	 *
	 * @return void
	 */
	private function emailConfirm()
	{
		$input = JFactory::getApplication()->input;
		$key = $input->get('key');

		if ($key)
		{
			$model = $this->getModel('Confirm');

			if ($model->confirm($key))
			{
				$model->sendNotification();
				$input->set('updatedIds', $model->getState('updatedIds'));
			}
		}

		$input->set('view', 'confirm');

		parent::display();
	}
}
