<?php
/**
 * @package     Redform.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Logs View
 *
 * @package     Redform.Backend
 * @subpackage  Views
 * @since       1.0
 */
class RedformViewLogs extends RdfView
{
	/**
	 * @var  array
	 */
	public $items;

	/**
	 * @var array
	 */
	public $stoolsOptions = array();

	/**
	 * display
	 *
	 * @param   string  $tpl  template
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		// Get data from the model
		$this->items = $this->get('Items');

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDFORM_LOG_LIST_TITLE');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$params = JComponentHelper::getParams('com_redform');

		$canDoCore = RedformHelpersAcl::getActions();
		$user = JFactory::getUser();

		$firstGroup = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;
		$thirdGroup = new RToolbarButtonGroup;

		// Options
		if ($canDoCore->get('core.manage'))
		{
			$options = RToolbarBuilder::createStandardButton('clearlog', 'COM_REDFORM_LOG_LIST_CLEAR_LOG', 'btn-danger', 'icon-remove-sign', false);
			$firstGroup->addButton($options);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup);

		return $toolbar;
	}
}
