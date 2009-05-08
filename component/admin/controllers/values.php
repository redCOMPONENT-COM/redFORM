<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * redFORM Controller
 */
class RedformControllerValues extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirect templates to templates as this is the standard call */
		$this->registerTask('save','values');
		$this->registerTask('remove','values');
		$this->registerTask('publish','values');
		$this->registerTask('unpublish','values');
		$this->registerTask('add','edit');
		$this->registerTask('apply','edit');
		$this->registerTask('cancel','values');
		$this->registerTask('saveorder','values');
	}
	
	/**
	 * Fields competition
	 */
	function Values() {
		/* Create the view */
		$view = $this->getView('values', 'html');
					
		/* Add the main model */
		$view->setModel( $this->getModel( 'values', 'RedformModel' ), true );
					
		/* Add extra models */
		$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redform' . DS . 'models' );
		$view->setModel( $this->getModel( 'fields', 'RedformModel' ));
		$view->setModel( $this->getModel( 'redform', 'RedformModel' ));
		
		/* Add the layout */
		$view->setLayout('values');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	 * Editing an value
	 */
	function Edit() {
		/* Create the view */
		$view =& $this->getView('values', 'html');
					
		/* Add the main model */
		$view->setModel( $this->getModel( 'values', 'RedformModel' ), true );
		
		/* Add extra models */
		$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redform' . DS . 'models' );
		$view->setModel( $this->getModel( 'fields', 'RedformModel' ));
		
		/* Hide the main menu */
		JRequest::setVar('hidemainmenu', 1);
		
		/* Add the layout */
		$view->setLayout('editvalue');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	 * Fields competition
	 */
	function CheckFieldType() {
		/* Create the view */
		$view = $this->getView('values', 'raw');
		$view->setModel( $this->getModel( 'values', 'RedformModel' ), true );
					
		/* Display it all */
		$view->display();
	}
	
	/**
	 * Fields competition
	 */
	function GetMailingList() {
		/* Create the view */
		$view = $this->getView('values', 'json');
		$view->setModel( $this->getModel( 'values', 'RedformModel' ), true );
					
		/* Display it all */
		$view->display();
	}
}
?>
