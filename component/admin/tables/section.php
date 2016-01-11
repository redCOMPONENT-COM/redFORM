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
 * @since       3.3.8
 */
class RedformTableSection extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_section';

	public $id;

	public $name;

	public $description;

	public $class;

	public $checked_out;

	public $checked_out_time;
}
