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
 * Submission Payment request table.
 *
 * @package     Redform.Backend
 * @subpackage  Tables
 * @since       2.5
 */
class RedformTablePaymentrequest extends RTable
{
	/**
	 * The table name without the prefix.
	 *
	 * @var  string
	 */
	protected $_tableName = 'rwf_payment_request';

	/**
	 * Called after delete().
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeDelete($pk = null)
	{
		$pk = $pk ?: $this->id;

		if ($cartId = $this->getCartId($pk))
		{
			$table = RTable::getInstance('Cart', 'RedformTable');
			$table->delete($pk);
		}

		return parent::beforeDelete($pk);
	}

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
			->delete('#__rwf_payment_request_item')
			->where('payment_request_id = ' . $pk);

		$db->setQuery($query);
		$db->execute();

		return parent::afterDelete($pk);
	}

	/**
	 * Get associated cart id
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return mixed
	 */
	private function getCartId($pk = null)
	{
		$pk = $pk ?: $this->id;

		$db = $this->_db;
		$query = $db->getQuery(true)
			->select('c.id')
			->from('#__rwf_cart AS c')
			->join('INNER', '#__rwf_cart_item AS ci')
			->where('ci.payment_request_id = ' . $pk);

		$db->setQuery($query);

		return $db->loadResult();
	}
}
