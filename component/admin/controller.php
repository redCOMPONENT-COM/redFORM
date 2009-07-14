<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * redFORM default controller
 */

jimport('joomla.application.component.controller');

/**
 * redFORM Component Controller
 */
class RedformController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
  function display()
  {
    // set a default view
    if (JRequest::getVar('view', '') == '') {
      JRequest::setVar('view', 'forms');    
    }
    parent::display();
  }
  
  /**
   * Clears log file
   *
   */
  function clearlog()
  {
    RedformHelperLog::clear();
    $msg = JText::_('LOG CLEARED');
    $this->setRedirect('index.php?option=com_redform&view=log', $msg);
    $this->redirect();
  }
}
?>
