<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
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
    global $option;

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
    	$this->setRedirect( 'index.php?option=com_redform&view=forms', JText::_( 'EDITED BY ANOTHER ADMIN' ) );
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
      $msg  = JText::_( 'FORM SAVED');

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
    
    $row = & JTable::getInstance('redform', 'Table');
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
      JError::raiseError(500, JText::_( 'Select an item to publish' ) );
    }

    $model = $this->getModel('forms');

    if(!$model->publish($cid, 1)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_( 'FORMS PUBLISHED');

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
      JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
    }

    $model = $this->getModel('forms');

    if(!$model->publish($cid, 0)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

    $total = count( $cid );
    $msg  = $total.' '.JText::_( 'FORMS UNPUBLISHED');

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
    global $option;

    $cid    = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_( 'Select an item to delete' ) );
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
    $this->setRedirect( 'index.php?option=com_redform&view=submitters&cid[]=' . $cid[0] );
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
	    $msg  = $total.' '.JText::_( 'FORMS COPIED');
    }
    
    $this->setRedirect( 'index.php?option=com_redform&view=forms', $msg );
  	
  }
}
?>
