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
class RedformViewSubmitters extends JView {
	
  function display($tpl = null) 
  {
  	/* Get the forms */  
  	$forms = $this->get('FormsOptions');

  	// we need to chose a form by default, for the database queries (form table names...)
  	$id = JRequest::getVar('form_id', false);
  	if (!$id && isset($forms[0])) {
  		JRequest::setVar('form_id', $forms[0]->value);
  	}
        
  	/* Create the dropdown list */
  	$lists['form_id'] = JHTML::_('select.genericlist',  $forms, 'form_id', '', 'value', 'text', JRequest::getVar('form_id'));

  	/* Get the form name, if one is selected */
  	$form = $this->get('Form');
  	$this->assignRef('form', $form);

  	/* Get the pagination */
  	$pagination = $this->get('Pagination');

  	/* Get the submitters list */
  	$submitters = $this->get('Submitters');

  	/* Get the fields list */
  	$fields = $this->get('Fields');

  	/* Get the event details if there is an xref value */
  	if (JRequest::getInt('xref', false)) {
  		$coursetitle = $this->get('CourseTitle');
  	}
  	else $coursetitle = null;

  	/* Set variabels */
  	$this->assignRef('pagination', $pagination);
  	$this->assignRef('submitters', $submitters);
  	$this->assignRef('lists', $lists);
  	$this->assignRef('fields', $fields);
  	$this->assignRef('coursetitle', $coursetitle);
    $this->assignRef('xref', JRequest::getInt('xref', 0));

  	JToolBarHelper::title(JText::_( 'Submitters' ), 'redform_submitters');
  	if (JRequest::getVar('xref', false)) JToolBarHelper::back();
  	JToolBarHelper::deleteList(JText::_('Are you sure to delete the submitter?'));
  	
  	//TODO: fix the add/modify submitters from backend
  	//JToolBarHelper::editListX();
  	if (JRequest::getVar('xref', false)) JToolBarHelper::addNewX();

  	// set the menu
  	RedformHelper::setMenu();

  	/* Display the page */
  	parent::display($tpl);
  }
  
}
?>
