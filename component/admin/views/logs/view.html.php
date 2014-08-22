<?php
/**
 * @version 1.0 $Id: view.html.php 165 2009-06-01 16:37:38Z julien $
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
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
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the redevent log
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
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
