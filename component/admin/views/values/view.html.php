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
class RedformViewValues extends JView 
{
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) 
	{
		/* Get the pagination */
		$pagination = $this->get('Pagination');

		/* Get the values list */
		$values = $this->get('Values');

		/* Check if there are any forms */
		$fields = $this->get('TotalFields');

		/* Get the forms */
		$forms = (array) $this->get('FormsOptions');
		array_unshift($forms, JHTML::_('select.option', 0, JText::_('COM_REDFORM_All')));
		/* Create the dropdown list */
		$lists['form_id'] = JHTML::_('select.genericlist',  $forms, 'form_id', '', 'value', 'text', JRequest::getVar('form_id', 0)) ;

		/* Set variabels */
		$this->assignRef('pagination', $pagination);
		$this->assignRef('values', $values);
		$this->assignRef('fields', $fields);
		$this->assignRef('lists', $lists);
		
    // set the menu
    RedformHelper::setMenu();
				
		/* Get the toolbar */
		JToolBarHelper::title(JText::_('COM_REDFORM_Values' ), 'redform_values');
		if ($fields > 0) {
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::spacer();
			JToolBarHelper::deleteList();
			JToolBarHelper::editListX();
			JToolBarHelper::addNew();
		}
		
		/* Display the page */
		parent::display($tpl);
	}
		
	function toolbar() {
		switch (JRequest::getCmd('task')) {
			case 'edit':
			case 'apply':
			case 'add':
				switch (JRequest::getCmd('task')) {
					case 'add':
						JToolBarHelper::title(JText::_('COM_REDFORM_Add_Value' ), 'redform_plus');
						break;
					default:
						JToolBarHelper::title(JText::_('COM_REDFORM_Edit_Value' ), 'redform_plus');
						break;
				}
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				break;
			default:
				JToolBarHelper::title(JText::_('COM_REDFORM_Values' ), 'redform_values');
				if ($this->get('Total', 'fields') > 0) {
					JToolBarHelper::publishList();
					JToolBarHelper::unpublishList();
					JToolBarHelper::spacer();
					JToolBarHelper::deleteList();
					JToolBarHelper::editListX();
					JToolBarHelper::addNew();
				}
				break;
		}
	}
}
?>