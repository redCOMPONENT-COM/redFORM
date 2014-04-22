<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Payments Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.5
 */
class RedformControllerPayments extends RControllerAdmin
{
	public function back()
	{
		// Redirect to the list screen
		$this->setRedirect(
			$this->getRedirectToListRoute()
		);
	}
}
