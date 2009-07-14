<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Redform Controller
 */
class RedformControllerRedform extends RedformController {
	
	/**
    * Method to display the view
    *
    * @access   public
    */
   function __construct() 
   {
      parent::__construct();

      /* Redirect templates to templates as this is the standard call */
      //$this->registerTask('save','redform');
	    $this->registerTask('redeventvm','redform');
   }

	
	/**
	 * Method to show a weblinks view
	 *
	 * @access	public
	 */
	public function redform() {
		/* Set a default view if none exists */
		JRequest::setVar('view', 'redform' );
		JRequest::setVar('layout', 'redform' );
		
		parent::display();
	}
	
	/**
	 * Shows a captcha
	 */
	public function displaycaptcha() {
		global $mainframe;
		// By default, just display an image
		$document = JFactory::getDocument();
		$doc = JDocument::getInstance('raw');
		// Swap the objects
		$document = $doc;
		$mainframe->triggerEvent('onCaptcha_display', array());
   }
  
  /**
   * save the posted form data.
   *
   */
  function save()
  {
    $model = $this->getModel('redform');
    
    $result = $model->saveform();
    
    if (!$result) {
    	echo JText::_('Sorry, there was a problem with your submission');
    	return;
    }
    
    echo $result[1];
    return;
  }
}

?>
