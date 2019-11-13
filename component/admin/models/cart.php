<?php
/**
 * @package     Redform.Backend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Cart Model
 *
 * @package     Redform.Backend
 * @subpackage  Models
 * @since       __deploy_version__
 */
class RedformModelCart extends RModelAdmin
{
	/**
	 * Get associated billing
	 *
	 * @return RdfEntityBilling
	 */
	public function getBilling()
	{
		$billing = RdfEntityBilling::getInstance();
		$billing->loadByCartId($this->getItem()->id);

		return $billing;
	}
}
