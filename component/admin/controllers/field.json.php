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
class RedformControllerField extends RDFControllerForm
{
	/**
	 * Add field
	 *
	 * @return void
	 */
	public function getValues()
	{
		$app = JFactory::getApplication();

		$fieldId = $app->input->getInt('id', 0);
		$model = $this->getModel();
		$model->setState('field.id', $fieldId);

		$values = $model->getValues();

		echo json_encode($values);

		JFactory::getApplication()->close();
	}

	public function saveOption()
	{
		$app = JFactory::getApplication();

		$cid = $app->input->get('option-id', array(0), 'array');
		JArrayHelper::toInteger($cid);

		$values = $app->input->get('option-value', array(0), 'array');
		JArrayHelper::toString($values);

		$labels = $app->input->get('option-label', array(0), 'array');
		JArrayHelper::toString($labels);

		$prices = $app->input->get('option-price', array(0), 'array');

		$fieldId = $app->input->getInt('id', 0);

		$data = array(
			'id' => $cid[0],
			'value' => $values[0],
			'label' => $labels[0],
			'price' => (float) $prices[0],
			'field_id' => $fieldId
		);

		$model = $this->getModel();
		$model->setState('field.id', $fieldId);
		$res = $model->saveValue($data);

		if ($res)
		{
			$data['id'] = $res;
			echo json_encode($data);
		}
		else
		{
			echo json_encode(array());
		}

		$app->close();
	}

	public function removeValue()
	{
		$app = JFactory::getApplication();

		$optionId = $app->input->getInt('optionId', 0);

		$model = $this->getModel();
		$model->removeValue($optionId);

		echo json_encode(array('success' => 1));

		$app->close();
	}
}
