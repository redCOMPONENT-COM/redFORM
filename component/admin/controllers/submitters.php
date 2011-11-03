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
class RedformControllerSubmitters extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirect templates to templates as this is the standard call */
		$this->registerTask('save', 'apply');
		$this->registerTask('add',  'edit');
		$this->registerTask('forcedelete',  'remove');
	}
	
	function remove()
	{		
    $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_('COM_REDFORM_Select_an_item_to_delete' ) );
    }

    $model = $this->getModel('submitters');

    if (JRequest::getVar('task') == 'forcedelete') {
    	$msg = $model->delete($cid, true);
    }
    else {
    	$msg = $model->delete($cid);    	
    }

    $cache = &JFactory::getCache('com_redform');
    $cache->clean();

    $form_id = JRequest::getVar('form_id', 0);
    
    $this->setRedirect( 'index.php?option=com_redform&view=submitters' . ($form_id ? '&form_id='.$form_id : ''), $msg );
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
    // JRequest::checkToken() or die( 'Invalid Token' );
    $this->setRedirect( 'index.php?option=com_redform&view=submitters' );
  }
	
	/**
	 * Submitters
	 */
	function Submitters() {
    JRequest::setVar( 'view', 'submitters' );
    parent::display();
	}
	
	/**
	 * Export submitters data
	 */
	function Export() {
		$view = $this->getView('submitters', 'raw');
		$view->setModel( $this->getModel( 'submitters', 'RedformModel' ), true );
		$view->setLayout('submitters_export');
		$view->display();
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
    JRequest::setVar( 'view', 'submitter' );
    JRequest::setVar( 'hidemainmenu', 1 );

    parent::display();
  }
	
	
	/**
	 * Redirect back to redEVENT
	 */
	public function RedEvent() {
		$mainframe = &JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_redevent&view=attendees&xref='.JRequest::getInt('xref'));
	}
	
	function save()
	{		
    $form_id = JRequest::getVar('form_id', 0);
    $xref = JRequest::getVar('xref', 0);
    $integration = JRequest::getVar('integration', '');
    
    $rfcore = new RedFormCore();
    $res = $rfcore->saveAnswers($integration);
    
    if ($res) {
    	$msg = JText::_('COM_REDFORM_Submission_updated');
    	$type = 'message';
    }    
    else {
    	$msg = JText::_('COM_REDFORM_Submission_update_failed');   
    	$type = 'error'; 	
    }
    $url = 'index.php?option=com_redform&controller=submitters&task=submitters';
    if ($form_id) {
    	$url .= '&form_id='.$form_id;
    }
    if ($integration) {
    	$url .= '&integration='.$integration;
    }
    if ($xref) {
    	$url .= '&xref='.$xref;
    }
    $this->setRedirect( $url, $msg, $type );    
	}
}
?>
