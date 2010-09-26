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
class RedformControllerPayments extends JController
{
  /**
   * constructor
   *
   */
  function __construct() 
  {
    parent::__construct();
    $this->registerTask('apply', 'save');
    $this->registerTask('add',   'edit');
  }
		
  /**
   * logic to create the new event screen
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function add( )
  {
    global $option;

    $this->setRedirect( 'index.php?option=com_redform&view=field' );
  }
  
  /**
   * logic to create the edit element screen
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function edit( )
  {
		// Set the view and the model
		$view = 'payment';
		$view = & $this->getView( $view, 'html' );
		
		$model = & $this->getModel( 'payment' );
		$object  = $model->getData();
  
		if (empty($object->gateway)) {
			$layout = JRequest::getVar('layout', 'form');
		} 
		else {
			$layout = JRequest::getVar('layout', 'default');
		}
		
		$view->setModel( $model, true );
		
		$view->setLayout( $layout );
		
		// Display the view
		$view->display();		
  }
  
  function save()
  {   
    // Check for request forgeries
    JRequest::checkToken() or die( 'Invalid Token' );
    
    $task   = JRequest::getVar('task');

    // Sanitize
    $post = JRequest::get('post');

    $model = $this->getModel('payment');
    
    if ($row = $model->store($post)) {

      switch ($task)
      {
        case 'apply':
          $link = 'index.php?option=com_redform&controller=payments&task=edit&cid[]='.$row->id;
          break;

        default:
          $link = 'index.php?option=com_redform&view=payments&submit_key='.$row->submit_key;
        	break;
      }
      $msg  = JText::_( 'PAYMENT SAVED');

      $cache = &JFactory::getCache('com_redform');
      $cache->clean();

    } else {
      $msg  = JText::_('Error').': '.$model->getError();
			$link = 'index.php?option=com_redform&view=payments&submit_key='.$row->submit_key;
    }

    $this->setRedirect( $link, $msg );
  }
  
  /**
   * logic for cancel an action
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function cancel()
  {
    // Check for request forgeries
//    JRequest::checkToken() or die( 'Invalid Token' );
    
    $key = JRequest::getVar('submit_key');

		$link = 'index.php?option=com_redform&view=payments&submit_key='.$key;
		$msg = JText::_('Action cancelled');
    $this->setRedirect( $link, $msg );
	}
  
  /**
   * logic for cancel an action
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function back()
  {
    // Check for request forgeries
//    JRequest::checkToken() or die( 'Invalid Token' );
    
    $model = $this->getModel('payments');
    $formid = $model->getFormId();

		$link = 'index.php?option=com_redform&view=submitters&form_id='.$formid;
    $this->setRedirect( $link, $msg );
	}
   
  /**
   * Logic to delete element
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function remove()
  {
    global $option;

    $cid    = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_( 'Select an item to delete' ) );
    }

    $model = $this->getModel('payment');

    if ($model->delete($cid)) {
    	$msg = JText::_('PAYMENT DELETED');
    }
    else {    	
      $msg = JText::_('PAYMENT DELETION ERROR' . ': ' . $model->getError());
    }

    $cache = &JFactory::getCache('com_redform');
    $cache->clean();

    $key = JRequest::getVar('submit_key');

		$link = 'index.php?option=com_redform&view=payments&submit_key='.$key;
		
    $this->setRedirect( $link, $msg );
  }
}
?>
