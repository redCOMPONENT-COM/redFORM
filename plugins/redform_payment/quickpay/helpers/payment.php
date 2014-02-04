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
class PaymentQuickpay extends  RDFPaymenthelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'quickpay';

	protected $params = null;

	/**
	 * contructor
	 * @param object plgin params
	 */
	function PaymentQuickpay($params)
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

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;
		require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'helpers'.DS.'currency.php');
		$currency = $details->currency;

		$req_params = array(
		  'protocol' => 4,
		  'msgtype' => "authorize",
		  'merchant' => $this->params->get('quickpayid'),
		  'language' => "en",
		  'ordernumber' => $request->uniqueid,
		  'amount' => round($details->price*100, 2 ),
		  'currency' => $currency,
		  'continueurl' => $this->getUrl('processing', $submit_key),
		  'cancelurl' => $this->getUrl('paymentcancelled', $submit_key),
		  'callbackurl' => $this->getUrl('notify', $submit_key),
		  'autocapture' => 0,
		  'cardtypelock' => $this->_getAllowedCard(),
		  'description' => 0,
		  'testmode' => $this->params->get('testmode', 0),
		  'splitpayment' => 0,
		);
		$md5 = md5(implode("", $req_params).$this->params->get('md5secret'));

		if (!$req_params['merchant']) {
			echo JText::_('PLG_REDFORM_QUICKPAY_MISSING_QUICKPAYID');
			return false;
		}
		if (!$this->params->get('md5secret')) {
			echo JText::_('PLG_REDFORM_QUICKPAY_MISSING_MD5SECRET');
			return false;
		}
		?>
		<h3><?php echo JText::_('Quickpay Payment Gateway'); ?></h3>
		<form action="https://secure.quickpay.dk/form/" method="post">
		<p><?php echo $request->title; ?></p>
		<?php foreach ($req_params as $key => $val): ?>
		<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>" />
		<?php endforeach; ?>
		<input type="hidden" name="md5check" value="<?php echo $md5; ?>" />
		<input type="submit" value="Open Quickpay payment window" />
		</form>
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
    RedformHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_QUICKPAY_NOTIFICATION_RECEIVED', $submit_key));

    // it was successull, get the details
    $resp = array();
    $resp[] = 'tid:'.JRequest::getVar('transaction');
    $resp[] = 'orderid:'.JRequest::getVar('ordernumber');
    $resp[] = 'amount:'.JRequest::getVar('amount');
    $resp[] = 'cur:'.JRequest::getVar('currency');
    $resp[] = 'date:'.substr(JRequest::getVar('time'), 0, 6);
    $resp[] = 'time:'.substr(JRequest::getVar('time'), 6);
    $resp = implode("\n  ", $resp);

		if ($this->params->get('md5secret'))
		{
			$req_params = array(
			  JRequest::getVar('msgtype'),
			  JRequest::getVar('ordernumber'),
			  JRequest::getVar('amount'),
			  JRequest::getVar('currency'),
			  JRequest::getVar('time'),
			  JRequest::getVar('state'),
			  JRequest::getVar('qpstat'),
			  JRequest::getVar('qpstatmsg'),
			  JRequest::getVar('chstat'),
			  JRequest::getVar('chstatmsg'),
			  JRequest::getVar('merchant'),
			  JRequest::getVar('merchantemail'),
			  JRequest::getVar('transaction'),
			  JRequest::getVar('cardtype'),
			  JRequest::getVar('cardnumber'),
			  //     	  JRequest::getVar('cardexpire'),
			  JRequest::getVar('splitpayment'),
			  JRequest::getVar('fraudprobability'),
			  JRequest::getVar('fraudremarks'),
			  JRequest::getVar('fraudreport'),
			  JRequest::getVar('fee')
	    );
	    $receivedkey = JRequest::getVar('md5check');
	    $calc = md5(implode('', $req_params).$this->params->get('md5secret'));
	    if (strcmp($receivedkey, $calc))
	    {
	    	$error = JText::sprintf('PLG_REDFORM_QUICKPAY_MD5_KEY_MISMATCH', $submit_key);
		    RedformHelperLog::simpleLog($error);
		    $this->writeTransaction($submit_key, $error.$resp, 'FAIL', 0);
		    return false;
	    }
    }


    if (!JRequest::getVar('qpstat') === '000')
    {
    	// payment was refused
    	$error = JText::sprintf('PLG_REDFORM_QUICKPAY_PAYMENT_REFUSED', $submit_key);
    	RedformHelperLog::simpleLog($error);
    	$this->writeTransaction($submit_key, JRequest::getVar('qpstat').': '.JRequest::getVar('qpstatmsg'), 'FAIL', 0);
	    return 0;
    }

    if (JRequest::getVar('state') == 0)
    {
		// payment was refused
    	RedformHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_INITIAL', $submit_key));
    	$this->writeTransaction($submit_key, JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_INITIAL', $submit_key)."\n  ".$resp, 'FAIL', 0);
    	return 0;
    }
    else if (JRequest::getVar('state') == 5)
    {
		// payment was refused
    	RedformHelperLog::simpleLog(JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_CANCELLED', $submit_key));
    	$this->writeTransaction($submit_key, JText::sprintf('PLG_REDFORM_QUICKPAY_TRANSACTION_STATE_CANCELLED', $submit_key)."\n  ".$resp, 'FAIL', 0);
    	return 0;
    }

    $details = $this->_getSubmission($submit_key);

    $currency = $details->currency;
    if (strcasecmp($currency,JRequest::getVar('currency'))) {
    	$error = JText::sprintf('PLG_REDFORM_QUICKPAY_CURRENCY_MISMATCH', $submit_key);
    	RedformHelperLog::simpleLog($error);
    	$this->writeTransaction($submit_key, $error.$resp, 'FAIL', 0);
    	return false;
    }

    if (round($details->price*100, 2 ) != JRequest::getVar('amount')) {
    	$error = JText::sprintf('PLG_REDFORM_QUICKPAY_PRICE_MISMATCH', $submit_key);
    	RedformHelperLog::simpleLog($error);
    	$this->writeTransaction($submit_key, $error.$resp, 'FAIL', 0);
    	return false;
    }
    else {
    	$paid = 1;
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

  /**
   * write transaction to db
   *
   * @param string $submit_key
   * @param string $data
   * @param string $status
   * @param int $paid
   */
  function writeTransaction($submit_key, $data, $status, $paid)
  {
    $db = & JFactory::getDBO();

    // payment was refused
    $query =  ' INSERT INTO #__rwf_payment (`date`, `data`, `submit_key`, `status`, `gateway`, `paid`) '
				    . ' VALUES (NOW() '
				    . ', '. $db->Quote($data)
				    . ', '. $db->Quote($submit_key)
				    . ', '. $db->Quote($status)
				    . ', '. $db->Quote('quickpay')
				    . ', '. $db->Quote($paid)
				    . ') ';
    $db->setQuery($query);
    $db->query();
  }

	/**
	 * returns allowed card types
	 * @return string
	 */
	private function _getAllowedCard()
	{
		$allowed = array();
		$methods = array(
		  'american-express',
		  'american-express-dk',
		  'dankort',
		  'danske-dk',
		  'diners',
		  'diners-dk',
		  'edankort',
		  'fbg1886',
		  'jcb',
		  'mastercard',
		  'mastercard-dk',
		  'mastercard-debet-dk',
		  'nordea-dk',
		  'visa',
		  'visa-dk',
		  'visa-electron',
		  'visa-electron-dk',
		  'paypal',
		  '3d-jcb',
		  '3d-maestro',
		  '3d-maestro-dk',
		  '3d-mastercard',
		  '3d-mastercard-dk',
		  '3d-mastercard-debet-dk',
		  '3d-visa',
		  '3d-visa-dk',
		  '3d-visa-electron',
		  '3d-visa-electron-dk',
		);
		foreach ($methods as $type)
		{
			if ($this->params->get($type)) {
				$allowed[] = $type;
			}
		}
		return implode(",", $allowed);
  }

}
