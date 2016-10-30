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
 * Payment entity.
 *
 * @since  3.0
 */
class RdfEntityPayment extends RdfEntityBase
{
	/**
	 * Get cart
	 *
	 * @return RdfEntityCart
	 */
	public function getCart()
	{
		if (!$item = $this->getItem())
		{
			return false;
		}

		$cart = RdfEntityCart::load($item->cart_id);

		return $cart;
	}
}
