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

		/* Set variabels */
		$this->assignRef('pagination',   $pagination);
		$this->assignRef('forms',   $forms);
		$this->assignRef('submitters',   $submitters);
				
		/* Get the toolbar */
		$this->toolbar();
		
		/* Display the page */
		parent::display($tpl);
	}
	
	function toolbar() 
	{
		JToolBarHelper::title(JText::_('COM_REDFORM' ), 'redform_redform');
		JToolBarHelper::addNew();
		JToolBarHelper::editListX();
    JToolBarHelper::custom('copy', 'copy', 'copy', 'COM_REDFORM_Clone', true);
		JToolBarHelper::deleteList(JText::_('COM_REDFORM_COM_REDEVENT_FORMS_DELETE_WARNING'));
		JToolBarHelper::divider();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::divider();
		JToolBarHelper::custom('submitters', 'redform_submitters', 'redform_submitters', 'COM_REDFORM_Submitters', true);
		JToolBarHelper::divider();
		if (JFactory::getUser()->authorise('core.admin', 'com_redform'))
		{
			JToolBarHelper::preferences('com_redform');
		}
	}
}
?>
