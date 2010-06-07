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

require_once('redform.defines.php');

require_once(RDF_PATH_SITE.DS.'classes'.DS.'field.php');
require_once(RDF_PATH_SITE.DS.'models'.DS.'redform.php');

class RedFormCore extends JObject {
	
	/**
	 * returns the html code for form elements (only the elements ! not the form itself, or the submit buttons...)
	 *
	 * @param int id of the form to display
	 * @param int/array optional id of submission_id, for example when we are modifying previous answers
	 * @param int optional number of instance of forms to display (1 is default)
	 * @return string html
	 */
	function displayForm($form_id, $submitter_id = 0, $multiple = 1)
	{
		$html = '<form action="'.JRoute::_('index.php?option=com_redform').'" method="post" name="redform" enctype="multipart/form-data" onsubmit="return CheckSubmit();">';
		$html .= $this->getFormFields($form_id, $submit_key, $multiple);
								
		/* Get the user details form */
		if (!$answers && !JRequest::getVar('redform_edit') &&  !JRequest::getVar('redform_add')) 
		{
			$html .= '<div id="submit_button" style="display: block;"><input type="submit" id="regularsubmit" name="submit" value="'.JText::_('Submit').'" />';
			$html .= '</div>';
		}
		
		$html .= '<input type="hidden" name="task" value="save" />';
		if ($submitter_id > 0) 
		{
			$html .= '<input type="hidden" name="submitter_id" value="'.$submitter_id.'" />';			
		}
		
		if (JRequest::getVar('redform_edit') || JRequest::getVar('redform_add')) {
			$html .= '<input type="hidden" name="controller" value="submitters" />';
		}
			
		if (JRequest::getVar('redform_edit')) {
			$html .= '<input type="hidden" name="event_task" value="review" />';
		}
		
		$html .= '</div>';
		
		$html .= '</form>';
	}
	
	function getFormFields($form_id, $submit_key = null, $multi = 1, $options = array())
	{
		$uri       = JURI::getInstance();
		$user      = JFactory::getUser();
		$document  = &JFactory::getDocument();
		
		$model_redform = new RedformModelRedform();
		$model_redform->setFormId($form_id);
		
		$form   = $model_redform->getForm();				
		$fields = $model_redform->getFormFields();
		
	  // css
    $document->addStyleSheet(JURI::base().'components/com_redform/assets/css/tooltip.css');
    $document->addStyleSheet(JURI::base().'plugins/content/redform/plgredform.css');
    
		// load jquery for the form javascript
		if (JRequest::getVar('format', 'html') == 'html') {
			JHTML::_('behavior.tooltip');
			jimport('joomla.html.html');
			$document->addScript(JURI::root().'components/com_redform/assets/js/jquery-1.4.min.js' );
			//$document->addCustomTag( '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.js"></script>' ); // for debugging...
			$document->addScriptDeclaration( 'jQuery.noConflict();' );
		}
  	
  	// custom tooltip
  	$toolTipArray = array('className'=>'redformtip'.$form->classname);
    JHTML::_('behavior.tooltip', '.hasTipField', $toolTipArray);

    // currency for javascript
    $js = "var currency = \"".$form->currency."\";\n";
    $document->addScriptDeclaration($js);
    
  	self::JsCheck();
  	self::jsPrice();
		
		// redmember integration: pull extra fields
		if ($user->get('id') && file_exists(JPATH_ROOT.DS.'components'.DS.'com_redmember')) {
			$this->getRedmemberfields($user);
		}
	
		/* Check if there are any answers to be filled in (already submitted)*/
		/* This is an array starting with 0 */
		if (isset($options['answers'])) {
			$answers = $options['answers'];
		}
		else {
			$answers = null;
		}
		
		/* Stuff to find and replace */
		$find = array(' ', '_', '-', '.', '/', '&', ';', ':', '?', '!', ',');
		$replace = '';
		
		$html = '<div id="redform'.$form->classname.'">';
		
		if ($form->showname) {
			$html .= '<div id="formname">'.$form->formname.'</div>';
		}
			
			
		// for virtuemart
		if (JRequest::getInt('productid', false))
		{
			$productinfo = $this->getProductinfo();
			if (!stristr('http', $productinfo->product_full_image)){
				$productimage = $uri->root().'/components/com_virtuemart/shop_image/product/'.$productinfo->product_full_image;
			}
			else {
				$productimage = $productinfo->product_full_image;
			}
			$html .= '<div id="productimage">'.JHTML::_('image', $productimage, $productinfo->product_name).'</div>';
			$html .= '<div id="productname">'.$productinfo->product_name.'</div>';
		}			

		if ($multi > 1 && $user->id == 0)
		{
			$html .= '<div id="needlogin">'.JText::_('LOGIN_BEFORE_MULTI_SIGNUP').'</div>';
			$multi = 1;
		}
			
		if ($multi > 1)
		{
			if (empty($answers)) {
				// link to add signups
				$html .= '<div id="signupuser"><a href="javascript: AddUser();">'.JText::_('SIGN_UP_USER').'</a></div>';
			}

			// signups display controls
			$html .= '<div id="signedusers" style="float: right">';
			$html .= '<a href="javascript: ShowAllUsers(true);" >'.JText::_('SHOW_ALL_USERS').'</a><br />'
			       . '<a href="javascript: ShowAllUsers(false);" >'.JText::_('HIDE_ALL_USERS').'</a><br />'
			       .JText::_('Signed up:').'<br />';
			$html .= '<a href="javascript: ShowSingleForm(\'div#formfield1\');"># 1</a><br />';
			if ($answers)
			{
				for ($k = 2; $k < count($answers)+1; $k++) {
					$html .= '<a href="javascript: ShowSingleForm(\'div#formfield'.$k.'\');"># '.$k.'</a><br />';
				}
			}
			$html .= '</div>';
		}

		if ($answers)
		{
			// set multi to number of answers...
			$multi = count($answers);
		}

		/* Loop through here for as many forms there are */
		for ($signup = 1; $signup <= $multi; $signup++)
		{
			if ($answers) {
				$submitter_id = $answers[($signup-1)]->sid;
				$html .= '<input type="hidden" name="submitter_id'.$signup.'" value="'.$submitter_id.'" />';	
			}
			else {
				$submitter_id = 0;
			}
			
			/* Make a collapsable box */
			$html .= '<div id="formfield'.$signup.'" class="formbox" style="display: '.($signup == 1 ? 'block' : 'none').';">';
			if ($multi > 1) {
				$html .= '<fieldset><legend>'.JText::sprintf('REDFORM_FIELDSET_SIGNUP_NB', $signup).'</legend>';
			}
				
			if ($answers && $multi > 1) {
				$html .= '<div class="confirmbox"><input type="checkbox" name="confirm[]" value="'.$answers[($signup-1)]->fields->id.'" checked="checked" />'.JText::_('INCLUDE_REGISTRATION').'</div>';
			}
			else if ($answers) {
				$html .= '<input type="hidden" name="confirm[]" value="'.$answers[($signup-1)]->fields->id.'" />';
			}
		
			if ($form->activatepayment && isset($options['eventdetails']) && $options['eventdetails']->course_price) {
				$html .= '<div class="eventprice" price="'.$options['eventdetails']->course_price.'">'.JText::_('Registration price').': '.$form->currency.' '.$options['eventdetails']->course_price.'</div>';
			}
			if ($form->activatepayment && isset($options['booking']) && $options['booking']->course_price) {
				$html .= '<div class="bookingprice" price="'.$options['booking']->course_price.'">'.JText::_('Registration price').': '.$form->currency.' '.$options['booking']->course_price.'</div>';
			}
							
			if (isset($options['extrafields']) && count($options['extrafields']))
			{
				foreach ($options['extrafields'] as $field)
				{
					$html .= '<div class="fieldline">';
					$html .= '<div class="label">'.$field['label'].'</div>';
					$html .= '<div class="field">'.$field['field'].'</div>';
					$html .= '</div>';
				}
			}

			foreach ($fields as $key => $field)
			{
				$html .= '<div id="fieldline_'.$field->id.'" class="fieldline">';

				$values = $model_redform->getFormValues($field->id);

				if ($field->fieldtype == 'info' && count($values))
				{
					$html .= '<div class="infofield">' . $values[0]->value . '</div>';
					$html .= '</div>';
					continue;
				}
					
				$cleanfield = 'field_'. $field->id;
				$element = "<div class=\"field".$field->fieldtype."\">";
				
				switch ($field->fieldtype)
				{
					case 'radio':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[radio][]">'.$field->field.'</div>';
						$element .= '<div class="fieldoptions">';		
						foreach ($values as $id => $value)
						{
							$element .= '<div class="fieldoption">';
							$element .= "<input class=\"".$form->classname.$field->parameters->get('class','')." ";
							if ($field->validate) $element .= "required";
							$element .= "\"";
							if ($answers)
							{
								if (in_array($value->value, explode('~~~', $answers[($signup-1)]->fields->$cleanfield))) {
									$element .= ' checked="checked"';
								}
							}
							else if ($user->get($field->redmember_field))
							{
								$fvalues = explode(',', $user->get($field->redmember_field));
								if (in_array($value->value, $fvalues)) {
									$element .= ' checked="checked"';
								}
							}
							$element .= ' type="radio" name="field'.$field->id.'.'.$signup.'[radio][]" value="'.$value->id.'" price="'.$value->price.'" />'.$value->value."\n";
							$element .= "</div>\n";
						}
						$element .= "</div>\n";
						break;
	
					case 'textarea':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[textarea]">'.$field->field.'</div>';
						$element .= '<textarea class="'.$form->classname.$field->parameters->get('class','');
						if ($field->validate) $element .= ' validate';
						$element .= '" name="field'.$field->id.'.'.$signup.'[textarea]"';
						$element .= ' cols="'.$field->parameters->get('cols',25).'" rows="'.$field->parameters->get('rows',6).'"';
						$element .= ">";
						if ($answers)
						{
							if (isset($answers[($signup-1)]->fields->$cleanfield)) {
								$element .= $answers[($signup-1)]->fields->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$element .= $user->get($field->redmember_field);
						}
						$element .= "</textarea>\n";
						break;
	
					case 'wysiwyg':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[wysiwyg]">'.$field->field.'</div>';
						$content = '';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->fields->$cleanfield)) {
								$element .= $answers[($signup-1)]->fields->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$content = $user->get($field->redmember_field);
						}
						$editor = & JFactory::getEditor();
	
						// Cannot have buttons, it triggers an error with tinymce for unregistered users
						$element .= $editor->display( "field".$field->id.'.'.$signup."[wysiwyg]", $content, '100%;', '200', '75', '20', false ) ;
						$element .= "\n";
						break;
	
					case 'email':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[email][]">'.$field->field.'</div>';
						$element .= "<div class=\"emailfields\">";
						$element .= "<div class=\"emailfield\">";
						$element .= "<input class=\"".$form->classname.$field->parameters->get('class','')." ";
						if ($field->validate) $element .= "required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[email][]\"";
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						$element .= ' value="';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->fields->$cleanfield)) {
								$element .= $answers[($signup-1)]->fields->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$element .= $user->get($field->redmember_field);
						}
						else if ($signup == 1 && $user->email) {
							$element .= $user->email;
						}
						$element .= "\" />\n";
						$element .= "</div>\n";
							
						$element .= "<div class=\"newsletterfields\">";
						/* E-mail field let's see */
						// TODO: transfer to field !
						foreach ($values as $id => $value)
						{
							if (strlen($value->listnames) > 0)
							{
								$listnames = explode(";", $value->listnames);
								if (count($listnames) > 0)
								{
									$element .= '<div id="signuptitle">'.JText::_('SIGN_UP_MAILINGLIST').'</div>';
									$element .= "<div class=\"field".$field->fieldtype."_listnames\">";
									foreach ($listnames AS $listkey => $listname)
									{
										$element .= "<div class=\"field_".$listkey."\"><input class=\"".$form->classname." ";
										$element .= "\" type=\"checkbox\" name=\"field".$field->id.'.'.$signup."[email][listnames][]\" value=\"".$listname."\" />".$listname.'</div>';
									}
									$element .= "</div>\n";
								}
							}
						}
						$element .= "</div>\n";
						$element .= "</div>\n";
						break;
	
					case 'fullname':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[fullname][]">'.$field->field.'</div>';
						$element .= "<input class=\"".$form->classname.$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[fullname][]\"";
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						$element .= ' value="';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->fields->$cleanfield)) {
								$element .= $answers[($signup-1)]->fields->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$element .= $user->get($field->redmember_field);
						}
						else if ($signup == 1 && $user->name) {
							$element .= $user->name;
						}
						$element .= "\" />\n";
						break;
	
					case 'username':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[username][]">'.$field->field.'</div>';
						$element .= "<input class=\"".$form->classname.$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[username][]\"";
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						$element .= ' value="';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->fields->$cleanfield)) {
								$element .= $answers[($signup-1)]->fields->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$element .= $user->get($field->redmember_field);
						}
						else if ($signup == 1 && $user->username) {
							$element .= $user->username;
						}
						$element .= "\" />\n";
						break;
	
					case 'textfield':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[text][]">'.$field->field.'</div>';
						$element .= "<input class=\"".$form->classname.$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[text][]\"";
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						$element .= ' value="';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->fields->$cleanfield)) {
								$element .= $answers[($signup-1)]->fields->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$element .= $user->get($field->redmember_field);
						}
						$element .= "\" />\n";
						break;
	
					case 'date':
						JHTML::_('behavior.calendar');
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[date]">'.$field->field.'</div>';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->fields->$cleanfield)) {
								$val = $answers[($signup-1)]->fields->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$val = $user->get($field->redmember_field);
						}
						else {
							$val = null;
						}
						$class = $form->classname.$field->parameters->get('class','');
						if ($field->validate) $class .= " required";
						
						$element .= JHTML::_('calendar', $val, 'field'.$field->id.'.'.$signup.'[date]', 'field'.$field->id.'.'.$signup.'[date]', 
						               $field->parameters->get('dateformat','%Y-%m-%d'), 
						               'class="'.$class.'"');
						break;
	
					case 'price':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[price][]">'.$field->field.'</div>';
						// if has not null value, it is a fixed price, if not this is a user input price
						if (count($values) && $values[0]) // display price and add hidden field (shouldn't be used when processing as user could forge the form...)
						{
							$element .= $form->currency .' '.$values[0]->value;
							$element .= '<input type="hidden" name="field'.$field->id.'.'.$signup.'[price][]" value="'.$values[0]->value.'"/>';
						}
						else // like a text input
						{
							$element .= '<input class="'. $form->classname.$field->parameters->get('class','') .($field->validate ? " required" : '') .'"';
							$element .= ' type="text" name="field'.$field->id.'.'.$signup.'[price][]"';
							$element .= ' size="'.$field->parameters->get('size', 25).'"';
							$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
							$element .= ' value="';
							if ($answers)
							{
								if (isset($answers[($signup-1)]->fields->$cleanfield)) {
									$element .= $answers[($signup-1)]->fields->$cleanfield;
								}
							}
							else if ($user->get($field->redmember_field)) {
								$element .= $user->get($field->redmember_field);
							}
							$element .= '"';
							$element .= '/>';
						}
						$element .= "\n";
						break;
						
					case 'checkbox':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[checkbox][]">'.$field->field.'</div>';
						$element .= '<div class="fieldoptions">';						
						foreach ($values as $id => $value)
						{
							$element .= '<div class="fieldoption">';
							$element .= "<input class=\"".$form->classname.$field->parameters->get('class','')." ";
							if ($field->validate) $element .= "required";
							$element .= "\"";
							if ($answers)
							{
								if (in_array($value->value, explode('~~~', $answers[($signup-1)]->fields->$cleanfield))) {
									$element .= ' checked="checked"';
								}
							}
							else if ($user->get($field->redmember_field))
							{
								$fvalues = explode(',', $user->get($field->redmember_field));
								if (in_array($value->value, $fvalues)) {
									$element .= ' checked="checked"';
								}
							}
							$element .= ' type="checkbox" name="field'.$field->id.'.'.$signup.'[checkbox][]" value="'.$value->value.'" price="'.$value->price.'" /> '.$value->value."\n";
							$element .= "</div>\n";
						}
						$element .= "</div>\n";
						break;
	
					case 'select':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[select][]">'.$field->field.'</div>';
						$element .= "<select name=\"field".$field->id.'.'.$signup."[select][]\" class=\"".$form->classname.$field->parameters->get('class','')."\">";
						foreach ($values as $id => $value)
						{
							$element .= "<option value=\"".$value->value."\"";
							if ($answers) 
							{
								if ($answers[($signup-1)]->fields->$cleanfield == $value->value) {
									$element .= ' selected="selected"';
								}
							}
							else if ($user->get($field->redmember_field) == $value->value) {
								$element .= ' selected="selected"';
							}
							$element .= ' price="'.$value->price.'" >'.$value->value."</option>";
						}
						$element .= '</select>';
						$element .= "\n";
						break;
	
					case 'multiselect':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[multiselect][]">'.$field->field.'</div>';
						$element .= '<select name="field'.$field->id.'.'.$signup.'[multiselect][]"'
						          . ' multiple="multiple" size="'.$field->parameters->get('size',5).'"'
						          . ' class="'.trim($form->classname.$field->parameters->get('class','').($field->validate ?" required" : '')).'"'
						          .'>'
						          ;
						foreach ($values as $id => $value)
						{
							$element .= "<option value=\"".$value->value."\"";
							if ($answers)
							{
								if (in_array($value->value, explode('~~~', $answers[($signup-1)]->fields->$cleanfield))) {
									$element .= ' selected="selected"';
								}
							}
							else if ($user->get($field->redmember_field))
							{
								$fvalues = explode(',', $user->get($field->redmember_field));
								if (in_array($value->value, $fvalues)) {
									$element .= ' selected="selected"';
								}
							}
							$element .= '" price="'.$value->price.'" />'.$value->value."</option>";
						}
						$element .= '</select>';
						$element .= "\n";
						break;
	
					case 'recipients':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[recipients][]">'.$field->field.'</div>';
						$element .= "<select name=\"field".$field->id.'.'.$signup."[recipients][]\""
						         . ($field->parameters->get('multiple', 1) ? ' multiple="multiple"' : '')
						         . ' size="'.$field->parameters->get('size', 5).'"'
						         . ' class="'.$form->classname.$field->parameters->get('class','').($field->validate ?" required" : '').'"'
						         . '>';
						foreach ($values as $id => $value)
						{
							$element .= "<option value=\"".$value->value."\"";
							if ($answers)
							{
								if (in_array($value->value, explode('~~~', $answers[($signup-1)]->fields->$cleanfield))) {
									$element .= ' selected="selected"';
								}
							}
							else if ($user->get($field->redmember_field))
							{
								$fvalues = explode(',', $user->get($field->redmember_field));
								if (in_array($value->value, $fvalues)) {
									$element .= ' selected="selected"';
								}
							}
							$element .= " >".$value->value."</option>";
						}
						$element .= '</select>';
						$element .= "\n";
						break;
	
					case 'fileupload':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'.'.$signup.'[fileupload][]">'.$field->field.'</div>';
						if ($submitter_id == 0) {
							$element .= "<input type=\"file\" name=\"field".$field->id.'.'.$signup."[fileupload][]\" class=\"fileupload".$field->parameters->get('class','')."\" id=\"fileupload_".$field->id."\"/>";
						}
						$element .= "\n";
						break;
				}
				
				$html .= $label.$element;
				$html .= '</div>'; // fieldtype div
				
				if ($field->validate || strlen($field->tooltip))
				{
					$html .= '<div class="fieldinfo">';
					if ($field->validate) {
						$img = JHTML::image(JURI::root().'components/com_redform/assets/images/warning.png', JText::_('Required'));
						$html .= ' <span class="editlinktip hasTipField" title="'.JText::_('Required').'" style="text-decoration: none; color: #333;">'. $img .'</span>';
					}
					if (strlen($field->tooltip) > 0) {
						$img = JHTML::image(JURI::root().'components/com_redform/assets/images/info.png', JText::_('ToolTip'));
						$html .= ' <span class="editlinktip hasTipField" title="'.$field->field.'::'.$field->tooltip.'" style="text-decoration: none; color: #333;">'. $img .'</span>';
					}
					$html .= '</div>';
				}
	
				$html .= '</div>'; // fieldline_ div
			}
			if ($multi > 1) {
				$html .= '</fieldset>';
			}
			if (isset($this->_rwfparams['uid']))
			{
				$html .= '<div>'.JText::_('JOOMLA_USER').': '. JHTML::_('list.users', 'uid', $this->_rwfparams['uid'], 1, NULL, 'name', 0 ).'</div>';
			}
			$html .= '</div>'; // formfield div
		}
			
		/* Add the captcha */
		if ($form->captchaactive && $submitter_id == 0) {
			$html .= '<div id="redformcaptcha"><img src="index.php?option=com_redform&task=displaycaptcha&controller=redform">';
			$html .= ' '.JHTML::tooltip(JText::_('CAPTCHA_TOOLTIP'), JText::_('CAPTCHA'), 'tooltip.png', '', '', false).'</div>';
			$html .= '<div id="redformcaptchaword"><input type="text" name="captchaword"></div>';
		}
		
		if (!empty($submit_key)) {
			// link to add signups
			$html .= '<input type="hidden" name="submit_key" value="'.$submit_key.'" />';
		}
		
		$html .= '<input type="hidden" name="curform" value="'.($answers && count($answers) ? count($answers) : 1).'" />';
		$html .= '<input type="hidden" name="form_id" value="'.$form_id.'" />';
		$html .= '<input type="hidden" name="multi" value="'.$multi.'" />';
		
		return $html;
	}
		
	/**
	 * saves submitted form data
	 * 
	 * @param string key, unique key for the 3rd party (allows to prevent deletions from within redform itself for 3rd party, and to find out which submission belongs to which 3rd party...)
	 * @param array data if empty, the $_POST variable is used
	 * @return int/array submission_id, or array of submission ids in case of success, 0 otherwise
	 */
	function saveAnswers($key, $options = array(), $data = null)
	{		
		require_once(RDF_PATH_SITE.DS.'models'.DS.'redform.php');
		$model = new RedformModelRedform();
		
		if (!$result = $model->apisaveform($key, $options, $data))
		{
			$this->setError($result->getError());
			return false;
		}				
		return $result;
	}
	
	/**
	 * removes submissions
	 * 
	 * @param array submission ids 
	 * @return bool true for success
	 */
	function removeAnswers($submission_ids)
	{
		
	}
	

	function jsPrice()
	{
		$script = <<< EOF
		jQuery(function () {
		   jQuery("#redform").find(":input").change(updatePrice);

		   updatePrice();
		});

		function updatePrice()
		{
			var price = 0.0;
			var active = parseInt(jQuery("input[name='curform']").val());

			var countforms = 0;
			
			for (var i = 1; i < active+1; i++)
			{
				var signup = jQuery("#formfield"+i);
				signup.find("input[name*=price]").each(function(k) {
					var p = jQuery(this).val();
					if (p) {
						price += parseFloat(p);
					}
				});
				
				signup.find("[selected]").each(function() {
					var p = jQuery(this).attr('price');
					if (p) {
						price += parseFloat(p);
					}
				});

				signup.find("[checked]").each(function() {
					var p = jQuery(this).attr('price');
					if (p) {
						price += parseFloat(p);
					}
				});

				signup.find(".eventprice").each(function() {
					var p = jQuery(this).attr('price');
					if (p) {
						price += parseFloat(p);
					}
				});

				signup.find(".bookingprice").each(function() {
					var p = jQuery(this).attr('price');
					if (p) {
						price += parseFloat(p);
					}
				});
			}
			// set the price
			if (price > 0 && !jQuery("#totalprice").length) {
				jQuery('#redform').append('<div id="totalprice">'+totalpricestr+': '+currency+' <span></span></div>');
			}
			jQuery("#totalprice span").text(Math.round(price*100)/100);
		}
EOF;
		
		$doc = &JFactory::getDocument();
		$doc->addScriptDeclaration($script);
		$doc->addScriptDeclaration('var totalpricestr = "'.JText::_('Total Price')."\";\n");
	}
	
	function JsCheck() 
	{
		/* Create some dynamic JS */
		?>
		<script type="text/javascript">		
			function CheckSubmit(form) 
			{
				var msg = '';
				var result = true;
				var newclass = 'emptyfield';
				var checkboxmsg = false;
				var radiomsg = false;

				// only check the form that were activated by the user
				var forms = jQuery('.formbox');
				var nb_active = parseInt(jQuery("input[name='curform']").val());

				for (var j = 0 ; j < nb_active ; j++)
				{
					// get the input data of the form
					var formelements = jQuery(forms[j]).find(':input');

  				for(i=0; i < formelements.length; i++) 
  	  		{
  					var check_element = formelements[i];
  					/* Check field type */
  					/* Fullname */
  					if (check_element.name.match("fullname") && check_element.className.match("required")) {
  						var fullresult = CheckFill(check_element);
  						if (!fullresult) {
								msg += getLabel(check_element).text()+': '+"<?php echo JText::_('please enter a name'); ?>\n";
		  				}
  						if (result) result = fullresult;
  					}
  					
  					/* Text field */
  					if (check_element.name.match("text") && check_element.className.match("required")) {
  						var textresult = CheckFill(check_element);
  						if (!textresult) {
								msg += getLabel(check_element).text()+': '+"<?php echo JText::_('this field is required'); ?>\n";
		  				}
  						if (result) result = textresult;
  					}
  					
  					/* Textarea field */
  					if (check_element.name.match("textarea") && check_element.className.match("required")) {
  						var textarearesult = CheckFill(check_element);
  						if (!textarearesult) {
								msg += getLabel(check_element).text()+': '+"<?php echo JText::_('this field is required'); ?>\n";
		  				}
  						if (result) result = textarearesult;
  					}
  					
  					/* Username field */
  					if (check_element.name.match("username") && check_element.className.match("required")) {
  						var usernameresult = CheckFill(check_element);
  						if (!usernameresult) {
								msg += getLabel(check_element).text()+': '+"<?php echo JText::_('please enter an username'); ?>\n";
		  				}
  						if (result) result = usernameresult;
  					}
  					
  					/* E-mail */
  					if (check_element.name.match("email") && check_element.className.match("required")) {
  						if (CheckFill(check_element)) {
  							if (!CheckEmail(check_element.value)) {
  								msg = msg + "<?php echo JText::_('No valid e-mail address'); ?>\n";
  								if (result) result = false;
  							}
  						}
  						else {
  							msg = msg + "<?php echo JText::_('E-mail address is empty'); ?>\n";
  							if (result) result = false;
  						}
  					}

  					/* multiselect field */
  					if (check_element.name.match("multiselect") && check_element.className.match("required")) {
  						var multires = CheckFill(check_element);
  						if (!multires) {
								msg += getLabel(check_element).text()+': '+"<?php echo JText::_('select a value'); ?>\n";
  	  				}
  						if (result) result = multires;
  					}
  					
		        /* Radio buttons */
	          if (check_element.name.match("radio") && check_element.className.match("required")) {
	            radios = document.getElementsByName(check_element.name);
	            var radiocheck = false;
	            for (var rct=radios.length-1; rct > -1; rct--) {
	              if (radios[rct].checked) {
	                radiocheck = true;
	                rct = -1;
	              }
	            }
	            if (radiocheck == false) {
	              addClass(check_element, newclass);
	              if (radiomsg == false) radiomsg = true;
	              if (result) result = false;
	            }
	          }
	          
	          /* Check boxes */
	          if (check_element.name.match("checkbox") && check_element.className.match("required")) {
	            checkboxes = document.getElementsByName(check_element.name);
	            var checkboxcheck = false;
	            for (var rct=checkboxes.length-1; rct > -1; rct--) {
	              if (checkboxes[rct].checked) {
	                checkboxcheck = true;
	                rct = -1;
	              }
	            }
	            
	            if (checkboxcheck == false) {
	              addClass(check_element, newclass);
	              if (checkboxmsg == false) checkboxmsg = true;
	              if (result) result = false;
	            }
	          }
  				}
				}
				if (result == false) {
					alert(msg);
					<?php if (JRequest::getVar('redform_edit') || JRequest::getVar('redform_add')) { ?>
						exit(0);
					<?php }
					else { ?>
						return false;
					<?php } ?>
					
				}

				return result;
			}
			
			function addClass(element, value) 
			{
				if (!element.className) {
					element.className = value;
				} else {
					var newClassName = element.className;
					newClassName += " ";
					newClassName += value;
					element.className = newClassName;
				}
			}
			
			function CheckFill(element) 
			{
				if (!(jQuery(element).val())) {
					addEmpty(element);
					return false;
				}
				else {
					removeEmpty(element);
					return true;
				}
			}

			function addEmpty(element) {
				jQuery(element).addClass('emptyfield');
				getLabel(element).addClass('emptyfield');
			}

			function removeEmpty(element) {
				jQuery(element).removeClass('emptyfield');
				getLabel(element).removeClass('emptyfield');
			}

			function getLabel(element) {
				return jQuery('label[for="'+element.name+'"]');
			}
			
			function Trim(text) 
			{
				text.value = jQuery.trim(text.value);
			}
			
			function CheckEmail(str) 
			{
				/* Check if regular expressions are supported */
				var supported = 0;
				if (window.RegExp) {
					var tempStr = "a";
					var tempReg = new RegExp(tempStr);
					if (tempReg.test(tempStr)) supported = 1;
				}
				if (!supported) return (str.indexOf(".") > 2) && (str.indexOf("@") > 0);
				
				/* Regular expressions supported */
				var r1 = new RegExp("(@.*@)|(\\.\\.)|(@\\.)|(^\\.)");
				var r2 = new RegExp("^.+\\@(\\[?)[a-zA-Z0-9\\-\\.]+\\.([a-zA-Z]{2,4}|[0-9]{1,4})(\\]?)$");
				return (!r1.test(str) && r2.test(str));
			}
			
			function AddUser() 
			{
				//jQuery("div#submit_button").show();
				var curform = parseInt(jQuery("input[name='curform']").val());
				var maxform = parseInt(jQuery("input[name='multi']").val());
				if (curform >= maxform) {
					alert('<?php echo JText::_('MAX_SIGNUP_REACHED'); ?>\n');
				}
				else {
					jQuery("[id^='formfield']").each(function(i) {
						jQuery(this).hide();
					})
					jQuery("#formfield"+curform).show();
					userlink = '<a href="#" onclick="ShowSingleForm(\'div#formfield'+(curform+1)+'\'); return false;"># '+(curform+1)+'</a><br />';
					jQuery("div#signedusers").append(userlink);
					jQuery("input[name='curform']").val(curform+1);
				}
				updatePrice();
			}
			
			function ShowSingleForm(showform) {
				jQuery("[id^='formfield']").each(function(i) {
					jQuery(this).hide();
				})
				jQuery(showform).show();
			}
			
			function ShowAllUsers(showhide) {
				var curform = parseInt(jQuery("input[name='curform']").val());
				jQuery("[id^='formfield']").each(function(i) {
					if (i < curform) {
						if (showhide) jQuery(this).show();
						else if (!showhide) jQuery(this).hide();
					}
					else {
						jQuery(this).hide();
					}
				})
			}
		</script>
		<?php
	}
	
	/**
	 * adds extra fields from redmember to user object
	 * @param $user object user
	 * @return object user
	 */
	function getRedmemberfields(&$user)
	{
		$db = JFactory::getDBO();
		$user_id = $user->get('id');
		if (!$user_id) {
			return false;
		}
		$query = ' SELECT * FROM #__redmember_users WHERE user_id = '. $db->Quote($user_id);
		$db->setQuery($query, 0, 1);
		$res = $db->loadObject();
		
		if ($res)
		{	
			foreach ($res as $name => $value) 
			{
				if (preg_match('/^rm_/', $name)) {
					$user->set($name, $value);
				}
			}
		}
		return $user;
	}
	
	/**
	 * get array of submission attached to submit_key
	 * 
	 * @param string $submit_key
	 * @return array
	 */
	function getSubmitKeyAnswers($submit_key)
	{		
		$db = JFactory::getDBO(); 
		// get form id and answer id
		$query = 'SELECT form_id, answer_id, submit_key, id '
		       . ' FROM #__rwf_submitters AS s '
		       . ' WHERE submit_key = '.$db->Quote($submit_key)
		       ;
		$db->setQuery($query);
		$submitters = $db->loadObjectList();
				
		$results = array();
		foreach ($submitters as $s)
		{
			$results[] = $this->getAnswers($s->id);
		}
		return $results;		
	}
	
	/**
	 * returns an object with properties sid, submit_key, form_id, fields
	 * 
	 * @param int $sid
	 */
	function getAnswers($sid)
	{
		$sid = intval($sid);
		if (!$sid) {
			return false;
		}
				
		$db = JFactory::getDBO();
		
		// get form id and answer id
		$query = 'SELECT form_id, answer_id, submit_key '
		       . ' FROM #__rwf_submitters AS s '
		       . ' WHERE id = '.$sid
		       ;
		$db->setQuery($query);

		list($form_id, $answer_id, $submit_key) = $db->loadRow();
			
		if (!$form_id || !$answer_id) {
			Jerror::raiseError(0, JText::_('No data'));
		}
			
		// get fields
		$query = 'SELECT id, field FROM #__rwf_fields '
		. ' WHERE form_id = '. $db->Quote($form_id)
		;
		$db->setQuery($query);
		$fields = $db->loadObjectList();
			
		// now get the anwsers
		$query = 'SELECT * FROM #__rwf_forms_'. $form_id
		. ' WHERE id = '. $db->Quote($answer_id)
		;
		$db->setQuery($query);
		$answers = $db->loadObject();

//		// add the answers to fields objects
//		foreach ($fields as $k => $f)
//		{
//			$property = 'field_'. $f->id;
//			if (property_exists($answers, $property)) {
//				$fields[$k]->value = $answers->$property;
//			}
//		}
		
		$result = new stdclass();
		$result->sid        = $sid;
		$result->form_id    = $form_id;
		$result->submit_key = $submit_key;
		$result->fields     = $answers;
		
		return $result;		
	}		
}