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
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'helper.php');

/* Load the necessary stylesheet */
$document = JFactory::getDocument();
$document->addStyleSheet( JURI::root().'administrator/components/com_redform/css/redform.css' );

// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested
if( $controller = JRequest::getWord('controller') ) {
  $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
  if (file_exists($path)) {
    require_once $path;
  } else {
    $controller = '';
  }
}

// Create the controller
$classname	= 'RedformController'.$controller;
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();

?>
