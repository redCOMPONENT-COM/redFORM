<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Cart Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       3.4.0
 */
class RedformControllerCart extends RdfControllerForm
{
	/**
	 * Method to close cart view.
	 *
	 * @return  boolean  True if success
	 */
	public function close()
	{
		// Redirect to the list screen
		$this->setRedirect(
			$this->getRedirectToListRoute($this->getRedirectToListAppend())
		);

		return true;
	}
}
