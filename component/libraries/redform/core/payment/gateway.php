<?php
/**
 * @package     Redform.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Class RdfCorePaymentGateway
 *
 * @package     Redform.Libraries
 * @subpackage  Helper
 * @since       2.5
 */
class RdfCorePaymentGateway
{
	protected $gateways;

	protected $paymentDetails;

	/**
	 * Constructor
	 *
	 * @param   object  $paymentDetails  payment details data to filter the gateways
	 */
	public function __construct($paymentDetails = null)
	{
		$this->paymentDetails = $paymentDetails;
	}

	/**
	 * Return gateway options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		$options = array();

		if ($gateways = $this->getGateways())
		{
			foreach ($gateways as $g)
			{
				if (isset($g['label']))
				{
					$label = $g['label'];
				}
				else
				{
					$label = $g['name'];
				}

				$options[] = JHTML::_('select.option', $g['name'], $label);
			}

			// Filter gateways through plugins
			JPluginHelper::importPlugin('redform_payment');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onFilterGateways', array(&$options, $this->paymentDetails));
		}

		return $options;
	}

	/**
	 * get redform plugin payment gateways, as an array of name and helper class
	 *
	 * @return array
	 */
	public function getGateways()
	{
		if (empty($this->gateways))
		{
			JPluginHelper::importPlugin('redform_payment');
			$dispatcher = JDispatcher::getInstance();

			$gateways = array();
			$dispatcher->trigger('onGetGateway', array(&$gateways, $this->paymentDetails));
			$this->gateways = $gateways;
		}

		return $this->gateways;
	}
}
