<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */
	 
	defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
	header('Content-Type: application/csv');
	header('Content-Encoding: UTF-8');
	header('Content-Disposition: inline; filename="submitters.csv"');
	$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
	echo JText::_('Submission date').",";
	echo JText::_('Form name').",";
	if ($xref) echo JText::_('EVENT').",";
	$fields = '';
	foreach ($this->fields as $key => $value) {
		$fields .= $value->field.",";
	}
	echo substr($fields, 0, -1)."\n";
	
	/* Data */
	if (count($this->submitters) > 0) {
		foreach ($this->submitters as $id => $value) {
			echo $value->submission_date.",";
			echo $value->formname.",";
			if ($xref) echo $this->event.",";
			$fields = '';
			$find = array("\r\n", "\n", "\r");
			$replace = ' ';
			foreach ($this->fields as $key => $field) {
        $fieldname = 'field_'. $field->id;
        if (isset($value->$fieldname)) 
        {
					$value->$fieldname = str_replace($find, $replace, $value->$fieldname);
					$fields .= str_replace("~~~", ";", $value->$fieldname).",";
				}
				else $fields .= ",";
			}
			echo substr($fields, 0, -1)."\n";
		}
	}
exit;
?>
