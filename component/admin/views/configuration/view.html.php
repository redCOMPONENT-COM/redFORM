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
		
		/* Create options */
		$lists['use_phplist']= JHTML::_('select.booleanlist',  'configuration[use_phplist]', 'class="inputbox"', $configuration['use_phplist']->value);
		$lists['use_ccnewsletter']= JHTML::_('select.booleanlist',  'configuration[use_ccnewsletter]', 'class="inputbox"', $configuration['use_ccnewsletter']->value);
		$lists['use_acajoom']= JHTML::_('select.booleanlist',  'configuration[use_acajoom]', 'class="inputbox"', $configuration['use_acajoom']->value);
		
		/* Set variabels */
		$this->assignRef('configuration', $configuration);
		$this->assignRef('lists', $lists);
		
		/* Get the toolbar */
		$this->toolbar();
		
		/* Display the page */
		parent::display($tpl);
	}
	
	function toolbar() {
		JToolBarHelper::title(JText::_('Configuration'), 'redform_config');
    JToolBarHelper::save();
		JToolBarHelper::apply();
    JToolBarHelper::cancel();
	}
}
?>
