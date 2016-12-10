<?php
/**
 * @package     Redform.Tests
 * @subpackage  Library
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// Register library prefix
JLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

require_once JPATH_LIBRARIES . '/redcore/bootstrap.php';
RdfBootstrap::bootstrap();

/**
 * Test class for tagsreplace.
 *
 * @package     Redform.Tests
 * @subpackage  Library
 * @since       2.5
 */
class carttagsreplaceTest extends TestCase
{
	/**
	 * Data provider
	 *
	 * @return array
	 */
	public function getTestReplaceData()
	{
		// Create a stub for the cart class.
		$mockCart = $this->getMock('RdfEntityCart', array('getPayment', 'getBilling'));

		$mockCart->price = 200.20;
		$mockCart->vat = 18.18;
		$mockCart->currency = 'EUR';
		$mockCart->paid = 1;
		$mockCart->note = "a test cart";
		$mockCart->invoice_id = "INVOICETEST-1234";

		// Billing entity
		$billing = RdfEntityBilling::getInstance(123);
		$billing->cart_id = 12;
		$billing->fullname = "John Doe";
		$billing->company = "Doe Inc.";
		$billing->iscompany = 1;
		$billing->vatnumber = "DK123456";

		// Configure the stub.
		$mockCart->method('getBilling')
			->will($this->returnValue($billing));

		$payment = RdfEntityPayment::getInstance(456);
		$payment->date = JFactory::getDate('2016-05-09 15:12:30')->toSql();
		$payment->gateway = 'testgateway';
		$payment->status = 'paid';
		$payment->paid = 1;

		$mockCart->method('getPayment')
			->will($this->returnValue($payment));

		$dateFormat = "d/m H:i";
		$paymentDateExpected = JFactory::getDate($payment->date)->format($dateFormat);

		return array(
			'nothing' => array($mockCart, 'a first test', 'a first test'),
			'[price]' => array($mockCart, 'testing [price]', 'testing ' . RHelperCurrency::getFormattedPrice($mockCart->price, $mockCart->currency)),
			'[vat]' => array($mockCart, 'testing [vat]', 'testing ' . RHelperCurrency::getFormattedPrice($mockCart->vat, $mockCart->currency)),
			'[currency]' => array($mockCart, 'testing [currency]', 'testing ' . $mockCart->currency),
			'[paid]' => array($mockCart, 'testing [paid]', 'testing ' . $mockCart->paid),
			'[note]' => array($mockCart, 'testing [note]', 'testing ' . $mockCart->note),
			'[invoice_id]' => array($mockCart, 'testing [invoice_id]', 'testing ' . $mockCart->invoice_id),
			'[billing_fullname]' => array($mockCart, 'testing [billing_fullname]', 'testing ' . $billing->fullname),
			'[payment_date]' => array($mockCart, 'testing [payment_date format="' . $dateFormat .'"]', 'testing ' . $paymentDateExpected),
		);
	}

	/**
	 * test cart tags replacer
	 *
	 * @param   RdfEntityCart  $mockCart  cart
	 * @param   string         $text      text
	 * @param   string         $expected  expected text
	 *
	 * @return void
	 *
	 * @dataProvider getTestReplaceData
	 */
	public function testCartReplace($mockCart, $text, $expected)
	{
		$helper = new RdfHelperTagsCart($mockCart);
		$resp = $helper->replace($text);

		$this->assertEquals($resp, $expected);
	}
}
