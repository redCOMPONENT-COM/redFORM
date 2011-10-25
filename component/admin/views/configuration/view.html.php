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
class RedformViewConfiguration extends JView {
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) {
				
		/* Get the configuration */
		$configuration = $this->get('Configuration');
		
		/* Set variabels */
		$this->assignRef('configuration', $configuration);
		$this->assignRef('lists', $lists);
		
		/* Get the toolbar */
		$this->toolbar();
				
		//Get global parameters
		$table =& JTable::getInstance('component');
		$table->loadByOption( 'com_redform' );
		$globalparams = new JParameter( $table->params, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redform'.DS.'config.xml' );
		
		$params = JComponentHelper::getParams('com_redform');
		
		$this->assignref('params' ,$globalparams);
		
		/* Display the page */
		parent::display($tpl);
	}
	
	function toolbar() {
		JToolBarHelper::title(JText::_('COM_REDFORM_Configuration'), 'redform_config');
    JToolBarHelper::save();
		JToolBarHelper::apply();
    JToolBarHelper::cancel();
	}
}
?>
