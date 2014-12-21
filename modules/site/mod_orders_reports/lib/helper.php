<?php
/**
 * @package     Redform.Frontend
 * @subpackage  mod_orders_reports
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * mod_orders_reports helper
 *
 * @since  1.0
 */
class ModordersreportsLibHelper
{
	private $params;

	private $daysStats;

	/**
	 * Get list of categories
	 *
	 * @param   array  $params  Module parameters
	 *
	 * @return array
	 */
	public static function getData($params)
	{
		$instance = new ModordersreportsLibHelper($params);

		return $instance->getStats();
	}

	private function __construct($params)
	{
		$this->params = $params;
	}

	private function getStats()
	{
		return array(
			'today' => $this->getTodayStat(),
			'month' => $this->getMonthStat(),
			'year' => $this->getYearStat()
		);
	}

	private function getTodayStat()
	{
		foreach ($this->getDaysStat() as $date)
		{
			if ($this->isToday(strtotime($date->date)))
			{
				return $date;
			}
		}

		return false;
	}

	private function getMonthStat()
	{
		$monthStats = array();
		$results = array();

		foreach ($this->getDaysStat() as $date)
		{
			if ($this->isThisMonth(strtotime($date->date)))
			{
				$monthStats[date("d", strtotime($date->date))] = $date;
			}
		}

		$cancelled = 0;
		$errors = 0;
		$reports = 0;

		$today = date("d");

		for ($i = 1; $i <= $today; $i++)
		{
			$stat = new ModordersreportsLibStatDayofthemonth;
			$stat->day = $i;

			if (isset($monthStats[$i]))
			{
				$cancelled += $monthStats[$i]->cancelled;
				$errors += $monthStats[$i]->errors;
				$reports += $monthStats[$i]->reports;
			}

			$stat->cancelled = $cancelled;
			$stat->errors = $errors;
			$stat->reports = $reports;

			$results[] = $stat;
		}

		return $results;
	}

	private function getYearStat()
	{
		$yearStats = array();
		$results = array();

		// Init
		for ($i = 1; $i <= date('m'); $i++)
		{
			$yearStats[$i] = array();
		}

		// Group current year stats by month
		foreach ($this->getDaysStat() as $date)
		{
			$timestamp = strtotime($date->date);

			if ($this->isThisYear($timestamp))
			{
				$yearStats[date("n", $timestamp)][] = $date;
			}
		}

		$cancelled = 0;
		$errors = 0;
		$reports = 0;

		foreach ($yearStats as $k => $dates)
		{
			$stat = new ModordersreportsLibStatMonth;
			$stat->month = date("M" ,strtotime("2014-$k-1"));

			foreach ($dates as $day)
			{
				$cancelled += $day->cancelled;
				$errors += $day->errors;
				$reports += $day->reports;
			}

			$stat->cancelled = $cancelled;
			$stat->errors = $errors;
			$stat->reports = $reports;

			$results[] = $stat;
		}

		return $results;
	}

	private function getDaysStat()
	{
		if (!$this->daysStats)
		{
			$data = explode("\n", $this->params->get('rows'));
			$this->daysStats = array();

			foreach ($data as $line)
			{
				$fields = explode(";", $line);

				if (count($fields) < 4)
				{
					continue;
				}

				$timestamp = strtotime($fields[0]);

				$stat = new ModordersreportsLibStatDate;
				$stat->date = date("Y-m-d", $timestamp);
				$stat->cancelled = $fields[1];
				$stat->errors = $fields[2];
				$stat->reports = $fields[3];

				$this->daysStats[] = $stat;

				usort($this->daysStats, array($this, 'sortByDate'));
			}
		}

		return $this->daysStats;
	}

	private static function isToday($timestamp)
	{
		return date("Y-m-d") == date("Y-m-d", $timestamp);
	}

	private static function isThisMonth($timestamp)
	{
		return date("Y-m") == date("Y-m", $timestamp);
	}

	private static function isThisYear($timestamp)
	{
		return date("Y") == date("Y", $timestamp);
	}

	private function sortByDate($a, $b)
	{
		return strtotime($a->date) < strtotime($b->date) ? -1 : 1;
	}
}