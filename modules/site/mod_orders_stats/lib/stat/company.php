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
class ModordersstatsLibStatCompany
{
	/**
	 * @var string sales person
	 */
	private $name;

	/**
	 * @var int electricity sales
	 */
	private $elec = 0;

	/**
	 * @var int gas sales
	 */
	private $gas = 0;

	public function __construct($name)
	{
		$this->name = $name;
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