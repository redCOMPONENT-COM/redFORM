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
class RedformViewValue extends JView 
{
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		$mainframe = & JFactory::getApplication();
		 
		$document = JFactory::getDocument();
		$document->addScript($mainframe->getSiteURL().'administrator/components/com_redform/js/javascript.js');
		$document->addScript($mainframe->getSiteURL().'administrator/components/com_redform/js/jquery.js');
		$document->addScriptDeclaration('jQuery.noConflict();');

		$row = $this->get('Data');

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
		array('fieldtype' => 'info', 'fieldname' => JText::_('info'))
		);
		$lists['fieldtypes']= JHTML::_('select.genericlist',  $fieldtypes, 'fieldtype', '', 'fieldtype', 'fieldname', $row->fieldtype) ;

		$editor = &JFactory::getEditor();

		/* Create the published field */
		$lists['published']= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published) ;

		/* Get the fields */
		$fields = $this->get('FieldsOptions');
		$lists['fields']= JHTML::_('select.genericlist',  $fields, 'field_id', 'onChange="CheckFieldType(); return false;"', 'value', 'text', $row->field_id) ;

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

		/* Get the toolbar */
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

		/* Display the page */
		parent::display($tpl);
	}

}
?>