<?php

class PaymentPaypal {
	
	var $params = null;
	
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
	 * @param string $submit_key
	 */
	function process($submit_key)
	{
		$app = &JFactory::getApplication();
		// get price and currency
		$db  = &JFactory::getDBO();
		
		$query = ' SELECT f.currency, SUM(s.price) AS price '
		       . ' FROM #__rwf_submitters AS s '
		       . ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
		       . ' WHERE s.submit_key = '. $db->Quote($submit_key)
		       . ' GROUP BY s.submit_key'
		            ;
		$db->setQuery($query);
		$res = $db->loadObject();
				
		if ($this->params->get('paypal_sandbox', 1) == 1) {
			$paypalurl = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		}
		else {
		  $paypalurl = "https://www.paypal.com/cgi-bin/webscr";	
		}
			
		$post_variables = Array(
	
		"cmd" => "_xclick",
	
		"business" => $this->params->get('paypal_account'),
	
		"item_name" =>  'Form submit',
		
		"no_shipping" =>  '1',
		
		"order_id" => $submit_key,
	
		"invoice" =>  $submit_key,
	
		"amount" =>  $res->price,
	 
		"return" => JRoute::_(JURI::base() . 'index.php?option=com_redform&controller=payment&task=processing&key='. $submit_key),
	
		"notify_url" => JRoute::_(JURI::base() . 'index.php?option=com_redform&controller=payment&task=notify&gw=paypal&key='. $submit_key),
	
		"cancel_return" => JRoute::_(JURI::base() . 'index.php?option=com_redform&controller=payment&task=cancel&key='. $submit_key),
	
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
    global $mainframe;
    $db = & JFactory::getDBO();
    
    				
    $post = JRequest::get( 'post' );
    $submit_key = JREQuest::getvar('key');
    $paid = 0;

    // RedformHelperLog::simpleLog('PAYPAL NOTIFICATION RECEIVED'. ' for ' . $submit_key);
    // read the post from PayPal system and add 'cmd'
    $req = 'cmd=_notify-validate';

    $data = array();
    foreach ($post as $key => $value) {
      $value = urlencode(stripslashes($value));
      $req .= "&$key=$value";
      $data[] = "$key=$value";
    }


    // post back to PayPal system to validate
    $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		if ($this->params->get('paypal_sandbox', 1) == 1) {
			$paypalurl = "www.sandbox.paypal.com";
		}
		else {
		  $paypalurl = "www.paypal.com";	
		}
		$fp = fsockopen ($paypalurl, 80, $errno, $errstr, 30); 

    // assign posted variables to local variables
    $item_name = $post['item_name'];
    $item_number = $post['item_number'];
    $payment_status = $post['payment_status'];
    $payment_amount = $post['mc_gross'];
    $payment_currency = $post['mc_currency'];
    $txn_id = $post['txn_id'];
    $receiver_email = $post['receiver_email'];
    $payer_email = $post['payer_email'];

    $check = '';
          
    if (!$fp) {
      // HTTP ERROR
    } else {
      fputs ($fp, $header . $req);
      while (!feof($fp)) {
        $res = fgets ($fp, 1024);
        //echo "$res";
        $status = 'ok';
        if (strcmp ($res, "VERIFIED") == 0) {
          // check the payment_status is Completed
          // check that txn_id has not been previously processed
          // check that receiver_email is your Primary PayPal email
          // check that payment_amount/payment_currency are correct
          
					$query = ' SELECT f.currency, SUM(s.price) AS price '
					       . ' FROM #__rwf_submitters AS s '
					       . ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
					       . ' WHERE s.submit_key = '. $db->Quote($submit_key)
					       . ' GROUP BY s.submit_key'
					            ;
					$db->setQuery($query);
					$res = $db->loadObject();
		       	
    			if ($payment_amount != $res->price) {
    				RedformHelperLog::simpleLog('PAYPAL NOTIFICATION WRONG CURRENCY'. ' - ' . $submit_key);
    			}      
    			if ($payment_currency != $res->currency) {
    				RedformHelperLog::simpleLog('PAYPAL NOTIFICATION WRONG CURRENCY'. ' - ' . $submit_key);
    			}
    			
    			if (strcasecmp($payment_status, 'completed') == 0) {
    				$paid = 1;
    			}
        }
        else if (strcmp ($res, "INVALID") == 0) {
          // log for manual investigation
    			RedformHelperLog::simpleLog('PAYPAL NOTIFICATION INVALID IPN'. ' - ' . $submit_key);
        }
      }
      fclose ($fp);
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
//    RedformHelperLog::simpleLog($db->getQuery());
    $db->query();
    return $paid;
  }
}