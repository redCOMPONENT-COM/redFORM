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
		array_unshift($forms, JHTML::_('select.option', 0, JText::_('All')));
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
		JToolBarHelper::title(JText::_( 'Values' ), 'redform_values');
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
	
/**
   * redFORM view display method
   * @return void
   **/
  function _old_display($tpl = null) 
  {
    $mainframe = & JFactory::getApplication();
    
    /* Get the task */
    $task = JRequest::getCmd('task');
    
    /* Check to see if we have a form name */
    if (strlen(trim(JRequest::getVar('value'))) == 0 && ($task == 'apply' || $task == 'save')) {
      $mainframe->redirect('index.php?option=com_redform&controller=values&task=add', JText::_('No value name specified'), 'error');
    }
    
    switch ($task) {
      case 'apply':
      case 'edit':
      case 'add':
        $document = JFactory::getDocument();
        $document->addScript($mainframe->getSiteURL().'administrator/components/com_redform/js/javascript.js');
        $document->addScript($mainframe->getSiteURL().'administrator/components/com_redform/js/jquery.js');
        $document->addScriptDeclaration('jQuery.noConflict();');
        
        /* Save the information in case of apply */
        if ($task == 'apply') $row = $this->get('SaveValue');
        else $row = $this->get('Value');
        
        /* Create the value field types */
        $fieldtypes = array(
            array('fieldtype' => 'radio', 'fieldname' => JText::_('radio')), 
            array('fieldtype' => 'textarea', 'fieldname' => JText::_('textarea')),
            array('fieldtype' => 'textfield', 'fieldname' => JText::_('textfield')),
            array('fieldtype' => 'checkbox', 'fieldname' => JText::_('checkbox')),
            array('fieldtype' => 'email', 'fieldname' => JText::_('email')),
            array('fieldtype' => 'username', 'fieldname' => JText::_('username')),
            array('fieldtype' => 'fullname', 'fieldname' => JText::_('fullname')),
            array('fieldtype' => 'select', 'fieldname' => JText::_('select')),
            array('fieldtype' => 'multiselect', 'fieldname' => JText::_('multiselect')),
            array('fieldtype' => 'fileupload', 'fieldname' => JText::_('fileupload')),
            array('fieldtype' => 'wysiwyg', 'fieldname' => JText::_('wysiwyg')),
            array('fieldtype' => 'info', 'fieldname' => JText::_('info')),
            array('fieldtype' => 'recipients', 'fieldname' => JText::_('recipients'))
            );
        $lists['fieldtypes']= JHTML::_('select.genericlist',  $fieldtypes, 'fieldtype', '', 'fieldtype', 'fieldname', $row->fieldtype) ;
        
        $editor = &JFactory::getEditor();
        
        /* Create the published field */
        $lists['published']= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published) ;
        
        /* Get the fields */
        $fields = $this->get('Fields', 'fields');
        $lists['fields']= JHTML::_('select.genericlist',  $fields, 'field_id', 'onChange="CheckFieldType(); return false;"', 'id', 'fieldname', $row->field_id) ;
        
        /* Get the mailing lists that can be used */
        $uselists = $this->get('UseMailinglists');
        
        /* Get the mailing lists if we have an e-mail field */
        if ($row->fieldtype == 'email') {
          /* Set the id */
          JRequest::setVar('id', $row->id);
          $mailinglists = $this->get('Mailinglists');
          $this->assignRef('mailinglists', $mailinglists);
        }
        
        /* Set variabels */
        $this->assignRef('row', $row);
        $this->assignRef('lists', $lists);
        $this->assignRef('uselists', $uselists);
        $this->assignRef('editor', $editor);
        break;
      default:
        switch($task) {
          case 'save':
            $this->get('SaveValue');
            break;
          case 'saveorder':
            $this->get('SaveOrder');
            break;
          case 'remove':
            $this->get('RemoveValue');
            break;
          case 'publish':
          case 'unpublish':
            $this->get('Publish');
          break;
        }
        /* Get the pagination */
        $pagination = $this->get('Pagination');
        
        /* Get the values list */
        $values = $this->get('Values');
        
        /* Check if there are any forms */
        $fields = $this->get('Total', 'fields');
        
        /* Get the forms */
        $forms = $this->get('Forms', 'redform');
        if (!is_array($forms)) $forms = array();
        $form = new stdClass();
        $form->id = 0;
        $form->formname = JText::_('All');
        array_unshift($forms, $form);
        
        /* Create the dropdown list */
        $lists['form_id'] = JHTML::_('select.genericlist',  $forms, 'form_id', '', 'id', 'formname', JRequest::getVar('form_id', 0)) ;
        
        /* Set variabels */
        $this->assignRef('pagination', $pagination);
        $this->assignRef('values', $values);
        $this->assignRef('fields', $fields);
        $this->assignRef('lists', $lists);
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
						JToolBarHelper::title(JText::_( 'Add Value' ), 'redform_plus');
						break;
					default:
						JToolBarHelper::title(JText::_( 'Edit Value' ), 'redform_plus');
						break;
				}
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				break;
			default:
				JToolBarHelper::title(JText::_( 'Values' ), 'redform_values');
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