<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
class RedformViewValues extends JView {
   
	function display() {
		/* Get the mailinglists */
		$mailinglists = array(array('optionValue' => 'acajoom', 'optionDisplay' => 'Acajoom'), 
							array('optionValue' => 'ccnewsletter', 'optionDisplay' => 'ccNewsletter'),
							array('optionValue' => 'phplist', 'optionDisplay' => 'PHPList')
							);
		echo json_encode($mailinglists);
	}
}
?>