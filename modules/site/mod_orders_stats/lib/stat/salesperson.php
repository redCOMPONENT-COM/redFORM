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
 * Sales person stat
 *
 * @since  1.0
 */
class ModordersstatsLibStatSalesperson
{
	/**
	 * @var string sales person
	 */
	public $name;

	/**
	 * @var string company
	 */
	public $company;

	/**
	 * @var int electricity sales
	 */
	public $elec = 0;

	/**
	 * @var int gas sales
	 */
	public $gas = 0;

	public function __construct($name, $company)
	{
		$this->name = $name;
		$this->company = $company;
	}

	public function __get($name)
	{
		if (isset($this->$name))
		{
			return $this->$name;
		}
		elseif ($name == 'total')
		{
			return $this->gas + $this->elec;
		}

		throw new InvalidArgumentException('Bad property');
	}

	public function addOrder(ModordersstatsLibOrder $order)
	{
		if ($order->hasGas)
		{
			$this->gas++;
		}

		if ($order->hasElec)
		{
			$this->elec++;
		}

		return true;
	}
}