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
class RedformControllerForms extends JController
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

    $this->setRedirect( 'index.php?option=com_redform&view=form' );
  }
  
  /**
   * logic to create the edit event screen
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function edit( )
  {
    JRequest::setVar( 'view', 'form' );
    JRequest::setVar( 'hidemainmenu', 1 );

    $model  = $this->getModel('form');
    $task   = JRequest::getVar('task');

    $user =& JFactory::getUser();
    // Error if checkedout by another administrator
    if ($model->isCheckedOut( $user->get('id') )) {
    	$this->setRedirect( 'index.php?option=com_redform&view=forms', JText::_('COM_REDFORM_EDITED_BY_ANOTHER_ADMIN' ) );
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
    $post = JRequest::get('post', 4);

    $model = $this->getModel('form');

    if ($row = $model->store($post)) {

      switch ($task)
      {
        case 'apply':
          $link = 'index.php?option=com_redform&view=form&hidemainmenu=1&cid[]='.$row->id;
          break;

        default:
          $link = 'index.php?option=com_redform&view=forms';
          break;
      }
      $msg  = JText::_('COM_REDFORM_FORM_SAVED');

      $cache = &JFactory::getCache('com_redform');
      $cache->clean();

    } else {
      $msg  = '';
      $link   = 'index.php?option=com_redform&view=forms';
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
    
    $row = & JTable::getInstance('redform', 'RedformTable');
    $row->bind(JRequest::get('post'));
    $row->checkin();

    $this->setRedirect( 'index.php?option=com_redform&view=forms' );
  }
  
  function details()
  {
    JRequest::setVar( 'view', 'form' );
    JRequest::setVar( 'layout', 'detailsform' );
  	
    parent::display();
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

    $model = $this->getModel('forms');

    if(!$model->publish($cid, 1)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_('COM_REDFORM_FORMS_PUBLISHED');

    $this->setRedirect( 'index.php?option=com_redform&view=forms', $msg );
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

    $model = $this->getModel('forms');

    if(!$model->publish($cid, 0)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_('COM_REDFORM_FORMS_UNPUBLISHED');

    $this->setRedirect( 'index.php?option=com_redform&view=forms', $msg );
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

    $model = $this->getModel('forms');

    $msg = $model->delete($cid);

    $cache = &JFactory::getCache('com_redform');
    $cache->clean();

    $this->setRedirect( 'index.php?option=com_redform&view=forms', $msg );
  }
  
  function submitters()
  {
    $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
    $this->setRedirect( 'index.php?option=com_redform&view=submitters&form_id=' . $cid[0] );
  }
  
  /**
   * copy the form, and it's fields
   *
   */
  function copy()
  {
    $cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
    JArrayHelper::toInteger( $cids );
    
    $model = $this->getModel('form');
    
    if ($model->copy($cids)) {
	    $total = count( $cids );
	    $msg  = $total.' '.JText::_('COM_REDFORM_FORMS_COPIED');
    }
    
    $this->setRedirect( 'index.php?option=com_redform&view=forms', $msg );
  	
  }
}
?>
