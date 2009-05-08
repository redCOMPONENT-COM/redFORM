<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * redFORM Controller
 */
class RedformControllerSubmitters extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirect templates to templates as this is the standard call */
		$this->registerTask('remove','submitters');
		$this->registerTask('cancel','submitters');
		$this->registerTask('save','submitters');
		$this->registerTask('add','edit');
	}
	
	/**
	 * Submitters
	 */
	function Submitters() {
		$view = $this->getView('submitters', 'html');
		$view->setModel( $this->getModel( 'submitters', 'RedformModel' ), true );
		$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redform' . DS . 'models' );
		$view->setModel( $this->getModel( 'redform', 'RedformModel' ));
		$view->setLayout('submitters');
		$view->display();
	}
	
	/**
	 * Export submitters data
	 */
	function Export() {
		$view = $this->getView('submitters', 'raw');
		$view->setModel( $this->getModel( 'submitters', 'RedformModel' ), true );
		$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redform' . DS . 'models' );
		$view->setModel( $this->getModel( 'redform', 'RedformModel' ));
		$view->setLayout('submitters_export');
		$view->display();
	}
	
	/**
	 * Edit a submission
	 */
	function Edit() {
		/* Hide the mainmenu so the user must save or cancel the template settings */
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view', 'submitters');
		JRequest::setVar('layout', 'editsubmitter');
		parent::display();
	}
	
	
	/**
	 * Redirect back to redEVENT
	 */
	public function RedEvent() {
		global $mainframe;
		$mainframe->redirect('index.php?option=com_redevent&view=attendees&xref='.JRequest::getInt('xref'));
	}
}
?>
