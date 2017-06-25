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
class ModorderscompanyLibStats
{
	/**
	 * @var array
	 */
	private $orders;

	private $monthStats;

	/**
	 * @var JRegistry
	 */
	private $params;

	public function __construct($orders, JRegistry $params)
	{
		$this->orders = $orders;
		$this->params = $params;
	}

	public function getStats()
	{
		return array(
			'today' => $this->todayStat(),
			'month' => $this->monthStats()
		);
	}

	private function todayStat()
	{
		$stat = new ModorderscompanyLibStatCompany;

		foreach ($this->orders as $order)
		{
			if ($this->isToday($order))
			{
				$stat->addOrder($order);
			}
		}

		return $stat;
	}

	private function monthStats()
	{
		if (!$this->monthStats)
		{
			$days = array();
			$cancelled = $this->getCancelled();

			foreach ($this->orders as $order)
			{
				$dayNumber = (int) date("j", strtotime($order->date));

				if (!isset($days[$dayNumber]))
				{
					$days[$dayNumber] = array();
				}

				$days[$dayNumber][] = $order;
			}

			$elec = 0;
			$gas = 0;
			$monthStats = array();
			$today = (int) date('j');

			for ($i = 1; $i <= $today; $i++)
			{
				$stat = new ModorderscompanyLibStatCompany;
				$stat->day = $i;

				if (isset($days[$i]))
				{
					foreach ($days[$i] as $order)
					{
						$elec += $order->hasElec;
						$gas += $order->hasGas;
					}
				}

				if (isset($cancelled['elec'][$i - 1]))
				{
					$elec -= $cancelled['elec'][$i - 1];
				}

				if (isset($cancelled['gas'][$i - 1]))
				{
					$gas -= $cancelled['gas'][$i - 1];
				}

				$stat->elec = $elec;
				$stat->gas = $gas;

				$monthStats[] = $stat;
			}

			$this->monthStats = array_values($monthStats);
		}

		return $this->monthStats;
	}

	/**
	 * Is this an order from today
	 *
	 * @param   ModorderscompanyLibOrder  $order  order
	 *
	 * @return boolean
	 */
	private function isToday($order)
	{
		return date("Y-m-d") == date("Y-m-d", strtotime($order->date));
	}

	/**
	 * Return cancellations per day
	 *
	 * @return array
	 */
	private function getCancelled()
	{
		$cancelledElec = explode(",", $this->params->get('cancelledElec'));
		JArrayHelper::toInteger($cancelledElec);

		$cancelledGas = explode(",", $this->params->get('cancelledGas'));
		JArrayHelper::toInteger($cancelledGas);

		return array('elec' => $cancelledElec, 'gas' => $cancelledGas);
	}
}
