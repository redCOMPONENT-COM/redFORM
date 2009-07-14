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
class RedformControllerConfiguration extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirect templates to templates as this is the standard call */
		$this->registerTask('apply','save');
	}
	
	/**
	 * Fields competition
	 */
	function display() {
		JRequest::setVar('view', 'configuration');
    JRequest::setVar('hidemainmenu', 1);
		
		parent::display();
	}
	
	function save()
	{
		$model = $this->getModel('configuration');
		$model->store();
	
    $task   = JRequest::getVar('task');
    
    switch ($task)
    {
    	case 'apply':
    		$link = 'index.php?option=com_redform&controller=configuration&task=edit';
    		break;

    	default:
    		$link = 'index.php?option=com_redform&view=forms';
    		break;
    }
    
    $this->setRedirect( $link );		
	}
  
  function cancel()
  {    
    $this->setRedirect( 'index.php?option=com_redform&view=forms' );    
  }
}
?>
