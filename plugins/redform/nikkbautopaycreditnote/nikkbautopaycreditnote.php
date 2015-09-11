<?php
/**
 * @package     Redform.Plugin
 * @subpackage  Redform.economic
 *
 * @copyright   Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * This plugin makes all billing form fields required
 *
 * @since  3.0
 */
class plgRedformNikkbautopaycreditnote extends JPlugin
{
	/**
	 * @var RdfEntityPaymentrequest
	 */
	private $paymentRequest;

	/**
	 * @var RdfEntityPaymentrequest
	 */
	private $cart;

	/**
	 * process form
	 *
	 * @param   RdfEntityPaymentrequest  $paymentRequest  payment request
	 *
	 * @return void
	 */
	public function onRedformAfterCreatePaymentRequest(RdfEntityPaymentrequest $paymentRequest)
	{
		// Filter out debit payment request
		if ($paymentRequest->price > 0)
		{
			return;
		}

		// We want to set the 'credit' payment request as paid
		$submitter = $paymentRequest->getSubmitter();
		$submitKey = $submitter->submit_key;

		$cart = new RdfCorePaymentCart;
		$cart->getNewCart($submitKey);

		$this->attachPreviousBilling($cart, $paymentRequest);

		$cart->writeTransaction('autopay', 'auto', 'paid', 1);
	}

	/**
	 * Attach a previous billing to the cart
	 *
	 * @param   RdfCorePaymentCart       $cart            cart
	 * @param   RdfEntityPaymentrequest  $paymentRequest  payment request
	 *
	 * @return void
	 */
	private function attachPreviousBilling($cart, $paymentRequest)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('b.*')
			->from('#__rwf_billinginfo AS b')
			->innerJoin('#__rwf_cart AS c ON c.id = b.cart_id')
			->innerJoin('#__rwf_cart_item AS ci ON ci.cart_id = c.id')
			->innerJoin('#__rwf_payment_request AS pr ON pr.id = ci.payment_request_id')
			->where('pr.submission_id = ' . $paymentRequest->submission_id);

		$db->setQuery($query, 0, 1);
		$previousBilling = $db->loadAssoc();

		if ($previousBilling)
		{
			$billingRow = RTable::getAdminInstance('billing', array(), 'com_redform');
			$billingRow->bind($previousBilling);
			$billingRow->id = null;
			$billingRow->cart_id = $cart->id;
		}

		$billingRow->store();
	}
}
