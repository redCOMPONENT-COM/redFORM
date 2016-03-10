<?php
/**
 * @package     Redform.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Billing entity.
 *
 * @since  3.0
 */
class RdfEntityBilling extends RdfEntityBase
{
	/**
	 * Return instance
	 *
	 * @param   int  $cartId  cart id
	 *
	 * @return RdfEntityBilling
	 */
	public function loadByCartId($cartId)
	{
		$table = $this->getTable();
		$table->load(array('cart_id' => $cartId));

		if ($table->id)
		{
			$this->loadFromTable($table);
		}

		return $this;
	}
}
