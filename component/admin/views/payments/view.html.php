<?php
/**
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

/* No direct access */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

/**
 * redFORM View
 */
class RedformViewPayments extends RdfView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel();

		$this->items = $model->getItems();
		$this->state = $model->getState();
		$this->pagination = $model->getPagination();

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDFORM_PAYMENTS_HISTORY');
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

		if ($canDoCore->get('core.edit'))
		{
			$new = RToolbarBuilder::createNewButton('payment.new');
			$firstGroup->addButton($new);

			$edit = RToolbarBuilder::createEditButton('payment.edit');
			$firstGroup->addButton($edit);
		}

		// Delete / Trash
		if ($canDoCore->get('core.delete'))
		{
			$delete = RToolbarBuilder::createDeleteButton('payment.delete');

			$secondGroup->addButton($delete);
		}

		// Options
		$back = RToolbarBuilder::createCancelButton('payments.back');
		$thirdGroup->addButton($back);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)
			->addGroup($secondGroup)
			->addGroup($thirdGroup);

		return $toolbar;
	}
}
