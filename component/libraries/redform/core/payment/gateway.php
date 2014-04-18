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
class RdfCorePaymentGateway {

	protected $gateways;

	protected $config;

	/**
	 * Return gateway options
	 *
	 * @param   object  $config  options to filter the gateways
	 *
	 * @return array
	 */
	public function getOptions($config = null)
	{
		$this->config = $config;
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
			$dispatcher->trigger('onFilterGateways', array(&$options, $this->config));
		}

		return $options;
	}

	/**
	 * get redform plugin payment gateways, as an array of name and helper class
	 *
	 * @return array
	 */
	protected function getGateways()
	{
		if (empty($this->gateways))
		{
			JPluginHelper::importPlugin('redform_payment');
			$dispatcher = JDispatcher::getInstance();

			$gateways = array();
			$dispatcher->trigger('onGetGateway', array(&$gateways, $this->config));
			$this->_gateways = $gateways;
		}

		return $this->_gateways;
	}
}
