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
class RedformViewForms extends JView {
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) 
	{
		// set the menu
		RedformHelper::setMenu();
		
		/* Get the competitions list */
		$forms = $this->get('Forms');

		/* Get the pagination */
		$pagination = & $this->get('Pagination');
		/* Get the number of contestantst */
		$submitters = $this->get('CountSubmitters');
		
		// filters
		$lists = array();
		
		$state = &$this->get('state');
		
		$options = array( JHTML::_('select.option',  0, '- '.JText::_('COM_REDFORM_FORMS_FILTER_STATE_ALL').' -'),
		                  JHTML::_('select.option',  1, JText::_('COM_REDFORM_FORMS_FILTER_STATE_NOT_ARCHIVED')),
		                  JHTML::_('select.option', -1, JText::_('COM_REDFORM_FORMS_FILTER_STATE_ARCHIVED')),
		                );
		$lists['state'] = JHTML::_('select.genericlist',  $options, 'filter_state', 'onchange="this.form.submit();"', 'value', 'text', $state->get('filter_state')) ;
		
		$lists['order_Dir'] = $state->get('filter_order_Dir');
		$lists['order']     = $state->get('filter_order');
		
		/* Set variabels */
		$this->assignRef('pagination',   $pagination);
		$this->assignRef('forms',   $forms);
		$this->assignRef('submitters',   $submitters);
		$this->assignRef('lists',   $lists);
				
		/* Get the toolbar */
		$this->toolbar();
		
		/* Display the page */
		parent::display($tpl);
	}
	
	function toolbar() 
	{
		JToolBarHelper::title(JText::_('COM_REDFORM' ), 'redform_redform');
		JToolBarHelper::custom('submitters', 'redform_submitters', 'redform_submitters', JText::_('COM_REDFORM_Submitters'), true);
		JToolBarHelper::spacer();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::archiveList();
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList(JText::_('COM_REDFORM_COM_REDEVENT_FORMS_DELETE_WARNING'));
		JToolBarHelper::editListX();
		JToolBarHelper::addNew();
    JToolBarHelper::custom('copy', 'copy', 'copy', JText::_('COM_REDFORM_Clone'), true);
	}
}
