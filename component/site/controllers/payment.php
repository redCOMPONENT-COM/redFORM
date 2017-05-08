<?php
/**
 * @package    Redform.Front
 * @copyright  Redform (C) 2008-2014 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Redform Payments Controller
 *
 * @package  Redform.Front
 * @since    2.0
 */
class RedformControllerPayment extends JControllerLegacy
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
		$app = JFactory::getApplication();
		$lang_v = '';

		if ($app->input->get('lang'))
		{
			$lang_v = "&lang=" . $app->input->get('lang');
		}

		$model = $this->getModel('payment');

		// We need a cart for this submit key
		try
		{
			if ($cartId = $this->input->get('cart_id'))
			{
				$cart = RdfEntityCart::load($cartId);
			}
			elseif ($submitKey = $app->input->get('key'))
			{
				$cart = $model->getSubmissionCart($submitKey);
			}
			else
			{
				throw new Exception('invalid cart');
			}
		}
		catch (Exception $e)
		{
			echo 'Error getting new cart';

			return;
		}

		$model->setCartReference($cart->reference);
		$options = $model->getGatewayOptions();
		$requireBilling = $model->isRequiredBilling();

		if ($requireBilling)
		{
			$this->setRedirect('index.php?option=com_redform&view=payment&layout=billing&reference=' . $cart->reference . $lang_v);
		}
		elseif (count($options) == 1)
		{
			$gateway = reset($options);
			$this->setRedirect(
				'index.php?option=com_redform&task=payment.process&reference=' . $cart->reference . '&gw=' . $gateway->value . $lang_v
			);
		}
		else
		{
			$this->setRedirect('index.php?option=com_redform&view=payment&layout=select&reference=' . $cart->reference . $lang_v);
		}
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
			throw new Exception('MISSING GATEWAY', 500);
		}

		$model  = $this->getModel('payment');
		$helper = $model->getGatewayHelper($gw);
		$key    = $app->input->get('reference');

		$model->setCartReference($key);
		$cart = $model->getCart();

		if ($model->isRequiredBilling())
		{
			$data  = $this->input->post->get('jform', array(), 'array');
			$data['cart_id'] = $cart->id;

			$billingModel = $this->getModel('billing');
			$form = $billingModel->getForm();
			$validData = $billingModel->validate($form, $data);

			// Check for validation errors.
			if ($validData === false)
			{
				// Get the validation messages.
				$errors = $billingModel->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				// Redirect back to the edit screen.
				$this->setRedirect(
					'index.php?option=com_redform&view=payment&layout=billing&reference=' . $cart->reference
				);

				return;
			}

			// Attempt to save the data.
			if (!$billingModel->save($validData))
			{
				// Redirect back to the edit screen.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $billingModel->getError()));
				$this->setMessage($this->getError(), 'error');

				// Redirect back to the edit screen.
				$this->setRedirect(
					'index.php?option=com_redform&view=payment&layout=billing&reference=' . $cart->reference
				);

				return;
			}
		}
		else
		{
			// Try to auto bill
			$billingModel = $this->getModel('billing');
			$billingModel->setCartReference($cart->reference);
			$billingModel->createAutoBilling();
		}

		$details = $model->getPaymentDetails();

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

		$key = $app->input->getString('reference');
		$gw = $app->input->get('gw', '');

		$model = $this->getModel('payment');
		$model->setCartReference($key);

		// Forward to the plugin
		$helper = $model->getGatewayHelper($gw);
		$helper->processing();

		$submitters = $model->getSubmitters();

		if (count($submitters))
		{
			$first = current($submitters);

			if ($app->input->get('lang'))
			{
				$lang_v = "&lang=" . $app->input->get('lang');
			}

			if (!empty($first->integration))
			{
				switch ($first->integration)
				{
					case 'redevent':
						$app->redirect(
							'index.php?option=com_redevent&view=payment&submit_key=' . $first->submit_key . '&state=processing' . $lang_v
							. '&cart=' . $key
						);
						break;

					default:
						$app->redirect(
							'index.php?option=com_' . $first->integration . '&view=payment&submit_key=' . $first->submit_key . '&state=processing'
							. $lang_v
							. '&cart=' . $key
						);
						break;
				}
			}
		}

		// Analytics for default landing page
		if (RdfHelperAnalytics::isEnabled())
		{
			$payment = $model->getPaymentDetails();

			// Add transaction
			$trans = new stdclass;
			$trans->id = $key;
			$trans->affiliation = $payment->form;
			$trans->revenue = $model->getPrice();
			$trans->currency = $payment->currency;

			RdfHelperAnalytics::addTrans($trans);

			// Add submitters as items
			foreach ($submitters as $s)
			{
				$item = new stdclass;
				$item->id = $key;
				$item->productname = 'submitter' . $s->id;
				$item->sku = 'submitter' . $s->id;
				$item->category = '';
				$item->price = $s->price;
				$item->currency = $s->currency;
				RdfHelperAnalytics::addItem($item);
			}

			RdfHelperAnalytics::trackTrans();
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
	public function cancelled()
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

		$key = $app->input->get('reference');
		$gw = $app->input->get('gw', '');

		RdfHelperLog::simpleLog('PAYMENT NOTIFICATION RECEIVED: ' . $gw);

		if (empty($gw))
		{
			RdfHelperLog::simpleLog('PAYMENT NOTIFICATION MISSING GATEWAY: ' . $gw);

			throw new Exception('PAYMENT NOTIFICATION MISSING GATEWAY: ' . $gw, 404);
		}

		$model = $this->getModel('payment');
		$model->setCartReference($key);

		$alreadypaid = $model->hasAlreadyPaid();
		$helper      = $model->getGatewayHelper($gw);

		$res = $helper->notify();

		if ($res)
		{
			// The payment was received !
			$app->input->set('state', 'accepted');

			if ($alreadypaid)
			{
				// Dont' forward to integration, to prevent side effects. This should only happen for async callbacks anyway.
				$app->enqueueMessage('COM_REDFORM_PAYMENT_NOTIFICATION_RECEIVED_ALREADY_PAID', 'notice');

				$app->input->set('view', 'payment');
				$app->input->set('layout', 'final');

				return $this->display();
			}

			$model->setPaymentRequestAsPaid();

			$cart = $model->getCart();

			JPluginHelper::importPlugin('redform_integration');
			RFactory::getDispatcher()->trigger('onAfterRedformCartPaymentAccepted', array($cart));

			// Built-in notifications
			if (!$model->notifyPaymentReceived())
			{
				$app->enqueueMessage($model->getError(), 'error');
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
						$app->redirect('index.php?option=com_redevent&view=payment&submit_key=' . $first->submit_key
							. '&state=' . ($res ? 'accepted' : 'refused')
						);
						break;

					default:
						$app->redirect('index.php?option=com_' . $first->integration . '&view=payment&submit_key=' . $first->submit_key
							. '&state=' . ($res ? 'accepted' : 'refused') . '&cart=' . $key
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
