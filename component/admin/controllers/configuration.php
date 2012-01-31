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
		
		if (!$model->store())
		{
			$msg = implode('<br/>',  $model->getErrors());
			$msgtype = 'error';
		}
		else
		{
			$msg = implode('<br/>',  $model->getErrors());
			$msgtype = 'message';			
		}
	
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
    
    $this->setRedirect( $link, $msg, $msgtype );		
	}
  
  function cancel()
  {    
    $this->setRedirect( 'index.php?option=com_redform&view=forms' );    
  }
}
