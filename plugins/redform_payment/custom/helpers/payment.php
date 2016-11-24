<?php
/**
 * @package     Redform.plugins
 * @subpackage  payment
 *
 * @copyright   Copyright (C) 2008 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Custom payment helper
 *
 * @package     Redform.plugins
 * @subpackage  payment
 * @since       2.5
 */
class PaymentCustom extends  RdfPaymentHelper
{
	/**
	 * name of the gateway for dispatching
	 * @var string
	 */
	protected $gateway = 'custom';

	protected $params = null;

	/**
	 * contructor
	 *
	 * @param   object  $params  plugin params
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

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
		$intro = $this->params->get('instructions');
		$params = $this->params;

		$target = $this->params->get('payment_status', 'pending') == 'paid' ? 'notify' : 'processing';
		$action = $this->getUrl($target, $request->key);

		echo RdfLayoutHelper::render(
			'redform_payment.custom',
			compact('request', 'intro', 'params', 'return_url', 'action'),
			'',
			array('defaultLayoutsPath' => dirname(__DIR__) . '/layouts')
		);

		return true;
	}

	/**
	 * processing
	 *
	 * @return bool|void
	 */
	public function processing()
	{
		$app = JFactory::getApplication();
		$reference = $app->input->get('reference');

		$data = 'tid:' . uniqid();

		$this->writeTransaction($reference, $data, 'pending', 0);

		return true;
	}

	/**
	 * notify
	 *
	 * @return bool|void
	 *
	 * @throws RuntimeException
	 */
	public function notify()
	{
		if ($status = $this->params->get('payment_status', 'pending') != 'paid')
		{
			throw new RuntimeException(500, 'Unauthorized payment status');
		}

		$app = JFactory::getApplication();
		$reference = $app->input->get('reference');

		$data = 'tid:' . uniqid();

		$this->writeTransaction($reference, $data, 'paid', 1);

		return 1;
	}
}
