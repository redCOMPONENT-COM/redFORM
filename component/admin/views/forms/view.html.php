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
class RedformViewForms extends JView {
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) 
	{
		// set the menu
		RedformHelper::setMenu();
		
		/* Get the competitions list */
		$forms = $this->get('Forms');

		/* Get the pagination */
		$pagination = & $this->get('Pagination');
		/* Get the number of contestantst */
		$submitters = $this->get('CountSubmitters');

		/* Set variabels */
		$this->assignRef('pagination',   $pagination);
		$this->assignRef('forms',   $forms);
		$this->assignRef('submitters',   $submitters);
				
		/* Get the toolbar */
		$this->toolbar();
		
		/* Display the page */
		parent::display($tpl);
	}
	
	function toolbar() 
	{
		JToolBarHelper::title(JText::_( 'redFORM' ), 'redform_redform');
		JToolBarHelper::custom('details', 'redform_details', 'redform_details', JText::_('Details'), true);
		JToolBarHelper::custom('submitters', 'redform_submitters', 'redform_submitters', JText::_('Submitters'), true);
		JToolBarHelper::spacer();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList(JText::_('Are you sure you want to delete the form and all related fields, values and submitter data?'));
		JToolBarHelper::editListX();
		JToolBarHelper::addNew();
    JToolBarHelper::custom('copy', 'copy', 'copy', JText::_('Clone'), true);
	}
}
?>
