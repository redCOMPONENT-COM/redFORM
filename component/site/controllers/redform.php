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
		$model = $this->getModel('redform');

		$result = $model->apisaveform();

		JPluginHelper::importPlugin('redform');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterRedformSavedSubmission', array(&$result));

		$referer = $app->input->get('referer');

		if (!$result)
		{
			$msg = JText::_('COM_REDFORM_Sorry_there_was_a_problem_with_your_submission') . ': ' . $model->getError();

			$this->setRedirect($referer, $msg, 'error');
			$this->redirect();
		}

		if ($url = $model->hasActivePayment($result->submit_key))
		{
			$url = 'index.php?option=com_redform&controller=payment&task=select&key=' . $result->submit_key;
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
}
