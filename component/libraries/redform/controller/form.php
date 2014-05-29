<?php
/**
 * @package     Redform.Library
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Controller form.
 *
 * @package     Redform.Library
 * @subpackage  Controller
 * @since       1.0
 */
abstract class RdfControllerForm extends RControllerForm
{
	/**
	 * Method called to save a model state
	 *
	 * @return  void
	 */
	public function saveModelState()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$returnUrl = $input->get('return', 'index.php');

		if ($model = $input->get('model', null))
		{
			$returnUrl = $input->get('return', 'index.php');
			$returnUrl = base64_decode($returnUrl);

			$model = RModel::getAdminInstance(ucfirst($model));

			$state = $model->getState();
		}

		$app->redirect($returnUrl);
	}
}
