<?php
/**
 * @package     Redevent.Plugin
 *
 * @copyright   Copyright (C) 2008-2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Class RdfRfieldDaterangelist
 *
 * @since  3.3.19
 */
class RdfRfieldDaterange extends \RdfRfield
{
	/**
	 * @var string
	 */
	protected $type = 'daterange';

	/**
	 * @var JRegistry
	 */
	protected $pluginParams;

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$element = \RdfLayoutHelper::render(
			'rform.rfield.' . $this->type,
			$this,
			null,
			array('component' => 'com_redform', 'defaultLayoutsPath' => dirname(__DIR__) . '/layouts')
		);

		return $element;
	}

	/**
	 * Set params from plugin
	 *
	 * @param   JRegistry  $params  params
	 *
	 * @return RdfRfieldDaterangelist
	 */
	public function setPluginParams(JRegistry $params)
	{
		$this->pluginParams = $params;

		return $this;
	}

	/**
	 * Get properties
	 *
	 * @return array
	 */
	public function getInputProperties()
	{
		$properties = parent::getInputProperties();

		$properties['data-format'] = $this->params->get('format', 'YYYY-MM-DD');

		$excluded = $this->params->get('excluded_dates');

		if (!empty($excluded))
		{
			$lines = explode("\n", $excluded);
			$dates = array();

			foreach ($lines as $l)
			{
				$l = trim($l);

				if (empty($l))
				{
					continue;
				}

				$dates[]= JFactory::getDate($l)->format('Y-m-d');
			}

			if (!empty($dates))
			{
				$properties['data-excluded'] = implode(",", $dates);
			}
		}

		if ($placeholder = $this->getParam('placeholder'))
		{
			$properties['placeholder'] = addslashes($placeholder);
		}

		return $properties;
	}
}
