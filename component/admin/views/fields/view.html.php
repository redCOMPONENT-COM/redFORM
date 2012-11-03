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
		$option = JRequest::getVar('option');
		
		$mainframe = &JFactory::getApplication();
		
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.values.filter_order_Dir',	'filter_order_Dir',	'', 'word' );
		$filter_order		  = $mainframe->getUserStateFromRequest( $option.'.values.filter_order', 		'filter_order', 	'ordering', 'cmd' );
    	$form_id          = $mainframe->getUserStateFromRequest( $option.'.fields.form_id', 'form_id', 0, 'int');

		/* Get the pagination */
		$pagination = $this->get('Pagination');

		/* Get the fields list */
		$fields = $this->get('Fields');

		/* Get the forms */
		$forms = array();
		$forms[] = JHTML::_('select.option', 0, JText::_('COM_REDFORM_All'));
		$forms = array_merge($forms, $this->get('FormsOptions'));
		/* Create the dropdown list */
		$lists['form_id'] = JHTML::_('select.genericlist',  $forms, 'form_id', 'onchange="this.form.submit();"', 'value', 'text', $form_id) ;
		
		// again for batch
		$lists['batch_form_options'] = JHTML::_('select.options', $this->get('FormsOptions'));
		
		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		
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
		JToolBarHelper::title(JText::_('COM_REDFORM_Fields' ), 'redform_fields');
		/* Only show add if there are forms */
		if ($countforms) {
			JToolBarHelper::custom('sanitize', 'redform_details', 'redform_details', JText::_('COM_REDFORM_SANITIZE'), false);
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::spacer();
			JToolBarHelper::deleteList(JText::_('COM_REDFORM_COM_REDEVENT_FIELDS_DELETE_WARNING'));
			JToolBarHelper::editListX();
			JToolBarHelper::addNew();
			JToolBarHelper::divider();
			if (JFactory::getUser()->authorise('core.admin', 'com_redform'))
			{
				JToolBarHelper::preferences('com_redform');
			}
		}
		
		/* Display the page */
		parent::display($tpl);
	}		
}
