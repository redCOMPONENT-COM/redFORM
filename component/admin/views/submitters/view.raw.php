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
class RedformViewSubmitters extends JView {
	/**
	 * redFORM view display method
	 * @return void
	 **/
	function display($tpl = null) {
		/* Get the submitters list */
		$submitters = $this->get('Submitters');
		$event = $this->get('CourseTitle');
		$fields = $this->get('Fields');
		$export_data = $this->get('SubmittersExport');
				
		$this->assignRef('export_data', $export_data);
		$this->assignRef('fields', $fields);
		$this->assignRef('event', $event);
		$this->assignRef('submitters', $submitters);
		
		parent::display($tpl);
	}
}
?>
