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
	
	// echo first line with field names
	$fields = array();
	$fields[] = JText::_('COM_REDFORM_Submission_date');
	$fields[] = JText::_('COM_REDFORM_Form_name');
	$fields[] = JText::_('COM_REDFORM_Unique_id');
	if ($xref) {
		$fields[] = JText::_('COM_REDFORM_EVENT');
	}	
	foreach ($this->fields as $key => $value) {		
		$fields[] = $value->field_header;
	}
	if ($this->form->activatepayment) {
		$fields[] = JText::_('COM_REDFORM_Total_price');
		$fields[] = JText::_('COM_REDFORM_Payment_status');				
	}
	echo $this->writecsvrow($fields);
	
	/* Data */
	if (count($this->submitters) > 0) 
	{
		foreach ($this->submitters as $id => $value) 
		{			
			$fields = array();
			$fields[] = $value->submission_date;
			$fields[] = $value->formname;
			if ($this->integration == 'redevent') {
				$fields[] = $this->course->uniqueid_prefix.$value->attendee_id;
			}
			else { 
				$fields[] = $value->submit_key;
			}
			if ($xref) $fields[] = $this->event;
			
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
				else 
				{
					$fields[] = '';
				}
			}
			if ($this->form->activatepayment) 
			{
		  	$fields[] = $value->price;
		  	$fields[] = $value->status;
			}
			echo $this->writecsvrow($fields);
		}
	}
exit;
?>
