<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

/**
 * redFORM Values controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       3.0
 */
class RedformControllerValues extends RControllerAdmin
{
	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return void
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$pks   = $this->input->post->get('option-id', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');
		$pks = array_slice($pks, 0, -1);

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}
}
