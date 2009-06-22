<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * redFORM component
 */

/* No direct access */
defined('_JEXEC') or die('Restricted access');

// log helper class
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'log.php');

/* Load the necessary stylesheet */
$document = JFactory::getDocument();
$document->addStyleSheet( JURI::root().'administrator/components/com_redform/css/redform.css' );


// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');
$controller = JRequest::getVar('controller', 'redform');
// Require specific controller if requested
if($controller) {
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
}

// Create the controller
$classname	= 'RedformController'.$controller;
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar('task', 'redform'));

// Redirect if set by the controller
$controller->redirect();

?>
