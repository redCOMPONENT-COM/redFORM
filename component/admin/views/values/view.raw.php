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