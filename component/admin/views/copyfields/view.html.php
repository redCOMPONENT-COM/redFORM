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
class RedformViewCopyFields extends JView {
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) 
	{
		$app = &Jfactory::getApplication();
		$document = JFactory::getDocument();
		
		$document->setTitle(JText::_('COM_REDFORM_Fields_COPY_TITLE'));
		
		JToolBarHelper::title(JText::_('COM_REDFORM_Fields_COPY_TITLE' ), 'redform_fields');
		JToolBarHelper::apply('docopy');
		JToolBarHelper::back();
				
		$lists = array();
		/* Get the forms */
		$forms = $this->get('FormsOptions');
		
		/* Create the dropdown list */
		$lists['form_id'] = JHTML::_('select.genericlist',  $forms, 'form_id');

		/* Set variables */
		$this->assignRef('fields', $this->get('fields'));
		$this->assignRef('lists',  $lists);

		/* Display the page */
		parent::display($tpl);
	}		
}
?>
