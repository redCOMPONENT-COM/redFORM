<?php
/**
 * @package     Redform.plugins
 * @subpackage  payment
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

require_once 'ideal.class.php';

/**
 * Ideal payment helper
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class PaymentIdeal extends  RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'ideal';

	protected $params = null;

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
		$posted = JFactory::getApplication()->input->get('partner_id', 0, 'post', 'int');

		if ($posted)
		{
			return $this->_processpost($request, $return_url, $cancel_url);
		}
		else
		{
			return $this->_buildform($request, $return_url, $cancel_url);
		}
	}

	/**
	 * handle the recpetion of notification
	 *
	 * @return bool paid status
	 */
	public function notify()
	{
		$app = JFactory::getApplication();
		$paid = 0;

		$reference = $app->input->get('reference');
		$app->input->set('reference', $reference);
		RdfHelperLog::simpleLog('IDEAL_NOTIFICATION_RECEIVED' . ' ' . $reference);
		$transaction_id = $app->input->get('transaction_id');

		$ideal = new iDEAL_Payment($this->params->get('partner_id'));
		$ideal->setTestmode($this->params->get('testmode'));
		$res = $ideal->checkPayment($transaction_id);

		if (!$res)
		{
			RdfHelperLog::simpleLog(
				JText::_('IDEAL_PAYMENT_ERROR') . ' / ' . $reference . ': ' . $ideal->getErrorMessage()
				. ' (' . $ideal->getErrorCode() . ')'
			);

			return false;
		}

		$details = $this->getDetails($reference);

		if (!$ideal->getPaidStatus())
		{
			RdfHelperLog::simpleLog(JText::_('IDEAL NOTIFICATION PAYMENT REFUSED') . ' / ' . $reference);
			$this->writeTransaction($reference, $ideal->getInfo(), 'NOTPAID', 0);

			return false;
		}

		if ($ideal->getAmount() != round($this->getPrice($details) * 100))
		{
			RdfHelperLog::simpleLog(JText::_('IDEAL NOTIFICATION PRICE MISMATCH') . ' / ' . $reference);
			$this->writeTransaction($reference, JText::_('IDEAL NOTIFICATION PRICE MISMATCH') . "\n" . $ideal->getInfo(), 'FAILED', 0);

			return false;
		}

		$this->writeTransaction($reference, $ideal->getInfo(), 'SUCCESS', 1);

		return $paid;
	}

	/**
	 * Get form html
	 *
	 * @param   object  $request     payment request object
	 * @param   string  $return_url  return url for redirection
	 * @param   string  $cancel_url  cancel url for redirection
	 *
	 * @return html
	 */
	private function _buildform($request, $return_url = null, $cancel_url = null)
	{
		$document = JFactory::getDocument();

		$details = $this->getDetails($request->key);
		$reference = $request->key;
		$currency = RHelperCurrency::getIsoNumber($details->currency);

		$ideal = new iDEAL_Payment($this->params->get('partner_id'));
		$ideal->setTestmode($this->params->get('testmode'));

		$banks = $ideal->getBanks();
		$options = array();

		foreach ($banks as $id => $b)
		{
			$options[] = JHTML::_('select.option', $id, $b);
		}

		?>
		<form method="post" action="<?php JRoute::_('index.php?option=com_redform'); ?>">
			<fieldset>
				<legend><?php echo JText::_('iDeal Payment Gateway'); ?></legend>
				<p><?php echo $request->title; ?></p>

				<p><?php echo $details->getPrice(); ?> Euros</p>
				<label for="bank_id"><?php echo JText::_('IDEAL_SELECT_BANK'); ?></label>
				<?php echo JHTML::_('select.genericlist', $options, 'bank_id') ?>
			</fieldset>

			<?php echo JHTML::_('form.token'); ?>
			<input type="hidden" name="partner_id" value="<?php echo $this->params->get('partner_id'); ?>">
			<input type="hidden" name="amount" value="<?php echo round($this->getPrice($details) * 100); ?>">
			<input type="hidden" name="description" value="<?php echo $request->title; ?>">
			<input type="hidden" name="task" value="payment.process">
			<input type="hidden" name="reference" value="<?php echo $request->key; ?>">
			<input type="hidden" name="gw" value="ideal">

			<input type="submit" name="submit" value="<?php echo JText::_('IDEAL_PAY_VIA_IDEAL'); ?>"/>
		</form>
		<?php

		return true;
	}

	/**
	 * Process post
	 *
	 * @param   object  $request     payment request object
	 * @param   string  $return_url  return url for redirection
	 * @param   string  $cancel_url  cancel url for redirection
	 *
	 * @return bool
	 */
	private function _processpost($request, $return_url = null, $cancel_url = null)
	{
		$app = JFactory::getApplication();

		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');

		$details = $this->getDetails($request->key);
		$reference = $request->key;

		$ideal = new iDEAL_Payment($this->params->get('partner_id'));
		$ideal->setTestmode($this->params->get('testmode'));
		$ideal->setProfileKey($this->params->get('profile_key'));

		$bank = sprintf('%04d', JRequest::getInt('bank_id'));

		$res = $ideal->createPayment(
			$bank,
			round($this->getPrice($details) * 100),
			$request->title,
			$this->getUrl('notify', $reference),
			$this->getUrl('notify', $reference)
		);

		if (!$res)
		{
			RdfHelperLog::simpleLog(
				'IDEAL_PAYMENT_ERROR' . ' for ' . $reference . ': ' . $ideal->getErrorMessage()
				. ' (' . $ideal->getErrorCode() . ')'
			);

			return false;
		}

		$this->writeTransaction($reference, $ideal->getInfo(), 'prepared', 0);
		$app->redirect($ideal->getBankURL());

		return true;
	}
}
