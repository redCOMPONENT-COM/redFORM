<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * redFORM view
 */

/* No direct access */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

/**
 * redFORM View
 */
class RedformViewSubmitter extends JView {
	
  function display($tpl = null) 
  {
  	$submitter = & $this->get('Data');
  	JRequest::setVar('answers', array($submitter));
  	JRequest::setVar('submit_key', $submitter->submit_key);
  	JRequest::setVar('xref', $submitter->xref);
  	JRequest::setVar('redform_edit', true);
  	JRequest::setVar('submitter_id', $submitter->id);
  	$this->assignRef('submitter', $submitter);
  	JToolBarHelper::title(JText::_( 'EDIT_SUBMITTER' ), 'redform_submitters');
  	JToolBarHelper::save();
  	JToolBarHelper::cancel();
        
  	/* Display the page */
  	parent::display($tpl);
  }
}
?>
