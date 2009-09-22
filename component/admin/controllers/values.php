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
class RedformControllerValues extends JController
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

    $this->setRedirect( 'index.php?option=com_redform&view=value' );
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
    JRequest::setVar( 'view', 'value' );
    JRequest::setVar( 'hidemainmenu', 1 );

    $model  = $this->getModel('value');
    $task   = JRequest::getVar('task');

    $user =& JFactory::getUser();
    // Error if checkedout by another administrator
    if ($model->isCheckedOut( $user->get('id') )) {
      $this->setRedirect( 'index.php?option=com_redform&view=values', JText::_( 'EDITED BY ANOTHER ADMIN' ) );
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
    if ($post['fieldtype'] == 'info') {
      $post['value'] = JRequest::getVar('value', '', 'post', 'string', JREQUEST_ALLOWHTML);
    }

    $model = $this->getModel('value');

    if ($row = $model->store($post)) {

      switch ($task)
      {
        case 'apply':
          $link = 'index.php?option=com_redform&view=value&hidemainmenu=1&cid[]='.$row->id;
          break;

        default:
          $link = 'index.php?option=com_redform&view=values';
          break;
      }
      $msg  = JText::_( 'VALUE SAVED');

      $cache = &JFactory::getCache('com_redform');
      $cache->clean();

    } else {
      $msg  = '';
      $link   = 'index.php?option=com_redform&view=values';
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
    
    $row = & JTable::getInstance('values', 'Table');
    $row->bind(JRequest::get('post'));
    $row->checkin();

    $this->setRedirect( 'index.php?option=com_redform&view=values' );
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

    $model = $this->getModel('values');

    if(!$model->publish($cid, 1)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_( 'VALUES PUBLISHED');

    $this->setRedirect( 'index.php?option=com_redform&view=values', $msg );
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

    $model = $this->getModel('values');

    if(!$model->publish($cid, 0)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_( 'VALUES UNPUBLISHED');

    $this->setRedirect( 'index.php?option=com_redform&view=values', $msg );
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
    $cid    = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_( 'Select an item to delete' ) );
    }

    $model = $this->getModel('values');

    if ($model->delete($cid)) {
      $msg = JText::_('VALUES DELETED');
    }
    else {
      $msg = JText::_('VALUES DELETION ERROR' . ': ' . $model->getError());
    }

    $cache = &JFactory::getCache('com_redform');
    $cache->clean();

    $this->setRedirect( 'index.php?option=com_redform&view=values', $msg );
  }
  
  /**
   * returns options for forms selecto list
   *
   * @return array
   */
  function getFormsOptions()
  {
    $query = "SELECT id AS value, formname AS text FROM #__rwf_forms";
    $this->_db->setQuery($query);
    return $this->_db->loadObjectList();
  }
	
	/**
	 * Fields competition
	 */
	function CheckFieldType() {
		/* Create the view */
		$view = $this->getView('values', 'raw');
		$view->setModel( $this->getModel( 'values', 'RedformModel' ), true );
					
		/* Display it all */
		$view->display();
	}
	
	/**
	 * Fields competition
	 */
	function GetMailingList() {
		/* Create the view */
		$view = $this->getView('values', 'json');
		$view->setModel( $this->getModel( 'values', 'RedformModel' ), true );
					
		/* Display it all */
		$view->display();
	}
}
?>
