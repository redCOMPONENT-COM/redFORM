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

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the redform component
 *
 * @static
 * @package		redform
 * @since 2.0
 */
class RedformViewPayment extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$object	=& $this->get('data');

		if (empty($object->gateway) && $this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		JToolBarHelper::title( JText::_('COM_REDFORM_Payment_history' ) );
		JToolBarHelper::back();

		//get the object
		$object =& $this->get('data');
		
		$this->assignRef('object',		$object);
		
		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		global $mainframe, $option;
		
		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();
		
    $document = & JFactory::getDocument();

		$lists = array();
		//get the project
		$object	=& $this->get('data');
		$isNew  = ($object->id < 1);
		 
		$this->assignRef('lists',      $lists);
		$this->assignRef('object',     $object);
		$this->assignRef('submit_key', JRequest::getVar('submit_key'));

		parent::display($tpl);
	}
}
?>
