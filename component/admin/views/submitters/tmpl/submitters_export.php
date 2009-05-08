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
	echo JText::_('Submission date').",";
	echo JText::_('Form name').",";
	$fields = '';
	foreach ($this->fields as $key => $value) {
		$fields .= $value.",";
	}
	echo substr($fields, 0, -1)."\n";
	
	/* Data */
	$orderindex = array_flip($this->fields);
	if (count($this->submitters) > 0) {
		foreach ($this->submitters as $id => $value) {
			echo $value->submission_date.",";
			echo $value->formname.",";
			$fields = '';
			$find = array("\r\n", "\n", "\r");
			$replace = ' ';
			foreach ($orderindex as $field => $index) {
				if (isset($value->$field)) {
					$value->$field = str_replace($find, $replace, $value->$field);
					$fields .= str_replace("~~~", ";", $value->$field).",";
				}
				else $fields .= ",";
			}
			echo substr($fields, 0, -1)."\n";
		}
	}
exit;
?>
