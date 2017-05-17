<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 *
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Class RdfRfieldAcymailinglist
 *
 * @since  __deploy_version__
 */
class RdfRfieldAcymailinglist extends \RdfRfieldCheckbox
{
	/**
	 * @var string
	 */
	protected $type = 'acymailing';

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
	 * @return RdfRfieldAcymailinglist
	 */
	public function setPluginParams(JRegistry $params)
	{
		$this->pluginParams = $params;

		return $this;
	}

	/**
	 * Return field options (for select, radio, etc...)
	 *
	 * @return mixed
	 */
	protected function getOptions()
	{
		$lists = json_decode($this->getParam('lists'));

		if (empty($lists))
		{
			return false;
		}

		$options = array();

		foreach ($lists->id as $i => $id)
		{
			$options[] = JHtml::_('select.option', $lists->id[$i], $lists->label[$i]);
		}

		return $options;
	}
}
