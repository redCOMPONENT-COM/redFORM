<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Fields Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.5
 */
class RedformControllerFields extends RControllerAdmin
{
	/**
	 * Filter item(s)
	 *
	 * @return  void
	 */
	public function filter()
	{
		$formId = $this->input->getInt('formId');

		$model = $this->getModel('Fields', 'RedformModel', array('ignore_request' => true));

		if ($formId)
		{
			$model->setState('filter.form_id', $formId);
		}

		$model->setState('limit', 0);
		$model->setState('list.ordering', 'f.field');
		$model->setState('list.direction', 'ASC');
		$fields = $model->getItems();

		$fields = array_map(
			function ($field)
			{
				return array('value' => $field->id, 'text' => $field->field);
			},
			$fields
		);

		echo json_encode($fields);

		JFactory::getApplication()->close();
	}
}
