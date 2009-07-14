<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * redFORM view
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
  	JToolBarHelper::editListX();
  	if (JRequest::getVar('xref', false)) JToolBarHelper::addNewX();

  	// set the menu
  	RedformHelper::setMenu();

  	/* Display the page */
  	parent::display($tpl);
  }
  
  /**
	 * redFORM view display method
	 * @return void
	 **/
	function _old_display($tpl = null) {
		/* Get the task */
		$task = JRequest::getCmd('task');
		
		switch ($task) {
			case 'edit':
				$submitter = $this->get('Submitter');
				JRequest::setVar('answers', $submitter);
				JRequest::setVar('submit_key', $submitter[0]->submit_key);
				JRequest::setVar('xref', $submitter[0]->xref);
				JRequest::setVar('redform_edit', true);
				JRequest::setVar('submitter_id', $submitter[0]->id);
				$this->assignRef('submitter', $submitter);
				JToolBarHelper::title(JText::_( 'EDIT_SUBMITTER' ), 'redform_submitters');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				break;
			case 'add':
				JRequest::setVar('redform_add', true);
				JRequest::setVar('close_form', false);
				JToolBarHelper::title(JText::_( 'NEW_SUBMITTER' ), 'redform_submitters');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				break;
			default:
				if ($task == 'remove') {
					$this->get('RemoveSubmitter');
					JRequest::SetVar('cid', array());
				}
				else if ($task == 'save') {
					$this->get('SaveSubmitter');
				}
				
				/* Get the forms */
				$forms = $this->get('Forms', 'redform');
				
				/* Create the dropdown list */
				$lists['form_id'] = JHTML::_('select.genericlist',  $forms, 'form_id', '', 'id', 'formname', JRequest::getVar('form_id')) ;
				
				$id = JRequest::getVar('form_id', false);
				if (!$id && isset($forms[0])) {
					JRequest::setVar('form_id', $forms[0]->id);
				}
				
				/* Get the form name */
				$form = $this->get('Form', 'redform');
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
				else $coursetitle = '';
				
				/* Set variabels */
				$this->assignRef('pagination', $pagination);
				$this->assignRef('submitters', $submitters);
				$this->assignRef('lists', $lists);
				$this->assignRef('fields', $fields);
				$this->assignRef('coursetitle', $coursetitle);
				
				JToolBarHelper::title(JText::_( 'Submitters' ), 'redform_submitters');
				if (JRequest::getVar('xref', false)) JToolBarHelper::back();
				JToolBarHelper::deleteList(JText::_('Are you sure to delete the submitter?'));
				JToolBarHelper::editListX();
				if (JRequest::getVar('xref', false)) JToolBarHelper::addNewX();
				
			break;
		}
		
		/* Display the page */
		parent::display($tpl);
	}
}
?>
