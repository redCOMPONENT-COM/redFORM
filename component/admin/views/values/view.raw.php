<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
class RedformViewValues extends JView {
   
	function display() {
		$fieldtype = $this->get('CheckFieldType');
		
		if (empty($fieldtype)) {
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
			echo JHTML::_('select.genericlist',  $fieldtypes, 'fieldtype', '', 'fieldtype', 'fieldname') ;
		}
		else {
			$type = '<select id="fieldtype" name="fieldtype">';
			$type .= '<option value="'.$fieldtype.'" selected=selected>'.JText::_($fieldtype).'</option>';
			$type .= '</select>';
			echo $type;
		}
   }
}
?>