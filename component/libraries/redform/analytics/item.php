<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Analytics
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * google analytics item type
 *
 * @package     Redform.Libraries
 * @subpackage  Analytics
 * @since       2.5
 */
class RdfAnalyticsItem
{
	/**
	 * Type of transaction
	 * @var string
	 */
	private $type = 'item';

	/**
	 * eCommerce transaction id
	 * @var string
	 */
	private $transactionId;

	/**
	 * eCommerce item name
	 * @var string
	 */
	private $name;

	/**
	 * eCommerce revenue
	 * @var float
	 */
	private $price;

	/**
	 * eCommerce quantity
	 * @var int
	 */
	private $quantity = 1;

	/**
	 * eCommerce sku
	 * @var string
	 */
	private $sku;

	/**
	 * eCommerce Item variation / category.
	 * @var string
	 */
	private $category;

	/**
	 * Currency code
	 * @var string
	 */
	private $currency;

	/**
	 * Setter
	 *
	 * @param   string  $tid  transaction id
	 *
	 * @return $this
	 */
	public function setTransactionId($tid)
	{
		$this->transactionId = $tid;

		return $this;
	}

	/**
	 * Setter
	 *
	 * @param   string  $name  $name
	 *
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Setter
	 *
	 * @param   float  $price  price
	 *
	 * @return $this
	 */
	public function setPrice($price)
	{
		$this->price = $price;

		return $this;
	}

	/**
	 * Setter
	 *
	 * @param   float  $quantity  quantity
	 *
	 * @return $this
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;

		return $this;
	}

	/**
	 * Setter
	 *
	 * @param   float  $sku  $sku
	 *
	 * @return $this
	 */
	public function setSku($sku)
	{
		$this->sku = $sku;

		return $this;
	}

	/**
	 * Setter
	 *
	 * @param   float  $category  category
	 *
	 * @return $this
	 */
	public function setCategory($category)
	{
		$this->category = $category;

		return $this;
	}

	/**
	 * Setter
	 *
	 * @param   string  $currency  currency iso code 3
	 *
	 * @return $this
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * Send
	 *
	 * @param   RdfAnalyticsMeasurementprotocolClientinterface  $client  client to send the hit
	 *
	 * @return mixed
	 */
	public function hit(RdfAnalyticsMeasurementprotocolClientinterface $client)
	{
		$data = array(
			't' => $this->type,
			'ti' => $this->transactionId,
			'in' => $this->name,
			'ip' => $this->price,
			'iq' => $this->quantity,
		);

		if ($this->sku)
		{
			$data['ic'] = $this->sku;
		}

		if ($this->category)
		{
			$data['iv'] = $this->category;
		}

		if ($this->currency)
		{
			$data['cu'] = $this->currency;
		}

		return $client->hit($data);
	}
}
