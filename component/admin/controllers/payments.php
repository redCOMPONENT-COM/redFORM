<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (c) 2008 - 2021 redweb.dk. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Router\Route;

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
	/**
	 * Back function
	 *
	 * @return void
	 */
	public function back()
	{
		// Redirect to the list screen
		$this->setRedirect(
			$this->getRedirectToListRoute()
		);
	}

	/**
	 * Get the JRoute object for a redirect to list.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  JRoute  The JRoute object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->get('return', '', 'Base64');

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);

			return Route::_($returnUrl . $append, false);
		}
		else
		{
			return Route::_('index.php?option=' . $this->option . '&view=submitters' . $append, false);
		}
	}
}
