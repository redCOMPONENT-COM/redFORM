<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * Front-end controller
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Front-end Controller
 */
class RedformController extends JController
{
	/**
	 * Method to show a weblinks view
	 *
	 * @access	public
	 */
	function display() {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			JRequest::setVar('view', 'redform' );
			JRequest::setVar('layout', 'redform' );
		}
		return;
		parent::display();
	}
	
}
?>