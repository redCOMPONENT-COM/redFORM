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
	function process($request, $return_url = null, $cancel_url = null)
	{
		$document = JFactory::getDocument();
		$document->addScript("http://www.epay.dk/js/standardwindow.js");		
		
		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;
		require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'helpers'.DS.'currency.php');
		$currency = RedformHelperLogCurrency::getIsoNumber($details->currency);		
		?>		
		<h3><?php echo JText::_('Epay Payment Gateway'); ?></h3>
		<form action="https://ssl.ditonlinebetalingssystem.dk/popup/default.asp" method="post" name="ePay" target="ePay_window" id="ePay">
		<p><?php echo $request->title; ?></p>
		<input type="hidden" name="merchantnumber" value="<?php echo $this->params->get('EPAY_MERCHANTNUMBER'); ?>">
		<input type="hidden" name="amount" value="<?php echo round($details->price*100, 2 ); ?>">
		<input type="hidden" name="currency" value="<?php echo $currency?>">
		<input type="hidden" name="orderid" value="<?php echo $request->uniqueid; ?>">
		<input type="hidden" name="user_attr_1" value="<?php echo $submit_key; ?>">
		<input type="hidden" name="ordretext" value="">
		<?php 
		if ($this->params->get('EPAY_CALLBACK') == "1")
		{
			echo '<input type="hidden" name="callbackurl" value="' . JURI::base() . 'index.php?option=com_redform&controller=payment&task=notify&gw=epay&accept=1&key='. $submit_key.'">';			
		}
		?>
		<input type="hidden" name="accepturl" value="<?php echo JURI::base() . 'index.php?option=com_redform&controller=payment&task=notify&gw=epay&accept=1&key='. $submit_key; ?>">
		<input type="hidden" name="declineurl" value="<?php echo JURI::base() . 'index.php?option=com_redform&controller=payment&task=notify&gw=epay&accept=0&key='. $submit_key; ?>">
		<input type="hidden" name="group" value="<?php echo $this->params->get('EPAY_GROUP'); ?>">
		<input type="hidden" name="instant" value="<?php echo $this->params->get('EPAY_INSTANT_CAPTURE'); ?>">
		<input type="hidden" name="language" value="<?php echo $this->params->get('EPAY_LANGUAGE') ?>">
		<input type="hidden" name="authsms" value="<?php echo $this->params->get('EPAY_AUTH_SMS') ?>">
		<input type="hidden" name="authmail" value="<?php echo $this->params->get('EPAY_AUTH_MAIL') . (strlen($this->params->get('EPAY_AUTH_MAIL') > 0 && $this->params->get('EPAY_AUTHEMAILCUSTOMER') == 1 ? ";" : "") . ($this->params->get('EPAY_AUTHEMAILCUSTOMER') == 1 ? $user->user_email : "")); ?>">
		<input type="hidden" name="windowstate" value="<?php echo $this->params->get('EPAY_WINDOW_STATE') ?>">
		<input type="hidden" name="use3D" value="<?php echo $this->params->get('EPAY_3DSECURE') ?>">
		<input type="hidden" name="addfee" value="<?php echo $this->params->get('EPAY_ADDFEE') ?>">
		<input type="hidden" name="subscription" value="<?php echo $this->params->get('EPAY_SUBSCRIPTION') ?>">
		<input type="hidden" name="md5Key" value="<?php if ($this->params->get('EPAY_MD5_TYPE') == 2) echo md5( $currency . round($details->price*100, 2 ) . $submit_key  . $this->params->get('EPAY_MD5_KEY')); ?>">
		<?php if ($this->params->get('EPAY_CARDTYPES_0') == "0"): ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_1') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="1"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_2') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="2"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_3') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="3"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_4') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="4"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_5') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="5"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_6') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="6"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_6') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="7"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_8') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="8"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_9') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="9"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_10') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="10"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_11') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="11"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_12') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="12"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_14') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="14"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_15') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="15"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_16') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="16"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_17') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="17"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_18') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="18"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_19') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="19"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_21') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="21"/>
			<?php endif; ?>
			<?php if ($this->params->get('EPAY_CARDTYPES_22') == "1"): ?>
			<input type="hidden" name="cardtype[]" value="22"/>
			<?php endif; ?>
		<?php endif; ?>
		</form>
		<script type="text/javascript">open_ePay_window();</script>
		<br>
		<table border="0" width="100%"><tr><td><input type="button" onClick="open_ePay_window()" value="<?php echo JTEXT::_('OPEN_EPAY_PAYMENT_WINDOW') ?>"></td><td width="100%" id="flashLoader"></td></tr></table>
		<?php 
	}
	
	/**
	 * handle the recpetion of notification
	 * @return bool paid status
	 */
  function notify()
  {
    $mainframe = &JFactory::getApplication();
    $db = & JFactory::getDBO();    
    $paid = 0;
    				
    $submit_key = JRequest::getvar('key');
    JRequest::setVar('submit_key', $submit_key);
    RedformHelperLog::simpleLog('EPAY NOTIFICATION RECEIVED'. ' for ' . $submit_key);
    
    if (JRequest::getVar('accept', 0) == 0)
    {
    	// payment was refused
    	RedformHelperLog::simpleLog('EPAY NOTIFICATION PAYMENT REFUSED'. ' for ' . $submit_key);
    	$this->writeTransaction($submit_key, JRequest::getVar('error').': '.JRequest::getVar('errortext'), $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);
	    return 0;
    }
    
    $details = $this->_getSubmission($submit_key);
		require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'helpers'.DS.'currency.php');
		$currency = RedformHelperLogCurrency::getIsoNumber($details->currency);		
    
    if (round($details->price*100, 2 ) != JRequest::getVar('amount')) {    	
    	RedformHelperLog::simpleLog('EPAY NOTIFICATION PRICE MISMATCH'. ' for ' . $submit_key);
    	$this->writeTransaction($submit_key, 'EPAY NOTIFICATION PRICE MISMATCH', $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);
    	return false;
    }
    else {
    	$paid = 1;
    }
    
    if ($currency != JRequest::getVar('cur')) {    	
    	RedformHelperLog::simpleLog('EPAY NOTIFICATION CURRENCY MISMATCH'. ' for ' . $submit_key);
    	$this->writeTransaction($submit_key, 'EPAY NOTIFICATION CURRENCY MISMATCH', $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);
    	return false;
    }
    
    if ($this->params->get('EPAY_MD5_TYPE', 0) > 0)
    {
    	$receivedkey = JRequest::getVar('key');
    	$calc = md5(JRequest::getVar('amount').JRequest::getVar('orderid').JRequest::getVar('tid').$this->params->get('EPAY_MD5_KEY'));
    	if (!strcmp($receivedkey, $calc)) 
    	{
	    	RedformHelperLog::simpleLog('EPAY NOTIFICATION MD5 KEY MISMATCH'. ' for ' . $submit_key);
	    	$this->writeTransaction($submit_key, 'EPAY NOTIFICATION MD5 KEY MISMATCH', $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);
	    	return false;    		
    	}
    }
    
	  $this->writeTransaction($submit_key, '', 'SUCCESS', 1);
    
    return $paid;
  }
  
  function _getSubmission($submit_key)
  {
		// get price and currency
		$db  = &JFactory::getDBO();
		
		$query = ' SELECT f.currency, SUM(s.price) AS price, s.id AS sid '
		       . ' FROM #__rwf_submitters AS s '
		       . ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
		       . ' WHERE s.submit_key = '. $db->Quote($submit_key)
		       . ' GROUP BY s.submit_key'
		            ;
		$db->setQuery($query);
		$res = $db->loadObject();
		return $res;
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
				    . ', '. $db->Quote('epay')
				    . ', '. $db->Quote($paid)
				    . ') ';
    $db->setQuery($query);
    $db->query();
  }
  	
}