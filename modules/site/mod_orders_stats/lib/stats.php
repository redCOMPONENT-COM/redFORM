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
 * mod_orders_stats order
 *
 * @since  1.0
 */
class ModordersstatsLibStats
{
	/**
	 * @var array
	 */
	private $orders;

	public function __construct($orders)
	{
		$this->orders = $orders;
	}

	public function getStats()
	{
		$res = new stdClass;
		$res->topSales = $this->getTopSales();
		$res->companySales = $this->getCompanySales();

		return $res;
	}

	private function getTopSales()
	{
		$grouped = array();

		foreach ($this->orders as $order)
		{
			if (!isset($grouped[$order->salesPerson]))
			{
				$stat = new ModordersstatsLibStatSalesperson($order->salesPerson, $order->company);
				$grouped[$order->salesPerson] = $stat;
			}

			$grouped[$order->salesPerson]->addOrder($order);
		}

		usort($grouped, array($this, 'sortTop'));

		return $grouped;
	}

	private function getCompanySales()
	{
		$grouped = array();

		foreach ($this->orders as $order)
		{
			if (!isset($grouped[$order->company]))
			{
				$stat = new ModordersstatsLibStatCompany($order->company);
				$grouped[$order->company] = $stat;
			}

			$grouped[$order->company]->addOrder($order);
		}

		usort($grouped, array($this, 'sortTop'));

		return $grouped;

	}

	private function sortTop($a, $b)
	{
		if ($a->total == $b->total)
		{
			return strcasecmp($a->name, $b->name);
		}

		return $a->total > $b->total ? -1 : 1;
	}
}