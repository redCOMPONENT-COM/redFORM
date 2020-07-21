<?php
/**
 * @package     Redform.Backend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * Payment Controller
 *
 * @package     Redform.Backend
 * @subpackage  Controllers
 * @since       1.0
 */
class RedformControllerPayment extends RdfControllerForm
{
	/**
	 * Override method to add a new record.
	 * It should not be possible to add a new record directly, this must be done from the cart screen.
	 * So first, we create a cart for the payment request, then redirect to it
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 */
	public function add()
	{
		$app     = Factory::getApplication();
		$context = "$this->option.edit.$this->context";

		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		// Create cart
		$paymentRequestId = $app->input->getInt('pr');
		$paymentRequest = RdfEntityPaymentrequest::load($paymentRequestId);

		if ($paymentRequest->paid)
		{
			// Set the internal error and also the redirect error.
			$this->setError(Text::_('COM_REDFORM_PAYMENT_ALREADY_PAID'));
			$this->setMessage($this->getError(), 'error');

			// Redirect to the list screen
			$this->setRedirect(
				$this->getRedirectToListRoute($this->getRedirectToListAppend())
			);

			return false;
		}

		$cart = $this->getModel()->createCart($paymentRequestId);

		// Clear the record edit information from the session.
		$app->setUserState($context . '.data', null);

		// Redirect back to the cart screen.
		$this->setRedirect(
			'index.php?option=com_redform&view=cart&id=' . $cart->id
		);

		return true;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$prid = $this->input->get('pr');

		if ($prid)
		{
			$append .= '&pr=' . $prid;
		}

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();

		$prid = $this->input->get('pr');

		if ($prid)
		{
			$append .= '&pr=' . $prid;
		}

		return $append;
	}
}
