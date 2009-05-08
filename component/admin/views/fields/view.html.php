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
class RedformViewFields extends JView {
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) {
		global $mainframe;
		/* Get the task */
		$task = JRequest::getCmd('task');
		
		/* Check to see if we have a form name */
		if (strlen(trim(JRequest::getVar('field'))) == 0 && ($task == 'apply' || $task == 'save')) {
			$mainframe->redirect('index.php?option=com_redform&controller=fields&task=add', JText::_('No field name specified'), 'error');
		}
		
		switch ($task) {
			case 'apply':
			case 'edit':
			case 'add':
				if ($task == 'apply') $row = $this->get('SaveField');
				else $row = $this->get('Field');
				
				/* Get the published field */
				$lists['published']= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published);
				
				/* Get the field validation */
				$lists['validate']= JHTML::_('select.booleanlist',  'validate', 'class="inputbox"', $row->validate);
				
				/* Get the field validation */
				$lists['unique']= JHTML::_('select.booleanlist',  'unique', 'class="inputbox"', $row->unique);
				
				/* Get the forms */
				$forms = $this->get('Forms', 'redform');
				$state = '';
				for ($i = 0; $i < count($forms); $i++) {
					if ($forms[$i]->id == $row->form_id && $forms[$i]->startdate < date('Y-m-d H:i:s', time())) {
						$state = 'disabled';
						$i = count($forms) + 1;
					}
				}
				if ($row->form_id > 0) $selected = $row->form_id;
				else $selected = JRequest::getInt('form_id');
				$lists['forms']= JHTML::_('select.genericlist',  $forms, 'form_id', $state, 'id', 'formname', $selected) ;
				
				/* Set variabels */
				$this->assignRef('form_id', $row->form_id);
				$this->assignRef('row', $row);
				$this->assignRef('lists', $lists);
				$this->assignRef('state', $state);
				
				break;
			default:
				switch($task) {
					case 'save':
						$this->get('SaveField');
						break;
					case 'saveorder':
						$this->get('SaveOrder');
						break;
					case 'remove':
						$this->get('RemoveField');
						break;
					case 'publish':
					case 'unpublish':
						$this->get('Publish');
					break;

					case 'sanitize':
						$this->get('Sanitize');
						break;
				}
				/* Get the pagination */
				$pagination = $this->get('Pagination');
				
				/* Get the fields list */
				$fields = $this->get('Fields');
				
				/* Get the forms */
				$forms = $this->get('Forms', 'redform');
				if (!is_array($forms)) $forms = array();
				$form = new stdClass();
				$form->id = 0;
				$form->formname = JText::_('All');
				array_unshift($forms, $form);
				
				/* Create the dropdown list */
				$lists['form_id'] = JHTML::_('select.genericlist',  $forms, 'form_id', '', 'id', 'formname', JRequest::getVar('form_id')) ;
				
				/* Check if there are any forms */
				$countforms = $this->get('Total', 'redform');
				
				/* Set variabels */
				$this->assignRef('pagination', $pagination);
				$this->assignRef('fields', $fields);
				$this->assignRef('lists', $lists);
				$this->assignRef('countforms', $countforms);
				
				break;
		}
		/* Get the toolbar */
		$this->toolbar();
		
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
