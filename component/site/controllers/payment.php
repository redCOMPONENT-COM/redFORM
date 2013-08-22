<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
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
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Redform Controller
*/
class RedformControllerPayment extends JController {

	/**
	 * Method to display the view
	 *
	 * @access   public
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask('cancel', 'paymentcancelled');
	}

	function select()
	{
		$app  = JFactory::getApplication();
		$key  = $app->input->get('key');

		if ($app->input->get('lang'))
		{
			$lang_v = "&lang=" . $app->input->get('lang');
		}

		$model   = $this->getModel('payment');
		$model->setSubmitKey($key);
		$options = $model->getGatewayOptions();

		if (count($options) == 1)
		{
			$this->setRedirect('index.php?option=com_redform&controller=payment&task=process&key='.$key.'&gw='.$options[0]->value . $lang_v);
			$this->redirect();
		}
		JRequest::setVar('view',   'payment');
		JRequest::setVar('layout', 'select');
		$this->display();
	}

	function process()
	{
		$gw = JRequest::getVar('gw', '');
		if (empty($gw)) {
			JError::raise(0, 'MISSING GATEWAY');
			return false;
		}

		$model  = &$this->getModel('payment');
		$helper = $model->getGatewayHelper($gw);
		$key    = JRequest::getVar('key');

		$details = $model->getPaymentDetails($key);

		$res = $helper->process($details);

		//echo '<pre>';print_r($res); echo '</pre>';exit;
		// get payment helper from selected gateway
	}


	function processing()
	{
		$app = JFactory::getApplication();

		$submit_key = $app->input->getString('key');

		if ($app->input->get('lang'))
		{
			$lang_v = "&lang=" . $app->input->get('lang');;
		}

		$model = $this->getModel('payment');
		$model->setSubmitKey($submit_key);

		$submitters = $model->getSubmitters();
		if (count($submitters))
		{
			$first = current($submitters);
			if (!empty($first->integration))
			{
				switch ($first->integration)
				{
					case 'redevent':
						$app->redirect('index.php?option=com_redevent&view=payment&submit_key='.$submit_key.'&state=processing' . $lang_v);
						break;

					default:
						$app->redirect('index.php?option=com_'.$first->integration.'&view=payment&submit_key='.$submit_key.'&state=processing' . $lang_v);
						break;
				}
			}
		}

		// Analytics for default landing page
		if (redFORMHelperAnalytics::isEnabled())
		{
			$payement = $model->getPaymentDetails($submit_key);

			// Add transaction
			$trans = new stdclass;
			$trans->id = $submit_key;
			$trans->affiliation = $payement->form;
			$trans->revenue = $model->getPrice();

			redFORMHelperAnalytics::addTrans($trans);

			// Add submitters as items
			foreach ($submitters as $s)
			{
				$item = new stdclass;
				$item->id = $submit_key;
				$item->productname = 'submitter' . $s->id;
				$item->sku = 'submitter' . $s->id;
				$item->category = '';
				$item->price = $s->price;
				redFORMHelperAnalytics::addItem($item);
			}

			redFORMHelperAnalytics::trackTrans();
		}

		JRequest::setVar('view',   'payment');
		JRequest::setVar('layout', 'final');
		JRequest::setVar('state', 'processing');
		$this->display();
	}

	function paymentcancelled()
	{
		$mainframe = JFactory::getApplication();

		$msg = JText::_('COM_REDFORM_PAYMENT_CANCELLED');
		$mainframe->redirect('index.php', $msg);
	}


	function notify()
	{
		$mainframe = JFactory::getApplication();

		$submit_key = JRequest::getVar('key');
		$gw = JRequest::getVar('gw', '');
		RedformHelperLog::simpleLog('PAYMENT NOTIFICATION RECEIVED'. ': ' . $gw);
		if (empty($gw)) {
			RedformHelperLog::simpleLog('PAYMENT NOTIFICATION MISSING GATEWAY'.': '.$gw);
			return false;
		}

		$model = &$this->getModel('payment');
		$alreadypaid = $model->hasAlreadyPaid();
		$helper = $model->getGatewayHelper($gw);

		$res = $helper->notify();

		if ($res) { // the payment was received !
			//TODO: send a mail ?
			JRequest::setVar('state', 'accepted');
			if (!$alreadypaid) {
				$model->notifyPaymentReceived();
			}
		}
		else {
			JRequest::setVar('state', 'failed');
		}

		$submitters = $model->getSubmitters();
		if (count($submitters))
		{
			$first = current($submitters);
			if (!empty($first->integration))
			{
				switch ($first->integration)
				{
					case 'redevent':
						$mainframe->redirect('index.php?option=com_redevent&view=payment&submit_key='.$submit_key.'&state='.($res ? 'accepted' : 'refused'));
						break;

					default:
						$mainframe->redirect('index.php?option=com_'.$first->integration.'&view=payment&submit_key='.$submit_key.'&state='.($res ? 'accepted' : 'refused'));
						break;
				}
			}
		}

		JRequest::setVar('view',   'payment');
		JRequest::setVar('layout', 'final');

		$this->display();
	}

	/**
	 * this is a test function for payment notification
	 */
	function notifytest()
	{
		$model = &$this->getModel('payment');
		$model->notifyPaymentReceived();
	}
}
