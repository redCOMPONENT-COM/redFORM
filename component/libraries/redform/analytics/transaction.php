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
 * google analytics transaction type
 *
 * @package     Redform.Libraries
 * @subpackage  Analytics
 * @since       2.5
 */
class RdfAnalyticsTransaction
{
	/**
	 * Type of transaction
	 * @var string
	 */
	private $type = 'transaction';

	/**
	 * eCommerce transaction id
	 * @var string
	 */
	private $transactionId;

	/**
	 * eCommerce affiliation
	 * @var string
	 */
	private $affiliation;

	/**
	 * eCommerce revenue
	 * @var float
	 */
	private $revenue;

	/**
	 * Currency
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
	 * @param   string  $affiliation  affiliation
	 *
	 * @return $this
	 */
	public function setAffiliation($affiliation)
	{
		$this->affiliation = $affiliation;

		return $this;
	}

	/**
	 * Setter
	 *
	 * @param   float  $revenue  revenue
	 *
	 * @return $this
	 */
	public function setRevenue($revenue)
	{
		$this->revenue = $revenue;

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
			'ti' => $this->transactionId
		);

		if ($this->affiliation)
		{
			$data['ta'] = $this->affiliation;
		}

		if ($this->revenue)
		{
			$data['tr'] = $this->revenue;
		}

		if ($this->currency)
		{
			$data['cu'] = $this->currency;
		}

		return $client->hit($data);
	}
}
