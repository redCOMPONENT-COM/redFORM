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
	header('Content-Type: application/csv');
	header('Content-Encoding: UTF-8');
	header('Content-Disposition: inline; filename="submitters.csv"');
	$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
	echo JText::_('Submission date').",";
	echo JText::_('Form name').",";
	if ($xref) echo JText::_('EVENT').",";
	
	// echo first line with field names
	$fields = array();
	foreach ($this->fields as $key => $value) {		
		$fields[] = $value->field;
	}
	echo $this->writecsvrow($fields);
	
	/* Data */
	if (count($this->submitters) > 0) 
	{
		foreach ($this->submitters as $id => $value) 
		{
			echo $value->submission_date.",";
			echo $value->formname.",";
			if ($xref) echo $this->event.",";
			
			$fields = array();
			$find = array("\r\n", "\n", "\r");
			$replace = ' ';
			foreach ($this->fields as $key => $field) 
			{
        $fieldname = 'field_'. $field->id;
        if (isset($value->$fieldname)) 
        {
					$value->$fieldname = str_replace($find, $replace, $value->$fieldname);
					$fields[] = str_replace("~~~", ";", $value->$fieldname);
				}
			}
			echo $this->writecsvrow($fields);
		}
	}
exit;
?>
