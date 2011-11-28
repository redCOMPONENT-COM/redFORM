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

		$row = $this->get('Data');

		$editor = &JFactory::getEditor();

		/* Create the published field */
		$lists['published']= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published) ;

		/* Get the fields */
		$fields = $this->get('FieldsOptions');
		$lists['fields']= JHTML::_('select.genericlist',  $fields, 'field_id', '', 'value', 'text', $row->field_id) ;

		/* Set variabels */
		$this->assignRef('row', $row);
		$this->assignRef('lists', $lists);
		$this->assignRef('editor', $editor);

		/* Get the toolbar */
		switch (JRequest::getCmd('task')) {
			case 'add':
				JToolBarHelper::title(JText::_('COM_REDFORM_Add_Value' ), 'redform_plus');
				break;
			case 'ajaxedit':
				$this->setLayout('ajax');
				$this->assign('field_id', JRequest::getInt('fieldid'));
				break;
			default:
				JToolBarHelper::title(JText::_('COM_REDFORM_Edit_Value' ), 'redform_plus');
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