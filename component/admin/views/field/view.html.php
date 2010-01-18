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
class RedformViewField extends JView {
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) 
	{
		$row = $this->get('Data');
		
		if (REDMEMBER_INTEGRATION) {
			$options = array();
			$options[] = JHTML::_('select.option', '', JText::_('Select corresponding redmember field'));
			$rm_options = $this->get('RedmemberFieldsOptions');
			if ($rm_options) {
				$options = array_merge($options, $rm_options);
			}
			$lists['rmfields'] = JHTML::_('select.genericlist', $options, 'redmember_field', 'class="inputbox"', 'value', 'text', $row->redmember_field);
		}

		/* Get the published field */
		$lists['published']= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published);

		/* Get the field validation */
		$lists['validate']= JHTML::_('select.booleanlist',  'validate', 'class="inputbox"', $row->validate);

		/* Get the field validation */
		$lists['unique']= JHTML::_('select.booleanlist',  'unique', 'class="inputbox"', $row->unique);

		/* Get the forms */
		$forms = $this->get('FormsOptions');
		$state = '';
		for ($i = 0; $i < count($forms); $i++) {
			if ($forms[$i]->value == $row->form_id && $forms[$i]->startdate < date('Y-m-d H:i:s', time())) {
				$state = 'disabled';
				break;
			}
		}
		if ($row->form_id > 0) {
			$selected = $row->form_id;
		}
		else {
			$selected = JRequest::getInt('form_id');
		}
		$lists['forms']= JHTML::_('select.genericlist',  $forms, 'form_id', $state, 'value', 'text', $selected) ;
		
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
			array('fieldtype' => 'price', 'fieldname' => JText::_('price')),
			array('fieldtype' => 'info', 'fieldname' => JText::_('info')),
	    array('fieldtype' => 'recipients', 'fieldname' => JText::_('recipients'))
		);
		$lists['fieldtypes']= JHTML::_('select.genericlist',  $fieldtypes, 'fieldtype', '', 'fieldtype', 'fieldname', $row->fieldtype) ;

		/* Get the toolbar */
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

		/* Set variabels */
		$this->assignRef('form_id', $row->form_id);
		$this->assignRef('row', $row);
		$this->assignRef('lists', $lists);
		$this->assignRef('state', $state);

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
