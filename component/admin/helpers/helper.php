<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 */

class RedformHelper {
	
  function setMenu()
  {
    $user = & JFactory::getUser();
    $view = JRequest::getVar('view', '');
    $controller = JRequest::getVar('controller', '');
    //Create Submenu
    JSubMenuHelper::addEntry( JText::_( 'FORMS' ), 'index.php?option=com_redform&view=forms', $view == '' || $view == 'forms');
    JSubMenuHelper::addEntry( JText::_( 'FIELDS' ), 'index.php?option=com_redform&view=fields', $view == 'fields');
    JSubMenuHelper::addEntry( JText::_( 'VALUES' ), 'index.php?option=com_redform&view=values', $view == 'values');
    JSubMenuHelper::addEntry( JText::_( 'SUBMITTERS' ), 'index.php?option=com_redform&view=submitters', $view == 'submitters');
    JSubMenuHelper::addEntry( JText::_( 'LOGS' ), 'index.php?option=com_redform&view=log', $view == 'log');
    if ($user->get('gid') > 24) {
      JSubMenuHelper::addEntry( JText::_( 'SETTINGS' ), 'index.php?option=com_redform&controller=configuration&task=edit', $controller == 'configuration');
    }
  }
}

?>