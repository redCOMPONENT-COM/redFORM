<?php
/**
 * @package     Redform.Backend
 * @subpackage  Tables
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Payment table.
 *
 * @package     Redform.Backend
 * @subpackage  Tables
 * @since       1.0
 */
class RedformTablePayment extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_payment';

	/** @var int Primary key */
	public $id = null;

	/** @var int Unique key that identifies single registrations */
	public $submit_key = null;

	/** @var string date */
	public $date = null;

	/** @var string gateway */
	public $gateway = null;

	/** @var string status */
	public $status = null;

	/** @var string information */
	public $data = null;

	/** @var int paid */
	public $paid = 0;

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		if (!$this->submit_key)
		{
			$this->setError(Jtext::_('COM_REDFORM_PAYMENT_TABLE_SUBMIT_KEY_IS_REQUIRED'));

			return false;
		}

		return true;
	}
}
