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
 *
 */

/**
 */ 
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * form field class
 *
 */
class RedformField {
	
	var $field_id = 0;
	var $name     = null;
	var $validate = false;
	var $unique   = false;
	var $tooltip  = '';
	var $fieldtype = null;
	
	var $options = null;
	
	public function __construct($field_id, $fieldtype, $name, $tooltip = null, $validate = false, $unique = false)
	{
		$this->field_id  = $field_id;
		$this->fieldtype = $fieldtype;
		$this->name      = $name;
		$this->validate  = $validate;
		$this->unique    = $unique;
		$this->tooltip   = $tooltip;
		return true;
	}
	
	/**
	 * Adds an option to field (checkboxes, lists, etc...)
	 * 
	 * @param string $value
	 * @param string $text
	 * @return boolean
	 */
	public function addOption($value, $text)
	{
		$option = new stdclass();
		$option->value = $value;
		$option->text  = $text;
		if (empty($this->options)) {
			$this->options = array($option);
		}
		else {
			$this->options[] = $option;
		}
		return true;
	}
	
	/**
	 * return the html code for specified field
	 * 
	 * @param string $value current value of the field
	 * @param int $multiple_id the id of the form
	 * @param mixed $attributes not used... 
	 * @return string html
	 */
	public function getElement($value = null, $multiple_id = 0, $attributes = null)
	{
		$element = '';
		switch (strtolower($this->fieldtype))
		{
			case 'fullname':
			case 'textfield':
				$element = '<input type="text"'
				         . ' name="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' id="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' value="'.$value.'"'
				         . ($this->validate ? ' class="required"' : '')
				         .'/>';
				break;
			case 'hidden':
				$element = '<input type="hidden"'
				         . ' name="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' id="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' value="'.$value.'"'
				         .'/>';
				break;
			case 'username':
				$element = '<input type="text"'
				         . ' name="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' id="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' value="'.$value.'"'
				         . ($this->validate ? ' class="validate-username required"' : '')
				         .'/>';
				break;
			case 'email':
				$element = '<input type="text"'
				         . ' name="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' id="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' value="'.$value.'"'
				         . ($this->validate ? ' class="validate-email required"' : '')
				         .'/>';
				break;
				
			case 'radio':
				$attribs = array();
				if ($this->validate) {
					$attribs['class'] = "required";
				}
				$element = JHTML::_('select.radiolist', $this->options, 'rdf_field'.$this->field_id.'['.$multiple_id.']', $attribs, 'value', 'text', $value);
				break;
				
			case 'textarea':
				$element = '<textarea'
				         . ' name="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ' id="rdf_field'.$this->field_id.'['.$multiple_id.']"'
				         . ($this->validate ? ' class="validate-email required"' : '')
				         . '>'				         
				         . $value
				         . '</textearea>';
				         ;
				break;				
				
			case 'select':
				$attribs = array();
				if ($this->validate) {
					$attribs['class'] = "required";
				}
				$element = JHTML::_('select.genericlist', $this->options, "rdf_field'.$this->field_id.'['.$multiple_id.']", $attribs, 'value', 'text', $value);
				break;
				
			case 'multiselect':
				$attribs = array('multiple' => "multiple");
				$attribs = array('size' => 5);
				if ($this->validate) {
					$attribs['class'] = "required";
				}
				$element = JHTML::_('select.genericlist', $this->options, "rdf_field'.$this->field_id.'['.$multiple_id.']", $attribs, 'value', 'text', $value);
				break;
				
			case 'info':
				$element = implode('<br/>', $this->options);
				break;
				
			case 'checkbox':
				$element = '';
				foreach ($this->options as $op)
				{					
					$element .= $op->text. ' <input type="checkbox"'
					         . ' name="rdf_field'.$this->field_id.'['.$multiple_id.'][]"'
					         . ' value="'.$op->value.'"'
					         . ($value && $value == $op->value ? ' checked="checked"':'')
					         //. ($this->validate ? ' class="required"' : '')
					         .'/> ';
				}
				break;
				
			case 'fileupload':
				if (empty($value))
				{
					$element = '<input type="file"'
					         . ' name="rdf_field'.$this->field_id.'['.$multiple_id.']"'
					         . ' id="rdf_field'.$this->field_id.'['.$multiple_id.']"'
					         . ($this->validate ? ' class="required"' : '')
					         .'/>';
				}
				break;
			case 'wysiwyg':
				$editor = & JFactory::getEditor();
				$element = $editor->display( "rdf_field'.$this->field_id.'['.$multiple_id.']", $value, '100%;', '200', '75', '20', false ) ;
				break;
		}
		return $element;
	}
}