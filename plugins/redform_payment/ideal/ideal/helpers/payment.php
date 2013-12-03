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
class PaymentIdeal extends  RDFPaymenthelper
{
	protected $params = null;

	/**
	 * contructor
	 * @param object plgin params
	 */
	function PaymentIdeal($params)
	{
		$this->params = $params;
	}

	/**
	 * sends the payment request associated to sumbit_key to the payment service
	 * @param string $submit_key
	 */
	function process($request, $return_url = null, $cancel_url = null)
	{
		$posted = JRequest::getVar('partner_id', 0, 'post', 'int');
		if ($posted) {
			return $this->_processpost($request, $return_url, $cancel_url);
		}
		else {
			return $this->_buildform($request, $return_url, $cancel_url);
		}
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
    RedformHelperLog::simpleLog('IDEAL_NOTIFICATION_RECEIVED'. ' ' . $submit_key);
    $transaction_id = JRequest::getVar('transaction_id');

    // first check that we didn't already received the payment...
    $db = &JFactory::getDBO();
    $query = ' SELECT id '
           . ' FROM #__rwf_payment '
           . ' WHERE submit_key = ' . $db->Quote($submit_key)
           . '   AND paid = 1 '
           ;
    $db->setQuery($query);
    $res = $db->loadResult();
    if ($res) // already paid
    {
    	return true;
    }

		$ideal = new iDEAL_Payment($this->params->get('partner_id'));
		$ideal->setTestmode($this->params->get('testmode'));
    $res = $ideal->checkPayment($transaction_id);

		if (!$res) {
			RedformHelperLog::simpleLog(JText::_('IDEAL_PAYMENT_ERROR'). ' / ' . $submit_key.': '.$ideal->getErrorMessage().' ('. $ideal->getErrorCode().')');
			JError::raiseWarning(0, $ideal->getErrorMessage());
			return false;
		}

		$details = $this->_getSubmission($submit_key);

		if (!$ideal->getPaidStatus())
		{
    	RedformHelperLog::simpleLog(JText::_('IDEAL NOTIFICATION PAYMENT REFUSED'). ' / ' . $submit_key);
	  	$this->writeTransaction($submit_key, $ideal->getInfo(), 'NOTPAID', 0);
			return false;
		}
		if ($ideal->getAmount() != round($details->price*100, 2 ))
		{
    	RedformHelperLog::simpleLog(JText::_('IDEAL NOTIFICATION PRICE MISMATCH'). ' / ' . $submit_key);
    	$this->writeTransaction($submit_key, JText::_('IDEAL NOTIFICATION PRICE MISMATCH')."\n".$ideal->getInfo(), 'FAILED', 0);
    	return false;
		}

	  $this->writeTransaction($submit_key, $ideal->getInfo(), 'SUCCESS', 1);

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
				    . ', '. $db->Quote('ideal')
				    . ', '. $db->Quote($paid)
				    . ') ';
    $db->setQuery($query);
    $db->query();
  }

  function _buildform($request, $return_url = null, $cancel_url = null)
  {
		$document = JFactory::getDocument();

		$details = $this->_getSubmission($request->key);
		$submit_key = $request->key;
		require_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'helpers'.DS.'currency.php');
		$currency = RedformHelperLogCurrency::getIsoNumber($details->currency);

		$ideal = new iDEAL_Payment($this->params->get('partner_id'));
		$ideal->setTestmode($this->params->get('testmode'));

		$banks = $ideal->getBanks();
		$options = array();
		foreach ($banks as $id => $b)
		{
			$options[] = JHTML::_('select.option', $id, $b);
		}

		?>
		<form method="post" action="<?php JRoute::_('index.php?option=com_redform&controller=payment');?>">
		<fieldset>
		<legend><?php echo JText::_('iDeal Payment Gateway'); ?></legend>
		<p><?php echo $request->title; ?></p>
		<p><?php echo $details->price; ?> Euros</p>
		<label for="bank_id"><?php echo JText::_('IDEAL_SELECT_BANK'); ?></label>
		<?php echo JHTML::_('select.genericlist', $options, 'bank_id')?>
		</fieldset>

		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" name="partner_id" value="<?php echo $this->params->get('partner_id'); ?>">
		<input type="hidden" name="amount" value="<?php echo round($details->price*100, 2 ); ?>">
		<input type="hidden" name="description" value="<?php echo $request->title; ?>">
		<input type="hidden" name="task" value="process">
		<input type="hidden" name="key" value="<?php echo $request->key; ?>">
		<input type="hidden" name="gw" value="ideal">

		<input type="submit" name="submit" value="<?php echo JText::_('IDEAL_PAY_VIA_IDEAL'); ?>" />
		</form>
		<?php

	  return true;
  }

  function _processpost($request, $return_url = null, $cancel_url = null)
  {
  	$app = &JFactory::getApplication();
    // Check for request forgeries
    JRequest::checkToken() or die( 'Invalid Token' );

    $details = $this->_getSubmission($request->key);
		$submit_key = $request->key;

		$ideal = new iDEAL_Payment($this->params->get('partner_id'));
		$ideal->setTestmode($this->params->get('testmode'));
		$ideal->setProfileKey($this->params->get('profile_key'));

		$bank = sprintf('%04d', JRequest::getInt('bank_id'));

		$res = $ideal->createPayment($bank,
		                      round($details->price*100, 2 ),
		                      $request->title,
			$this->getUrl('notify', $submit_key),
			$this->getUrl('notify', $submit_key));

		if (!$res) {
			RedformHelperLog::simpleLog('IDEAL_PAYMENT_ERROR'. ' for ' . $submit_key.': '.$ideal->getErrorMessage().' ('. $ideal->getErrorCode().')');
			JError::raiseWarning(0, $ideal->getErrorMessage());
			return false;
		}
		$this->writeTransaction($submit_key, $ideal->getInfo(), 'prepared', 0);
		$app->redirect($ideal->getBankURL());

		return true;
  }
}
