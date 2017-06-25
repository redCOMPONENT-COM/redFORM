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
 * Mod orders company helper
 *
 * @since  1.0
 */
class ModorderscompanyLibHelper
{
	/**
	 * Get data
	 *
	 * @param   array  $params  Module parameters
	 *
	 * @return array
	 */
	public static function getData($params)
	{
		$model = new ModorderscompanyLibModelOrders($params);
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

		$model = new ModorderscompanyLibStats($orders, $params);
		$stats = $model->getStats();

		return $stats;
	}
}
