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
 * mod_orders_stats helper
 *
 * @since  1.0
 */
class ModordersstatsLibHelper
{
	/**
	 * Get list of categories
	 *
	 * @param   array  $params  Module parameters
	 *
	 * @return array
	 */
	public static function getData($params)
	{
		$model = new ModordersstatsLibModelOrders($params);
		$formIds = $params->get('formIds');

		$orders = array();

		if (!(is_array($formIds) && count($formIds)))
		{
			return false;
		}

		foreach ($formIds as $formId)
		{
			$orders = array_merge($orders, $model->getOrders($formId));
		}

		$model = new ModordersstatsLibStats($orders);
		$stats = $model->getStats();

		return $stats;
	}
}