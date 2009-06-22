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
class RedformControllerRedform extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirect templates to templates as this is the standard call */
		$this->registerTask('save','redform');
		$this->registerTask('remove','redform');
		$this->registerTask('publish','redform');
		$this->registerTask('unpublish','redform');
		$this->registerTask('cancel','redform');
		$this->registerTask('apply','edit');
	}
	
	/**
	 * Gets a list of IP/IP ranges in the database
	 */
	function Redform() {
		JRequest::setVar('view', 'redform');
		JRequest::setVar('layout', 'redform');
		
		parent::display();
	}
	
	/**
	 * Editing a competition
	 */
	function Edit() {
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view', 'redform');
		JRequest::setVar('layout', 'editform');
		
		parent::display();
	}
	
	/**
	 * Adding a competition
	 */
	function Add() {
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view', 'redform');
		JRequest::setVar('layout', 'editform');
		
		parent::display();
	}
	
	/**
	 * Editing configuration
	 */
	function Configuration() {
		JRequest::setVar('view', 'configuration');
		JRequest::setVar('layout', 'configuration');
		
		parent::display();
	}
	
	/**
	 * Details competition
	 */
	function Details() {
		JRequest::setVar('hidemainmenu', 1);
		
		$view =& $this->getView('redform', 'html');
		$view->setModel( $this->getModel( 'redform', 'RedformModel' ), true );
		$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redform' . DS . 'models' );
		$view->setModel( $this->getModel( 'submitters', 'RedformModel' ));
		$view->setLayout('detailsform');
		$view->display();
	}
	
	/**
	 * Fields competition
	 */
	function Fields() {
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view', 'fields');
		JRequest::setVar('layout', 'fields');
		
		parent::display();
	}
	
	/**
	 * List of submitters
	 */
	function Submitters() {
		$view =& $this->getView('submitters', 'html');
		$view->setModel( $this->getModel( 'submitters', 'RedformModel' ), true );
		$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redform' . DS . 'models' );
		$view->setModel( $this->getModel( 'redform', 'RedformModel' ));
		$view->setLayout('submitters');
		$view->display();
	}	

  /**
   * Clears log file
   *
   */
  function Log()
  {
    JRequest::setVar('view', 'log');
    parent::display();
  }
	
  /**
   * Clears log file
   *
   */
  function clearlog()
  {
    RedFormHelperLog::clear();
    $msg = JText::_('LOG CLEARED');
    $this->setRedirect('index.php?option=com_redform&task=log', $msg);
    $this->redirect();
  }
}
?>
