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

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_redform'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// log helper class
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'log.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'helper.php');

require_once (JPATH_COMPONENT_SITE.DS.'redform.core.php');

// redmember integration
if (file_exists(JPATH_ROOT.DS.'components'.DS.'com_redmember')) {
	define('REDMEMBER_INTEGRATION', true);
}
else {
	define('REDMEMBER_INTEGRATION', false);	
}

/* Load the necessary stylesheet */
$document = JFactory::getDocument();
$document->addStyleSheet( JURI::root().'administrator/components/com_redform/css/redform.css' );

// Set the table directory
JLoader::discover('RedformTable', JPATH_COMPONENT.DS.'tables');

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
$classname	= 'RedformController'.ucfirst($controller);
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();

?>
