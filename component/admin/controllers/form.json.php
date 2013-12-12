<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Form Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedformControllerForm extends RDFControllerForm
{
	public function getFields()
	{
		$app = JFactory::getApplication();

		$form_id = $app->input->getInt('id', 0);
		$model = $this->getModel();
		$res = $model->getFieldsOptions('id');

		echo json_encode($res);

		$app->close();
	}
}
