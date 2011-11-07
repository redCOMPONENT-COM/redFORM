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
    $option = JRequest::getCmd('option');

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
      $this->setRedirect( 'index.php?option=com_redform&view=fields', JText::_('COM_REDFORM_EDITED_BY_ANOTHER_ADMIN' ) );
    }
    $model->checkout();

    parent::display();
  }
  
  /**
   * save a field
   */
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
      $msg  = JText::_('COM_REDFORM_FIELD_SAVED');

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
      JError::raiseError(500, JText::_('COM_REDFORM_Select_an_item_to_publish' ) );
    }

    $model = $this->getModel('fields');

    if(!$model->publish($cid, 1)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_('COM_REDFORM_FIELDS_PUBLISHED');

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
      JError::raiseError(500, JText::_('COM_REDFORM_Select_an_item_to_unpublish' ) );
    }

    $model = $this->getModel('fields');

    if(!$model->publish($cid, 0)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_('COM_REDFORM_FIELDS_UNPUBLISHED');

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
    $option = JRequest::getCmd('option');

    $cid    = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_('COM_REDFORM_Select_an_item_to_delete' ) );
    }

    $model = $this->getModel('fields');

    if ($model->delete($cid)) {
    	$msg = JText::_('COM_REDFORM_FIELDS_DELETED');
    }
    else {    	
      $msg = JText::_('COM_REDFORM_FIELDS_DELETION_ERROR' . ': ' . $model->getError());
    }

    $cache = &JFactory::getCache('com_redform');
    $cache->clean();

    $this->setRedirect( 'index.php?option=com_redform&view=fields', $msg );
  }
  
  function sanitize()
  {  	
    $model = $this->getModel('fields');
    
    if ($model->sanitize()) {
      $this->setRedirect( 'index.php?option=com_redform&view=fields', JText::_('COM_REDFORM_SANITIZE_COMPLETE'));
    }
    else {
      $this->setRedirect( 'index.php?option=com_redform&view=fields', JText::_('COM_REDFORM_SANITIZE_ERROR'));    	
    }
  }
  
  function saveorder()
  {
    $model = $this->getModel('fields');
    
    if ($model->saveorder()) {
      $this->setRedirect( 'index.php?option=com_redform&view=fields');
    }
    else {
      $this->setRedirect( 'index.php?option=com_redform&view=fields', JText::_('COM_REDFORM_ERROR_REORDERING'));      
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
    
	/**
	* copy fields screen
	*/
	function copy()
	{
		$cids = JRequest::getVar('cid', null, 'request', 'array');
		JArrayHelper::toInteger($cids);
		 
		$model = $this->getModel('copyfields');
		$model->setCids($cids);

		$view  = $this->getView('copyfields', 'html');
		$view->setModel($model, true);
		$view->assign('cids', $cids);
		 
		$view->display();
	}
  
  /**
  * copy fields
  */
  function docopy()
  {
		$cids = JRequest::getVar('cids', null, 'request', 'string');
		$cids = explode(',', $cids);
		JArrayHelper::toInteger($cids);
		
		$form_id = JRequest::getInt('form_id');
  	
		$msg = '';
		$msgtype = 'message';
		
  	$model = $this->getModel('field');
  	if ($res = $model->copy($cids, $form_id)) {
  		$msg = JText::_('COM_REDFORM_FIELDS_COPY_SUCCESS');
  	}
  	else {
  		$msg = $model->getError();
  		$msgtype = 'error';
  	}
  	
  	$this->setRedirect( 'index.php?option=com_redform&view=fields', $msg, $msgtype);
  	$this->redirect();
  }
}
?>
