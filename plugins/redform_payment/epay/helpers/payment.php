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

require_once (JPATH_SITE . '/components/com_redform/classes/paymenthelper.class.php');

/**
 * @package  RED.redform
 * @since    2.5
 */
class PaymentEpay extends  RDFPaymenthelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'epay';

	/**
	 * Display or redirect to the payment page for the gateway
	 *
	 * @param   object  $request     payment request object
	 * @param   string  $return_url  return url for redirection
	 * @param   string  $cancel_url  cancel url for redirection
	 *
	 * @return true on success
	 */
	public function process($request, $return_url = null, $cancel_url = null)
	{
		$document = JFactory::getDocument();
		$document->addScript("https://ssl.ditonlinebetalingssystem.dk/integration/ewindow/paymentwindow.js");

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;
		require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'helpers'.DS.'currency.php');
		$currency = RedformHelperLogCurrency::getIsoNumber($details->currency);
		?>
		<h3><?php echo JText::_('PLG_REDFORM_PAYMENT_EPAY_FORM_TITLE'); ?></h3>
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
			echo '<input type="hidden" name="callbackurl" value="' . $this->getUrl('notify', $submit_key) . '">';
		}
		$premd5 = $currency . round($details->price*100, 2 ) . $request->uniqueid  . $this->params->get('EPAY_MD5_KEY');
		?>
		<input type="hidden" name="accepturl" value="<?php echo $this->getUrl('notify', $submit_key); ?>">
		<input type="hidden" name="declineurl" value="<?php echo $this->getUrl('decline', $submit_key); ?>">
		<input type="hidden" name="group" value="<?php echo $this->params->get('EPAY_GROUP'); ?>">
		<input type="hidden" name="instant" value="<?php echo $this->params->get('EPAY_INSTANT_CAPTURE'); ?>">
		<input type="hidden" name="language" value="<?php echo $this->params->get('EPAY_LANGUAGE') ?>">
		<input type="hidden" name="authsms" value="<?php echo $this->params->get('EPAY_AUTH_SMS') ?>">
		<input type="hidden" name="authmail" value="<?php echo $this->params->get('EPAY_AUTH_MAIL'); ?>">
		<input type="hidden" name="windowstate" value="<?php echo $this->params->get('EPAY_WINDOW_STATE') ?>">
		<input type="hidden" name="use3D" value="<?php echo $this->params->get('EPAY_3DSECURE') ?>">
		<input type="hidden" name="addfee" value="<?php echo $this->params->get('EPAY_ADDFEE') ?>">
		<input type="hidden" name="subscription" value="<?php echo $this->params->get('EPAY_SUBSCRIPTION') ?>">
		<input type="hidden" name="md5Key" value="<?php if ($this->params->get('MD5') == 2) echo md5($premd5); ?>">
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

			<input type="submit" value="<?php echo JTEXT::_('OPEN_EPAY_PAYMENT_WINDOW'); ?>">
		</form>
		<br>
		<?php

		return true;
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
    // it was successull, get the details
    $resp = array();
    $resp[] = 'tid:'.JRequest::getVar('tid');
    $resp[] = 'orderid:'.JRequest::getVar('orderid');
    $resp[] = 'amount:'.JRequest::getVar('amount');
    $resp[] = 'cur:'.JRequest::getVar('cur');
    $resp[] = 'date:'.JRequest::getVar('date');
    $resp[] = 'time:'.JRequest::getVar('time');
    $resp = implode("\n", $resp);

    $details = $this->_getSubmission($submit_key);
		require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'helpers'.DS.'currency.php');
		$currency = RedformHelperLogCurrency::getIsoNumber($details->currency);

    if (round($details->price*100, 2 ) != JRequest::getVar('amount')) {
    	RedformHelperLog::simpleLog('EPAY NOTIFICATION PRICE MISMATCH'. ' for ' . $submit_key);
    	$this->writeTransaction($submit_key, 'EPAY NOTIFICATION PRICE MISMATCH'."\n".$resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);
    	return false;
    }
    else {
    	$paid = 1;
    }

    if ($currency != JRequest::getVar('cur')) {
    	RedformHelperLog::simpleLog('EPAY NOTIFICATION CURRENCY MISMATCH'. ' for ' . $submit_key);
    	$this->writeTransaction($submit_key, 'EPAY NOTIFICATION CURRENCY MISMATCH'."\n".$resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);
    	return false;
    }

    if ($this->params->get('MD5', 0) > 0)
    {
    	$receivedkey = JRequest::getVar('eKey');
    	$calc = md5(JRequest::getVar('amount').JRequest::getVar('orderid').JRequest::getVar('tid').$this->params->get('EPAY_MD5_KEY'));
    	if (strcmp($receivedkey, $calc))
    	{
	    	RedformHelperLog::simpleLog('EPAY NOTIFICATION MD5 KEY MISMATCH'. ' for ' . $submit_key);
	    	$this->writeTransaction($submit_key, 'EPAY NOTIFICATION MD5 KEY MISMATCH'."\n".$resp, $this->params->get('EPAY_INVALID_STATUS', 'FAIL'), 0);
	    	return false;
    	}
    }

	  $this->writeTransaction($submit_key, $resp, 'SUCCESS', 1);

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


	/**
	 * returns state uri object (notify, cancel, etc...)
	 *
	 * @param   string  $state       the state for the url
	 * @param   string  $submit_key  submit key
	 *
	 * @return string
	 */
	protected function getUri($state, $submit_key)
	{
		$uri = parent::getUri($state, $submit_key);

		switch ($state)
		{
			case 'cancel':
				$uri->setVar('accept', '0');
				break;
			case 'notify':
				$uri->setVar('accept', '1');
				break;
			case 'decline':
				$uri->setVar('task', 'notify');
				$uri->setVar('accept', '0');
				break;
		}

		return $uri;
	}
}
