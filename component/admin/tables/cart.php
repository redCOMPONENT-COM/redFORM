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
 * Cart table.
 *
 * @package     Redform.Backend
 * @subpackage  Tables
 * @since       2.5
 */
class RedformTableCart extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_cart';

	/**
	 * Called after delete().
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterDelete($pk = null)
	{
		$pk = $pk ?: $this->id;

		$db = $this->_db;
		$query = $db->getQuery(true)
			->delete('#__rwf_cart_item')
			->where('cart_id = ' . $pk);

		$db->setQuery($query);
		$db->execute();

		return parent::afterDelete($pk);
	}
}
