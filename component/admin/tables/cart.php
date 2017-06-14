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

	/**
	 * Called before store().
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeStore($updateNulls = false)
	{
		if (!$this->invoice_id)
		{
			$this->setInvoiceId();
		}

		return parent::beforeStore($updateNulls);
	}

	/**
	 * get default invoice id, based on prefix and current max value of it
	 *
	 * @return void
	 */
	protected function getNewInvoiceId()
	{
		$config = JComponentHelper::getParams('com_redform');
		$prefix = $config->get('invoice_prefix', 'INVOICE-');
		$padding = $config->get('invoice_padding', 4);

		// Get existing prefixes
		$query = $this->_db->getQuery(true)
			->select('invoice_id')
			->from('#__rwf_cart')
			->where('invoice_id LIKE ' . $this->_db->q($prefix . '%'));

		$this->_db->setQuery($query);
		$res = $this->_db->loadColumn();

		$max = !$res ? 0 : array_reduce(
			$res,
			function($carry, $invoiceId) use ($prefix)
			{
				$number = (int) substr($invoiceId, strlen($prefix));

				return max($number, $carry);
			},
			0
		);

		return $prefix . sprintf('%0' . $padding . 'd', $max + 1);
	}

	/**
	 * Set invoice id
	 *
	 * @return void
	 */
	protected function setInvoiceId()
	{
		$invoice_id = null;

		JPluginHelper::importPlugin('redform');
		RFactory::getDispatcher()->trigger('onGetNewInvoiceId', array($this, &$invoice_id));

		if (!$invoice_id)
		{
			$invoice_id = $this->getNewInvoiceId();
		}

		$this->invoice_id = $invoice_id;
	}
}
