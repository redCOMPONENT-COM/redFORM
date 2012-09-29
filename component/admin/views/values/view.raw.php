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
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
class RedformViewValues extends JView {
   
	function display() {
		$fieldtype = $this->get('CheckFieldType');
		
		if (empty($fieldtype)) {
			$fieldtypes = array(
				array('fieldtype' => 'radio', 'fieldname' => JText::_('COM_REDFORM_radio')), 
				array('fieldtype' => 'textarea', 'fieldname' => JText::_('COM_REDFORM_textarea')),
				array('fieldtype' => 'textfield', 'fieldname' => JText::_('COM_REDFORM_textfield')),
				array('fieldtype' => 'checkbox', 'fieldname' => JText::_('COM_REDFORM_checkbox')),
				array('fieldtype' => 'email', 'fieldname' => JText::_('COM_REDFORM_email')),
				array('fieldtype' => 'username', 'fieldname' => JText::_('COM_REDFORM_username')),
				array('fieldtype' => 'fullname', 'fieldname' => JText::_('COM_REDFORM_fullname')),
				array('fieldtype' => 'select', 'fieldname' => JText::_('COM_REDFORM_select')),
				array('fieldtype' => 'multiselect', 'fieldname' => JText::_('COM_REDFORM_multiselect')),
				array('fieldtype' => 'fileupload', 'fieldname' => JText::_('COM_REDFORM_fileupload')),
        array('fieldtype' => 'wysiwyg', 'fieldname' => JText::_('COM_REDFORM_wysiwyg')),
				array('fieldtype' => 'price', 'fieldname' => JText::_('COM_REDFORM_price')),
        array('fieldtype' => 'info', 'fieldname' => JText::_('COM_REDFORM_info')),
        array('fieldtype' => 'recipients', 'fieldname' => JText::_('COM_REDFORM_recipients')),
	      array('fieldtype' => 'date', 'fieldname' => JText::_('COM_REDFORM_date')),
	      array('fieldtype' => 'hidden', 'fieldname' => JText::_('COM_REDFORM_hiddenfield')),
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