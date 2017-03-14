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

		try
		{
			$form = RdfEntityForm::load($formId);

			if (!$form->isValid())
			{
				throw new LogicException('Invalid form id');
			}

			$model = new RdfCoreFormSubmission($formId);
			$result = $model->apisaveform();

			JPluginHelper::importPlugin('redform');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterRedformSavedSubmission', array(&$result));

			$notificationModel = $this->getModel('notification');

			echo new JResponseJson($notificationModel->getNotification($result->submit_key));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}
}
