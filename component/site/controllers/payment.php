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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Redform Controller
 */
class RedformControllerPayment extends JController {

	/**
    * Method to display the view
    *
    * @access   public
    */
   function __construct() 
   {
      parent::__construct();
			$this->registerTask('cancel', 'paymentcancelled');
   }
		
	function select()
	{
		JRequest::setVar('view',   'payment');
		JRequest::setVar('layout', 'select');
		$this->display();
	}

	function process()
	{
		$gw = JRequest::getVar('gw', '');
		if (empty($gw)) {
			JError::raise(0, 'MISSING GATEWAY');
			return false;
		} 
		
    $model  = &$this->getModel('payment');
    $helper = $model->getGatewayHelper($gw);
    $key    = JRequest::getVar('key');
    
    $details = $model->getPaymentDetails($key);
    
    $res = $helper->process($details);
    
    //echo '<pre>';print_r($res); echo '</pre>';exit;
    // get payment helper from selected gateway    
	}
	

	function processing()
  {
  	global $mainframe;
  
    $submit_key = JRequest::getVar('key');
    
    $model = &$this->getModel('payment');    
    $submitters = $model->getSubmitters();
    if (count($submitters))
    {
    	$first = current($submitters);
    	switch ($first->integration)
    	{
    		case 'redevent':
    			$mainframe->redirect('index.php?option=com_redevent&view=payment&submit_key='.$submit_key.'&state=processing');
    			break;
    	}
    }
    
		JRequest::setVar('view',   'payment');
		JRequest::setVar('layout', 'final');
		JRequest::setVar('state', 'processing');
		$this->display();
  }
  
  function paymentcancelled()
  {
    global $mainframe;
    
    $msg = JText::_('PAYMENT CANCELLED');
    $mainframe->redirect('index.php', $msg);
  }
  

  function notify()
  {
    global $mainframe;
    
    $submit_key = JRequest::getVar('key');
		$gw = JRequest::getVar('gw', '');
    RedformHelperLog::simpleLog('PAYMENT NOTIFICATION RECEIVED'. ': ' . $gw);
		if (empty($gw)) {
			RedformHelperLog::simpleLog('PAYMENT NOTIFICATION MISSING GATEWAY'.': '.$gw);
			return false;
		} 
		
    $model = &$this->getModel('payment');    
    $helper = $model->getGatewayHelper($gw);
    
    $res = $helper->notify();
    
    $submitters = $model->getSubmitters();
    if (count($submitters))
    {
    	$first = current($submitters);
    	switch ($first->integration)
    	{
    		case 'redevent':
    			$mainframe->redirect('index.php?option=com_redevent&view=payment&submit_key='.$submit_key.'&state='.($res ? 'accepted' : 'refused'));
    			break;
    	}
    }
    
		JRequest::setVar('view',   'payment');
		JRequest::setVar('layout', 'final');
		
    if ($res) { // the payment was received !
    	//TODO: send a mail ?
			JRequest::setVar('state', 'accepted');
    }
    else {
			JRequest::setVar('state', 'failed');
    }
    
		$this->display();
  }
}