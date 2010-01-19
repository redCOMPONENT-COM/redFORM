<?php

class PaymentEpay {
	
	var $params = null;
	
	/**
	 * contructor
	 * @param object plgin params
	 */
	function PaymentEpay($params)
	{
		$this->params = $params;
	}
	
	/**
	 * sends the payment request associated to sumbit_key to the payment service
	 * @param string $submit_key
	 */
	function process($submit_key, $return_url = null, $cancel_url = null)
	{
		
	}
	
	/**
	 * handle the recpetion of notification
	 * @return bool paid status
	 */
  function notify()
  {
    
    return 0;
  }
}