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
 * Value table.
 *
 * @package     Redshopb.Backend
 * @subpackage  Tables
 * @since       2.5
 */
class RedformTableValue extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_values';

	/**
	 * @var int Primary key
	 */
	public $id = null;

	/**
	 * @var string
	 */
	public $value = null;

	/**
	 * @var string
	 */
	public $label = null;

	/**
	 * @var int published state
	 */
	public $published = null;

	/**
	 * @var int id of user having checked out the item
	 */
	public $checked_out = null;

	/**
	 * @var string
	 */
	public $checked_out_time = null;

	/**
	 * @var int
	 */
	public $field_id = null;

	/**
	 * @var int
	 */
	public $ordering = null;

	/**
	 * @var float
	 */
	public $price = null;

	/**
	 * @var string
	 */
	public $sku = null;

	/**
	 * Field name to publish/unpublish/trash table registers. Ex: state
	 *
	 * @var  string
	 */
	protected $_tableFieldState = 'published';
}
