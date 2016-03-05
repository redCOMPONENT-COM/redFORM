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
 * Section table.
 *
 * @package     Redform.Backend
 * @subpackage  Tables
 * @since       3.3.10
 */
class RedformTableInvoice extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_invoice';

	public $id;

	public $cart_id;

	public $date;

	public $reference;

	public $name;

	public $note;

	public $booked;

	public $turned;

	public $params;
}
