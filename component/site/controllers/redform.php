<?php
/**
 * @package    Redform.Front
 * @copyright  Redform (C) 2008-2014 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

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

		$model = new RdfCoreSubmission($formId);
		$result = $model->apisaveform();

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterRedformSavedSubmission', array(&$result));

		$referer = $app->input->get('referer', '', 'base64');
		$referer = $referer ? base64_decode($referer) : 'index.php';

		if (!$result)
		{
			$msg = JText::_('COM_REDFORM_SORRY_THERE_WAS_A_PROBLEM_WITH_YOUR_SUBMISSION') . ': ' . $model->getError();

			$this->setRedirect($referer, $msg, 'error');
			$this->redirect();
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
			$this->redirect();
		}
		else
		{
			echo $model->getNotificationText();
		}
	}

	/**
	 * Confirm submission by email
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function confirm()
	{
		$input = JFactory::getApplication()->input;
		$key = $input->get('key');
		$model = $this->getModel('Confirm');

		if (!$model->confirm($key))
		{
			throw new Exception('Key not found', 403);
		}

		$input->set('view', 'confirm');

		parent::display();
	}
}
