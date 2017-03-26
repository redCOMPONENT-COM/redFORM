<?php
/**
 * @package     Redform.Libraries
 * @subpackage  payment
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.event.plugin');

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');

/**
 * Payment plugin abstract class
 *
 * @package     Redform.Libraries
 * @subpackage  payment
 * @since       2.5
 */
abstract class RdfPaymentPlugin extends RPlugin
{
	/**
	 * Name of the plugin gateway
	 * @var string
	 */
	protected $gateway = null;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @since   2.0
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Adds Gateway to list if available for payment
	 *
	 * @param   array   $gateways  array of current gateways
	 * @param   object  $details   details for payment
	 *
	 * @return boolean
	 */
	public function onGetGateway(&$gateways, $details = null)
	{
		$reflector = new ReflectionClass(get_class($this));
		$dirpath   = dirname($reflector->getFileName());

		require_once $dirpath . '/helpers/payment.php';

		$helperClass = 'Payment' . ucfirst($this->gateway);
		$helper = new $helperClass($this->params);
		$helper->plugin = $this;

		if (!$details || $helper->currencyIsAllowed($details->currency))
		{
			$label = $this->params->get('gatewaylabel', $this->gateway);
			$backendlabel = $this->params->get('gatewaybackendlabel', $this->gateway);
			$gateways[] = array('name' => $this->gateway, 'helper' => $helper, 'label' => $label, 'backendlabel' => $backendlabel);
		}

		return true;
	}
}
