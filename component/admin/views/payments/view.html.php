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

jimport( 'joomla.application.component.view' );

/**
 * redFORM View
 */
class RedformViewPayments extends JView {
	
  function display($tpl = null) 
  {
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar('option');
		
		$user 		= & JFactory::getUser();
		$document	= & JFactory::getDocument();		
  	$params   = JComponentHelper::getParams('com_redform');
  	
  	$rows       = $this->get('Data');
  	$pagination = $this->get('Pagination');
  	
  	$lists = array();

  	/* Set variabels */
  	$this->assignRef('rows',        $rows);
  	$this->assignRef('pagination',  $pagination);
  	$this->assignRef('lists',       $lists);
    $this->assignRef('params',      $params);
    $this->assignRef('key',         JRequest::getVar('submit_key'));

  	JToolBarHelper::title(JText::_( 'COM_REDFORM_PAYMENTS_HISTORY' ), 'redform_submitters');
  	JToolBarHelper::addNew();
  	JToolBarHelper::editListX();
  	JToolBarHelper::deleteListX();
		JToolBarHelper::custom('back', 'back', 'back', 'back', false);
  	  	
  	// set the menu
  	RedformHelper::setMenu();

  	/* Display the page */
  	parent::display($tpl);
  }
  
}
?>
