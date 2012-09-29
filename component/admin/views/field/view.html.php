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
		$app = &Jfactory::getApplication();		
   	$uri = JFactory::getURI();
   		
		$document	= & JFactory::getDocument();
		JHTML::_('behavior.modal'); 
    $document->addScript(JURI::root().'/administrator/components/com_redform/js/ajaxvalues.js');
		
		$row = $this->get('Data');
		
		if (REDMEMBER_INTEGRATION) 
		{
			$options = array();
			$options[] = JHTML::_('select.option', '', JText::_('COM_REDFORM_Select_corresponding_redmember_field'));
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
		for ($i = 0; $i < count($forms); $i++) 
		{
			if ($forms[$i]->value == $row->form_id && $forms[$i]->startdate < date('Y-m-d H:i:s', time())) {
				$state = 'disabled';
				break;
			}
		}
		$forms = array_merge( array(JHTML::_('select.option', 0, JText::_('COM_REDFORM_FIELD_SELECT_FORM'))),
		                      $forms	 
		                    );
		
		if ($row->form_id > 0) {
			$selected = $row->form_id;
		}
		else {
			$selected = JRequest::getInt('form_id');
		}
		$lists['forms']= JHTML::_('select.genericlist',  $forms, 'form_id', ($state == 'disabled' ? 'disabled="disabled"' : ''), 'value', 'text', $selected) ;
		
		/* Create the value field types */
		$fieldtypes = array(
			array('fieldtype' => 'textfield', 'fieldname' => JText::_('COM_REDFORM_textfield')),
			array('fieldtype' => 'textarea', 'fieldname' => JText::_('COM_REDFORM_textarea')),
			array('fieldtype' => 'radio', 'fieldname' => JText::_('COM_REDFORM_radio')),
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
		$lists['fieldtypes']= JHTML::_('select.genericlist',  $fieldtypes, 'fieldtype', '', 'fieldtype', 'fieldname', $row->fieldtype) ;
		
		/* Get the mailing lists if we have an e-mail field */
		if ($row->fieldtype == 'email') 
		{
			/* Get the mailing lists that can be used */
			$activelists = $this->get('ActiveMailinglists');
			/* Set the id */
			JRequest::setVar('id', $row->id);
			$mailinglist = $this->get('Mailinglist');
			$options = array(JHTML::_('select.option', '', JText::_('COM_REDFORM_FIELD_SELECT_MAILINGLIST_INTEGRATION')));
			foreach ($activelists as $list)
			{
				$options[] = JHTML::_('select.option', $list, $list);
			}
			$lists['mailinglists'] = JHTML::_('select.genericlist', $options, 'mailinglist', '', 'value', 'text', $mailinglist->mailinglist);
			$this->assignRef('mailinglist', $mailinglist);
			$this->assign('displaymailinglist', count($activelists));
		}		
		
		/* Get the toolbar */
		switch (JRequest::getCmd('task')) {
			case 'add':
				JToolBarHelper::title(JText::_('COM_REDFORM_Add_Field' ), 'redform_plus');
				break;
			default:
				JToolBarHelper::title(JText::_('COM_REDFORM_Edit_Field' ), 'redform_plus');
				break;
		}
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();

		/* Set variables */
		$this->assignRef('form_id', $row->form_id);
		$this->assignRef('row', $row);
		$this->assignRef('lists', $lists);
		$this->assignRef('state', $state);
		$this->assignRef('uselists', $uselists);

		/* Display the page */
		parent::display($tpl);
	}		
}
?>
