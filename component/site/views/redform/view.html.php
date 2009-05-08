<?php
/** 
 * @copyright Copyright (C) 2008-2009 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 */
class RedformViewRedform extends JView {
	function display($tpl = null) {
		global $mainframe;
		
		/* See if we need to save any form */
		if (JRequest::getCmd('task') == 'save') $save = $this->get('SaveForm');
		else if (JRequest::getCmd('task') == 'redeventvm') {
			$save = array();
		}
		if (is_array($save)) $ok = true;
		else $ok = false;
		
		/* Get the VirtueMart settings */
		$vmsettings = $this->get('VmSettings');
		
		/* Set the page title */
		$document = JFactory::getDocument();
		$document->setTitle($document->getTitle().' - '.JText::_('redFORM'));
		
		/* Get the product image and description */
		$productdetails = $this->get('ProductDetails');
		
		/* Assign the necessary data */
		$this->assignRef('save', $save);
		$this->assignRef('ok', $ok);
		$this->assignRef('vmsettings', $vmsettings);
		$this->assignRef('productdetails', $productdetails);
		
		parent::display($tpl);
	}
}
?>
