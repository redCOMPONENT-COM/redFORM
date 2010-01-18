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
class RedformViewFields extends JView {
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) 
	{
		/* Get the pagination */
		$pagination = $this->get('Pagination');

		/* Get the fields list */
		$fields = $this->get('Fields');

		/* Get the forms */
		$forms = array();
		$forms[] = JHTML::_('select.option', 0, JText::_('All'));
		$forms = array_merge($forms, $this->get('FormsOptions'));

		/* Create the dropdown list */
		$lists['form_id'] = JHTML::_('select.genericlist',  $forms, 'form_id', '', 'value', 'text', JRequest::getVar('form_id')) ;

		/* Check if there are any forms */
		$countforms = (count($forms) > 1);

		/* Set variabels */
		$this->assignRef('pagination', $pagination);
		$this->assignRef('fields', $fields);
		$this->assignRef('lists', $lists);
		$this->assignRef('countforms', $countforms);
				
		// set menu
		RedformHelper::setMenu();
		
    /* Get the toolbar */
		JToolBarHelper::title(JText::_( 'Fields' ), 'redform_fields');
		/* Only show add if there are forms */
		if ($countforms) {
			JToolBarHelper::custom('sanitize', 'redform_details', 'redform_details', JText::_('SANITIZE'), false);
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::spacer();
			JToolBarHelper::deleteList(JText::_('Are you sure you want to delete the fields and related values?'));
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
						JToolBarHelper::title(JText::_( 'Add Field' ), 'redform_plus');
						break;
					default:
						JToolBarHelper::title(JText::_( 'Edit Field' ), 'redform_plus');
						break;
				}
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				break;
			default:
				JToolBarHelper::title(JText::_( 'Fields' ), 'redform_fields');
				/* Only show add if there are forms */
				if ($this->get('Total', 'redform') > 0) {

					JToolBarHelper::custom('sanitize', 'redform_details', 'redform_details', JText::_('SANITIZE'), false);
					JToolBarHelper::publishList();
					JToolBarHelper::unpublishList();
					JToolBarHelper::spacer();
					JToolBarHelper::deleteList(JText::_('Are you sure you want to delete the fields and related values?'));
					JToolBarHelper::editListX();
					JToolBarHelper::addNew();
				}
				break;
		}
	}
}
?>
