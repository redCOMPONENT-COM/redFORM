<?php
/**
 * @package     Redform
 * @subpackage  Payment
 * @copyright   Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
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

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.event.plugin');

require_once 'paymenthelper.class.php';

abstract class RDFPaymentPlugin extends JPlugin
{
	/**
	 * Name of the plugin gateway
	 * @var string
	 */
	protected $gateway = null;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
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
	 * @param   array   &$gateways  array of current gateways
	 * @param   object  $details    details for payment
	 *
	 * @return bool
	 */
	public function onGetGateway(&$gateways, $details = null)
	{
		$reflector = new ReflectionClass(get_class($this));
		$dirpath   = dirname($reflector->getFileName());

		require_once $dirpath . '/helpers/payment.php';

		$helperClass = 'Payment' . ucfirst($this->gateway);
		$helper = new $helperClass($this->params);

		if (!$details || $helper->currencyIsAllowed($details->currency))
		{
			$label = $this->params->get('gatewaylabel') ? $this->params->get('gatewaylabel') . ' (' . $this->gateway . ')' : $this->gateway;
			$gateways[] = array('name' => $this->gateway, 'helper' => $helper, 'label' => $label);
		}

		return true;
	}
}
