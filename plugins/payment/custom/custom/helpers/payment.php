<?php

class PaymentCustom {
	
	var $params = null;
	
	/**
	 * contructor
	 * @param object plgin params
	 */
	function PaymentCustom($params)
	{
		$this->params = $params;
	}
	
	/**
	 * sends the payment request associated to sumbit_key to the payment service
	 * @param string $submit_key
	 */
	function process($request, $return_url = null, $cancel_url = null)
	{
		$text = $this->params->get('instructions');
		if ($return_url) {
			echo '<p>'.JHTML::link($return_url, JText::_('Return')).'</b>';
		}
		echo $text;
	}
	    
  function writeTransaction($submit_key, $data, $status, $paid)
  {
    $db = & JFactory::getDBO();    
  	
    // payment was refused
    $query =  ' INSERT INTO #__rwf_payment (`date`, `data`, `submit_key`, `status`, `gateway`, `paid`) '
				    . ' VALUES (NOW() '
				    . ', '. $db->Quote($data)
				    . ', '. $db->Quote($submit_key)
				    . ', '. $db->Quote($status)
				    . ', '. $db->Quote('custom')
				    . ', '. $db->Quote($paid)
				    . ') ';
    $db->setQuery($query);
    $db->query();
  }
}