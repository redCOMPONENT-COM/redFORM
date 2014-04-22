<?php
/**
 * @copyright Copyright (C) 2008-2013 redCOMPONENT.com. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * redFORM can be downloaded from www.redcomponent.com
 * redFORM is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redFORM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redFORM; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

/**
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package  RED.redform
 * @since    2.5
 */
class PaymentPaypal extends  RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'paypal';

	protected $params = null;

	/**
	 * contructor
	 * @param object plgin params
	 */
	function PaymentPaypal($params)
	{
		$this->params = $params;
	}

	/**
	 * sends the payment request associated to sumbit_key to the payment service
	 * @param object $request
	 */
	function process($request, $return_url = null, $cancel_url = null)
	{
		$app = JFactory::getApplication();
		$submit_key = $request->key;

		if (empty($return_url)) {
			$return_url = $this->getUrl('processing', $submit_key);
		}
		if (empty($cancel_url)) {
			$cancel_url = $this->getUrl('cancel', $submit_key);
		}

		// get price and currency
		$res = $this->_getSubmission($submit_key);

		if ($this->params->get('paypal_sandbox', 1) == 1) {
			$paypalurl = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		}
		else {
		  $paypalurl = "https://www.paypal.com/cgi-bin/webscr";
		}

		$post_variables = Array(

		"cmd" => "_xclick",

		"business" => $this->params->get('paypal_account'),

		"item_name" =>  $request->title,

		"no_shipping" =>  '1',

		"invoice" =>  $request->uniqueid,

		"amount" =>  $res->price,

		"return" => $return_url,

		"notify_url" => $this->getUrl('notify', $submit_key),

		"cancel_return" => $cancel_url,

		"undefined_quantity" => "0",

		"currency_code" => $res->currency,

		"no_note" => "1"

		);


		$query_string = "?";

		foreach( $post_variables as $name => $value ) {

		$query_string .= $name. "=" . urlencode($value) ."&";

		}

		$app->redirect( $paypalurl . $query_string );
	}

	/**
	 * handle the recpetion of notification
	 * @return bool paid status
	 */
  function notify()
  {
    $mainframe = JFactory::getApplication();
    $db = JFactory::getDBO();


    $post = JRequest::get( 'post' );
    $submit_key = JRequest::getvar('key');
    $paid = 0;

    //RdfHelperLog::simpleLog('PAYPAL NOTIFICATION RECEIVED'. ' for ' . $submit_key);
    // read the post from PayPal system and add 'cmd'
    $req = 'cmd=_notify-validate';

    $data = array();
    foreach ($post as $key => $value) {
      $value = urlencode(stripslashes($value));
      $req .= "&$key=$value";
      $data[] = "$key=$value";
    }

    // assign posted variables to local variables
    $item_name = $post['item_name'];
    $item_number = $post['item_number'];
    $payment_status = $post['payment_status'];
    $payment_amount = $post['mc_gross'];
    $payment_currency = $post['mc_currency'];
    $txn_id = $post['txn_id'];
    $receiver_email = $post['receiver_email'];
    $payer_email = $post['payer_email'];

    // post back to PayPal system to validate
		if ($this->params->get('paypal_sandbox', 1) == 1) {
			$paypalurl = "https://www.sandbox.paypal.com";
		}
		else {
		  $paypalurl = "https://www.paypal.com";
		}
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paypalurl.'/cgi-bin/webscr');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.paypal.com'));
    // In wamp like environment where the root authority certificate doesn't comes in the bundle, you need
    // to download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
    // of the certificate as shown below.
    // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
    $res = curl_exec($ch);
    curl_close($ch);

    //RdfHelperLog::simpleLog('PAYPAL curl result: '.$res);

    if (strcmp ($res, "VERIFIED") == 0)
    {
    	// check the payment_status is Completed
    	// check that txn_id has not been previously processed
    	// check that receiver_email is your Primary PayPal email
    	// check that payment_amount/payment_currency are correct

	    $res = $this->_getSubmission($submit_key);

    	if ($payment_amount != $res->price) {
    		RdfHelperLog::simpleLog('PAYPAL NOTIFICATION WRONG AMOUNT('. $res->price.') - ' . $submit_key);
    	}
    	else if ($payment_currency != $res->currency) {
    		RdfHelperLog::simpleLog('PAYPAL NOTIFICATION WRONG CURRENCY ('. $res->currency.') - ' . $submit_key);
    	}
    	else if (strcasecmp($payment_status, 'completed') == 0) {
    		$paid = 1;
    	}
    }
    else if (strcmp ($res, "INVALID") == 0)
    {
    	// log for manual investigation
			RdfHelperLog::simpleLog('PAYPAL NOTIFICATION INVALID IPN'. ' - ' . $submit_key);
    }
    else {
    	RdfHelperLog::simpleLog('PAYPAL NOTIFICATION HTTP ERROR'. ' for ' . $submit_key);
    }

    // log ipn
    $query =  ' INSERT INTO #__rwf_payment (`date`, `data`, `submit_key`, `status`, `gateway`, `paid`) '
				    . ' VALUES (NOW(), ' . $db->Quote(implode("\n", $data))
				    . ', '. $db->Quote($submit_key)
				    . ', '. $db->Quote($payment_status)
				    . ', '. $db->Quote('Paypal')
				    . ', '. $db->Quote($paid)
				    . ') ';
    $db->setQuery($query);
    $db->query();

    // for routing
    JRequest::setVar('submit_key', $submit_key);
    return $paid;
  }
}
