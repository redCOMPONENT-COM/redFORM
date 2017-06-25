<?php
/**
 * @package     Redform.Frontend
 * @subpackage  mod_orders_stats
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Orders model
 *
 * @since  1.0
 */
class ModorderscompanyLibModelOrders extends RModel
{
	private $params;

	/**
	 * Constructor
	 *
	 * @param   array  $params  params
	 */
	public function __construct($params)
	{
		parent::__construct();

		$this->params = $params;
	}

	/**
	 * Get orders
	 *
	 * @param   int  $formId  form id
	 *
	 * @return array
	 */
	public function getOrders($formId)
	{
		$orders = array();

		$submissions = $this->getSubmissions($formId);

		foreach ($submissions as $submission)
		{
			$orders[] = $this->mapSubmission($submission);
		}

		return $orders;
	}

	/**
	 * Get submissions from db
	 *
	 * @param   int  $formId  form id
	 *
	 * @return mixed
	 */
	private function getSubmissions($formId)
	{
		$model = RModel::getAdminInstance('Submitters', array('ignore_request' => true), 'com_redform');
		$model->setState('filter.form_id', $formId);

		if ($this->formUsesConfirm($formId))
		{
			$model->setState('filter.confirmed', 1);
		}

		$model->setState('filter.from', date('Y-m-1 00:00:00', time()));
		$model->setState('filter.to', date('Y-m-t 23:59:59', time()));
		$model->setState('limit', 0);

		return $model->getItems();
	}

	/**
	 * Check if form requires confirmation
	 *
	 * @param   int  $formId  form id
	 *
	 * @return int
	 */
	private function formUsesConfirm($formId)
	{
		$table = RTable::getAdminInstance('Form', array(), 'com_redform');
		$table->load($formId);

		return $table->enable_confirmation;
	}

	/**
	 * Map submission to order
	 *
	 * @param   object  $submission  data
	 *
	 * @return ModorderscompanyLibOrder
	 */
	private function mapSubmission($submission)
	{
		$order = new ModorderscompanyLibOrder;

		$order->date = $submission->submission_date;

		if ($val = $this->mapField('elFields', $submission))
		{
			// Checkbox or radio depending on form
			if ($val == 1 || $val == 'new')
			{
				$order->hasElec = 1;
			}
		}

		if ($val = $this->mapField('gasFields', $submission))
		{
			// Checkbox or radio depending on form
			if ($val == 1 || $val == 'new')
			{
				$order->hasGas = 1;
			}
		}

		return $order;
	}

	/**
	 * Map form fields to order
	 *
	 * @param   string  $name        field name
	 * @param   object  $submission  submission data
	 *
	 * @return string
	 */
	private function mapField($name, $submission)
	{
		$fieldIds = $this->params->get($name);

		if (!(is_array($fieldIds) && count($fieldIds)))
		{
			return false;
		}

		foreach ($fieldIds as $fieldId)
		{
			$field = 'field_' . $fieldId;

			if (isset($submission->$field))
			{
				return $submission->$field;
			}
		}

		return false;
	}
}
