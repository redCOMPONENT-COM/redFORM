<?php
/**
 * @package    Redform.Admin
 * @copyright  Redform (C) 2008-2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Redform Payments Controller
 *
 * @package  Redform.Admin
 * @since    2.0
 */
class RedformControllerPayment extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access   public
	 */
	public function __construct()
	{
		parent::__construct();
		$this->registerTask('cancel', 'paymentcancelled');
	}

	/**
	 * Select gateway
	 *
	 * @return void
	 */
	public function select()
	{
		$app    = JFactory::getApplication();
		$key    = $app->input->get('key');
		$lang_v = '';

		if ($app->input->get('lang'))
		{
			$lang_v = "&lang=" . $app->input->get('lang');
		}
		$model = $this->getModel('payment');
		$model->setSubmitKey($key);
		$options = $model->getGatewayOptions();

		if (count($options) == 1)
		{
			$this->setRedirect('index.php?option=com_redform&controller=payment&task=process&key=' . $key . '&gw=' . $options[0]->value . $lang_v);
			$this->redirect();
		}

		$app->input->set('view', 'payment');
		$app->input->set('layout', 'select');
		$this->display();
	}

	/**
	 * Process with gateway payment (display payment form for gateway)
	 *
	 * @throws Exception
	 * @return void
	 */
	public function process()
	{
		$app = JFactory::getApplication();
		$gw  = $app->input->get('gw', '');

		if (empty($gw))
		{
			JError::raiseError(404, 'MISSING GATEWAY');
		}

		$model  = $this->getModel('payment');
		$helper = $model->getGatewayHelper($gw);
		$key    = $app->input->get('key');

		$details = $model->getPaymentDetails($key);

		if (!$res = $helper->process($details))
		{
			throw new Exception('Error displaying gateway');
		}
	}

	/**
	 * Handle gateway processing message
	 * Occurs when returning from gateway, but payment was not yet confirmed
	 *
	 * @return void
	 */
	public function processing()
	{
		$app = JFactory::getApplication();

		$submit_key = $app->input->getString('key', '');

		if ($app->input->get('lang'))
		{
			$lang_v = "&lang=" . $app->input->get('lang');
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
						$app->redirect('index.php?option=com_redevent&view=payment&submit_key=' . $submit_key . '&state=processing' . $lang_v);
						break;

					default:
						$app->redirect('index.php?option=com_' . $first->integration . '&view=payment&submit_key=' . $submit_key . '&state=processing' . $lang_v);
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

		$app->input->set('view', 'payment');
		$app->input->set('layout', 'final');
		$app->input->set('state', 'processing');

		$this->display();
	}

	/**
	 * handle payment cancelled notifications from gateways
	 *
	 * @return void
	 */
	public function paymentcancelled()
	{
		$app = JFactory::getApplication();

		$msg = JText::_('COM_REDFORM_PAYMENT_CANCELLED');
		$app->redirect('index.php', $msg);
	}

	/**
	 * Handle notifications (callback) from the gateways
	 *
	 * @throws Exception
	 * @return void
	 */
	public function notify()
	{
		$app = JFactory::getApplication();

		$submit_key = $app->input->get('key');
		$gw = $app->input->get('gw', '');

		RedformHelperLog::simpleLog('PAYMENT NOTIFICATION RECEIVED' . ': ' . $gw);

		if (empty($gw))
		{
			RedformHelperLog::simpleLog('PAYMENT NOTIFICATION MISSING GATEWAY' . ': ' . $gw);

			throw new Exception('PAYMENT NOTIFICATION MISSING GATEWAY' . ': ' . $gw, 404);
		}

		$model = $this->getModel('payment');

		$alreadypaid = $model->hasAlreadyPaid();
		$helper      = $model->getGatewayHelper($gw);

		$res = $helper->notify();

		if ($res)
		{
			// The payment was received !
			$app->input->set('state', 'accepted');

			if (!$alreadypaid)
			{
				// Trigger event for custom handling
				JPluginHelper::importPlugin('redform');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAfterPaymentVerified', array($submit_key));

				// Built-in notifications
				$model->notifyPaymentReceived();
			}
		}
		else
		{
			$app->input->set('state', 'failed');
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
						$app->redirect('index.php?option=com_redevent&view=payment&submit_key=' . $submit_key
							. '&state=' . ($res ? 'accepted' : 'refused')
						);
						break;

					default:
						$app->redirect('index.php?option=com_' . $first->integration . '&view=payment&submit_key=' . $submit_key
							. '&state=' . ($res ? 'accepted' : 'refused')
						);
						break;
				}
			}
		}

		$app->input->set('view', 'payment');
		$app->input->set('layout', 'final');

		$this->display();
	}

	/**
	 * this is a test function for payment notification
	 *
	 * @return void
	 */
	public function notifytest()
	{
		$model = $this->getModel('payment');
		$model->notifyPaymentReceived();
	}
}
