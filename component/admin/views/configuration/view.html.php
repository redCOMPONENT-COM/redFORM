<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * redFORM view
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
