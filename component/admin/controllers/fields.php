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
class RedformControllerFields extends JController
{
  /**
   * constructor
   *
   */
  function __construct() 
  {
    parent::__construct();
    $this->registerTask('apply','save');
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
    JRequest::setVar( 'view', 'field' );
    JRequest::setVar( 'hidemainmenu', 1 );

    $model  = $this->getModel('field');
    $task   = JRequest::getVar('task');

    $user =& JFactory::getUser();
    // Error if checkedout by another administrator
    if ($model->isCheckedOut( $user->get('id') )) {
      $this->setRedirect( 'index.php?option=com_redform&view=fields', JText::_( 'EDITED BY ANOTHER ADMIN' ) );
    }
    $model->checkout();

    parent::display();
  }
  
  function save()
  {   
    // Check for request forgeries
    JRequest::checkToken() or die( 'Invalid Token' );
    
    $task   = JRequest::getVar('task');

    // Sanitize
    $post = JRequest::get('post');

    $model = $this->getModel('field');

    if ($row = $model->store($post)) {

      switch ($task)
      {
        case 'apply':
          $link = 'index.php?option=com_redform&view=field&hidemainmenu=1&cid[]='.$row->id;
          break;

        default:
          $link = 'index.php?option=com_redform&view=fields';
          break;
      }
      $msg  = JText::_( 'FIELD SAVED');

      $cache = &JFactory::getCache('com_redform');
      $cache->clean();

    } else {
      $msg  = '';
      $link   = 'index.php?option=com_redform&view=fields';
    }

    $model->checkin();
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
    JRequest::checkToken() or die( 'Invalid Token' );
    
    $row = & JTable::getInstance('Fields', 'RedformTable');
    $row->bind(JRequest::get('post'));
    $row->checkin();

    $this->setRedirect( 'index.php?option=com_redform&view=fields' );
  }
   
 /**
   * Logic to publish
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function publish()
  {
    $cid  = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_( 'Select an item to publish' ) );
    }

    $model = $this->getModel('fields');

    if(!$model->publish($cid, 1)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_( 'FIELDS PUBLISHED');

    $this->setRedirect( 'index.php?option=com_redform&view=fields', $msg );
  }

  /**
   * Logic to unpublish
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function unpublish()
  {
    $cid  = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
    }

    $model = $this->getModel('fields');

    if(!$model->publish($cid, 0)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_( 'FIELDS UNPUBLISHED');

    $this->setRedirect( 'index.php?option=com_redform&view=fields', $msg );
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

    $model = $this->getModel('fields');

    if ($model->delete($cid)) {
    	$msg = JText::_('FIELDS DELETED');
    }
    else {    	
      $msg = JText::_('FIELDS DELETION ERROR' . ': ' . $model->getError());
    }

    $cache = &JFactory::getCache('com_redform');
    $cache->clean();

    $this->setRedirect( 'index.php?option=com_redform&view=fields', $msg );
  }
  
  function sanitize()
  {  	
    $model = $this->getModel('fields');
    
    if ($model->sanitize()) {
      $this->setRedirect( 'index.php?option=com_redform&view=fields', JText::_('SANITIZE_COMPLETE'));
    }
    else {
      $this->setRedirect( 'index.php?option=com_redform&view=fields', JText::_('SANITIZE_ERROR'));    	
    }
  }
  
  function saveorder()
  {
    $model = $this->getModel('fields');
    
    if ($model->saveorder()) {
      $this->setRedirect( 'index.php?option=com_redform&view=fields');
    }
    else {
      $this->setRedirect( 'index.php?option=com_redform&view=fields', JText::_('ERROR REORDERING'));      
    }
  	
  }
  
  /**
   * Logic to orderup
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function orderup()
  {
    $model = $this->getModel('fields');
    $model->move(-1);

    $this->setRedirect( 'index.php?option=com_redform&view=fields');
  }

  /**
   * Logic to orderdown
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function orderdown()
  {
    $model = $this->getModel('fields');
    $model->move(1);

    $this->setRedirect( 'index.php?option=com_redform&view=fields');
  }
}
?>
