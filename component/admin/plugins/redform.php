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

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.plugin.plugin' );

class plgContentRedform extends JPlugin {
	/**
	 * specific redform plugin parameters
	 *
	 * @var JParameter object
	 */
	var $_rwfparams = null;
	
	function plgContentRedform( &$subject, $config = array() )
	{
		parent::__construct( $subject, $config );     
	}	
	
	function onPrepareEvent(&$row, $params = array()) 
	{
		return $this->_process($row, $params);
	}
	
	function onPrepareContent(&$row, $params = array()) 
	{
    return $this->_process($row, array());		
	}
	
	function _process(&$row, $params = array()) 
	{
    JPlugin::loadLanguage( 'plg_content_redform', JPATH_ADMINISTRATOR );
    
		$this->_rwfparams = $params;
				
		/* Regex to find categorypage references */
		$regex = "#{redform}(.*?){/redform}#s";
		
		if (preg_match($regex, $row->text)) 
		{			
			/* Check if there are forms to be started or stopped */
			$this->CheckForms();		
			
			/* Hook up other red components */
			if (isset($row->eventid)) JRequest::setVar('redevent', $row);
			else if (isset($row->competitionid)) JRequest::setVar('redcompetition', $row);

			// load jquery for the form javascript
			if (JRequest::getVar('format', 'html') == 'html') {
				$document = JFactory::getDocument();
				$document->addCustomTag( '<script type="text/javascript" src="'.JURI::root().'administrator/components/com_redform/js/jquery.js"></script>' );
				$document->addCustomTag( '<script type="text/javascript">jQuery.noConflict();</script>' );
				jimport('joomla.html.html');
				JHTML::_('behavior.tooltip');
			}
								
			/* Execute the code */
			$row->text = preg_replace_callback( $regex, array($this, 'FormPage'), $row->text );
		}
		return $row->text;
	}
	
	/**
	 * Create the forms
	 *
	 * $matches[0] = form ID
	 * $matches[1] = Number of sign ups
	 */
	function FormPage ($matches) {
		/* Load the language file as Joomla doesn't do it */
		$language = JFactory::getLanguage();
		$language->load('plg_content_redform');
		
		if (!isset($matches[1])) return false;
		else {
			/* Reset matches result */
			$matches = explode(',', $matches[1]);
			
			/* Get the form details */
			$form = $this->getForm($matches[0]);
	
			/* Check if the user is allowed to access the form */
			$user	= JFactory::getUser();
			if ($user->aid < $form->access) {
				return JText::_('LOGIN_REQUIRED');
			}		
	
			/* Check if the number of sign ups is set, otherwise default to 1 */
			if (!isset($matches[1])) $matches[1] = 1;
			
			if (!isset($form->id)) {
				return JText::_('No active form found');
			}
			else {
				/* Get the field details */
				$fields = $this->getFormFields($form->id);
				
				/* Draw the form form */
				return $this->getFormForm($form, $fields, $matches[1]);
	
			}
		}
	}
	
	/**
	 * Check if there are any forms to be started or stopped
	 */
	function CheckForms() {
		$db = JFactory::getDBO();
		
		/* Check running forms to be stopped */
		$q = "SELECT id 
			FROM #__rwf_forms
			WHERE enddate < NOW()
			AND published = 1
			AND formstarted = 1
			AND formexpires = 1";
		$db->setQuery($q);
		$forms = $db->loadObjectList();
		
		if (count($forms) > 0) {
			foreach ($forms as $id => $form_id) {
				$q = "UPDATE #__rwf_forms
				SET published = 0, formstarted = 0
				WHERE id = ".$form_id->id;
				$db->setQuery($q);
				$db->query();
			}
		}
		
		/* Check not running forms to be started*/
		$q = "SELECT id 
			FROM #__rwf_forms
			WHERE startdate < NOW()
			AND published = 1
			AND formstarted = 0";
		$db->setQuery($q);
		$forms = $db->loadObjectList();
		
		if (count($forms) > 0) {
			foreach ($forms as $id => $form_id) {
				$q = "UPDATE #__rwf_forms
				SET formstarted = 1
				WHERE id = ".$form_id->id;
				$db->setQuery($q);
				$db->query();
			}
		}
	}
	
	function getForm($form_id) {
		$db = JFactory::getDBO();
		
		$q = "SELECT *
			FROM #__rwf_forms f
			WHERE f.id = ".$form_id."
			AND published = 1
			AND formstarted = 1";
		$db->setQuery($q);
		return $db->loadObject();
	}
	
	function getFormFields($form_id) {
		$db = JFactory::getDBO();
		
		$q = "SELECT id, field, validate, tooltip, redmember_field
			FROM #__rwf_fields q
			WHERE published = 1
			AND q.form_id = ".$form_id."
			ORDER BY ordering";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	function getFormValues($field_id) {
		$db = JFactory::getDBO();
		
		$q = "SELECT q.id, value, field_id, fieldtype, listnames
			FROM #__rwf_values q
			LEFT JOIN #__rwf_mailinglists m
			ON q.id = m.id
			WHERE published = 1
			AND q.field_id = ".$field_id."
			ORDER BY ordering";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	function replace_accents($str) {
	  $str = htmlentities($str, ENT_COMPAT, "UTF-8");
	  $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|elig|slash|ring);/','$1',$str);
	  return html_entity_decode($str);
	}
	
	function getFormForm($form, $fields, $multi=1) 
	{
		$mainframe = & JFactory::getApplication();
		$document  = &JFactory::getDocument();
		$pdf = false;
    
		if (JRequest::getVar('format', 'html') == 'html') 
		{		
		  // css
      $document->addStyleSheet(JURI::base().'components/com_redform/assets/css/tooltip.css');
  		
  		// custom tooltip
  		$toolTipArray = array('className'=>'redformtip'.$form->classname);
      JHTML::_('behavior.tooltip', '.hasTipField', $toolTipArray);
      
  		$this->JsCheck();
		}
		else if (JRequest::getVar('format', 'html') == 'pdf')
		{
			$pdfform = JRequest::getVar('pdfform');
			$pdf = true;		  
		}
		
		/* Check if there are any answers to be filled in (already submitted)*/
		/* This is an array starting with 0 */
		if (!isset($this->_rwfparams['answers'])) {
			$answers = JRequest::getVar('answers', false);
		}
		else {
			$answers = $this->_rwfparams['answers'];
		}
		if (!isset($this->_rwfparams['submitter_id'])) {
			$submitter_id = JRequest::getInt('submitter_id', 0);
		}
		else {
			$submitter_id = $this->_rwfparams['submitter_id'];
		}
		
		/* Get the user details */
		$user = JFactory::getUser();
		
		// redmember integration: pull extra fields
		if ($user->get('id') && file_exists(JPATH_ROOT.DS.'components'.DS.'com_redmember')) {
			$this->getRedmemberfields($user);
		}
		
		/* Stuff to find and replace */
		$find = array(' ', '_', '-', '.', '/', '&', ';', ':', '?', '!', ',');
		$replace = '';
		
		/* Set the css class */
		if (!$pdf) 
		{
			$form->classname = strtolower($this->replace_accents(str_replace($find, $replace,$form->classname)));
			$html = '<div id="redform_'.$form->classname.'">';
			if ($form->showname) {
				$html .= '<div id="formname">'.$form->formname.'</div>';
			}
			
			$uri = JURI::getInstance();
			
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
			
			$html .= '<form action="'.JRoute::_('index.php?option=com_redform').'" method="post" name="adminForm" enctype="multipart/form-data" onSubmit="return CheckSubmit();">';			
		}
		$footnote = false;
		
		// user can submit multiple forms only if he is logged => display notice
		if (!$pdf) 
		{
			if ($multi > 1 && $user->id == 0) 
			{
				$html .= '<div id="needlogin">'.JText::_('LOGIN_BEFORE_MULTI_SIGNUP').'</div>';
				$multi = 1;
			}
		}
		else {
		  $multi = 1;
		}
		
		/* Set display type */
		if ($multi == 1) {
		  $display = 'block';
		}
		else $display = 'none';
		
		if (!$pdf) 
		{
			if ($multi > 1) 
			{
				if (!$answers) {
				  $html .= '<div id="signupuser"><a href="#" onclick="AddUser()">'.JText::_('SIGN_UP_USER').'</a></div>';
				}
				
				$html .= '<div id="signedusers" style="float: right">
					<a href="#" onclick="ShowAllUsers(true)">'.JText::_('SHOW_ALL_USERS').'</a>
					<br />
					<a href="#" onclick="ShowAllUsers(false)">'.JText::_('HIDE_ALL_USERS').'</a>
					<br />'.JText::_('Signed up:').'<br />';
				
				if ($answers) 
				{
					for ($signup = 1; $signup <= count($answers); $signup++) {
						$html .= '<a href="#" onclick="ShowSingleForm(\'div#formfield'.$signup.'\'); return false;">User '.$signup.'</a><br />';
					}
				}
				$html .= '</div>';
			}
		}
		
		if ($answers) 
		{
			$multi = count($answers);
			if ($multi > 1) { 
				$html .= '<div id="event_notify"><input type="checkbox" name="notify_attendants" id="notify_attendants" value="1">'.JText::_('EVENT_NOTIFY').'</input></div>';
			}
		}
		
		/* Loop through here for as many forms there are */
		for ($signup = 1; $signup <= $multi; $signup++) 
		{
			/* Make a collapsable box */
			if (!$pdf) {
				$html .= '<div id="formfield'.$signup.'" class="formbox" style="display: '.$display.';">';
			}
			else {
				if ($signup > 1) $pdfform->Addpage('P');
				$pdfform->Cell(0, 10, JText::_('ATTENDEE').' '.$signup, 0, 1, 'L');
			}
			$footnote = false;
			
			if ($answers && $multi > 1) {
				$html .= '<div class="confirmbox"><input type="checkbox" name="confirm[]" value="'.$answers[($signup-1)]->id.'" checked="checked" />'.JText::_('INCLUDE_REGISTRATION').'</div>';
			}
			else if ($answers) {
				$html .= '<input type="hidden" name="confirm[]" value="'.$answers[($signup-1)]->id.'" />';	
			}
			foreach ($fields as $key => $field) 
			{
				$field->cssfield = strtolower($this->replace_accents(str_replace($find, $replace, $field->field)));
				if (!$pdf) $html .= '<div id="fieldline_'.$field->cssfield.'" class="fieldline">';
				
				$values = $this->getFormValues($field->id);
				
				if (count($values) > 0) {

					if ($values[0]->fieldtype == 'info') {
						$html .= '<div class="infofield">' . $values[0]->value . '</div>';
						$html .= '</div>';						
						continue;
					}
					
					if (!$pdf) {
						$html .= '<div id="field_'.$field->cssfield.'">'.$field->field;
						if ($field->validate) {
							$html .= '<sup>'.JText::_('REQUIRED_FIELD_MARKER').'</sup>';
							$footnote = true;
						}
						if (strlen($field->tooltip) > 0) {
              $img = JHTML::image(JURI::root().'/includes/js/ThemeOffice/tooltip.png', JText::_('ToolTip'));
							$html .= ' <span class="editlinktip hasTipField" title="'.$field->field.'::'.$field->tooltip.'" style="text-decoration: none; color: #333;">'. $img .'</span>';
						}
						$html .='</div>';
					}
					else {
						$pdfform->Cell(0, 10, $field->field, 0, 1, 'L');
					}
					$radio = '';
					$textarea = '';
					$textfield = '';
					$checkbox = '';
					$select = '';
					$fileupload = '';
          $wysiwyg = '';
					$finalid = end(array_keys($values));
					$cleanfield = 'field_'. $field->id;
					foreach ($values as $id => $value) {
						switch ($value->fieldtype) {
							case 'radio':
								if (!$pdf) {
									$radio .= "<div class=\"field".$value->fieldtype."\"><input class=\"".$form->classname." ";
									if ($field->validate) $radio .= "validate";
									$radio .= "\"";
									if ($answers && stristr($answers[($signup-1)]->$cleanfield, $value->value)) {
										$radio .= ' checked="checked"';
									}
									else if ($user->get($field->redmember_field)) 
									{
										$fvalues = explode(',', $user->get($field->redmember_field));
										if (in_array($value->value, $fvalues)) {
											$radio .= ' checked="checked"';
										}
									}
									$radio .= " type=\"radio\" name=\"field".$field->id.'.'.$signup."[radio][]\" value=\"".$value->id."\"/>".$value->value."</div>\n";
								}
								else {
									$pdfform->setX($pdfform->getX()+2);
									$pdfform->Circle($pdfform->getX(), $pdfform->getY(), 2);
									$pdfform->setXY($pdfform->getX()+3, $pdfform->getY()-5);
									$pdfform->Write(10, $value->value);
									$pdfform->Ln();
								}
								break;
								
							case 'textarea':
								if (!$pdf) {
									$textarea .= "<div class=\"field".$value->fieldtype."\"><textarea class=\"".$form->classname." ";
									if ($field->validate) $textarea .= "validate";
									$textarea .= "\" name=\"field".$field->id.'.'.$signup."[textarea]\">";
									if ($answers && isset($answers[($signup-1)]->$cleanfield)) {
										$textarea .= $answers[($signup-1)]->$cleanfield;
									}
									else if ($user->get($field->redmember_field)) {
										$textarea .= $user->get($field->redmember_field);
									}
									$textarea .= "</textarea></div>\n";
								}
								else {
									$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 100, 15);
									$pdfform->Ln();
								}
								break;								
																
              case 'wysiwyg':
                if (!$pdf) {
                  $content = '';
									if ($answers && isset($answers[($signup-1)]->$cleanfield)) {
										$content = $answers[($signup-1)]->$cleanfield;
									}
									else if ($user->get($field->redmember_field)) {
										$content = $user->get($field->redmember_field);
									}
                  $editor = & JFactory::getEditor();
                  
                  $wysiwyg .= "<div class=\"field".$value->fieldtype."\">";
                  // Cannot have buttons, it triggers an error with tinymce for unregistered users
                  $wysiwyg .= $editor->display( "field".$field->id.'.'.$signup."[wysiwyg]", $content, '100%;', '200', '75', '20', false ) ;
                  $wysiwyg .= "</div>\n";
                }
                else {
                  $pdfform->Rect($pdfform->getX(), $pdfform->getY(), 100, 15);
                  $pdfform->Ln();
                }
                break;
                
							case 'email':
								if (!$pdf) {
									$textfield .= "<div class=\"field".$value->fieldtype."\"><input class=\"".$form->classname." ";
									if ($field->validate) $textfield .= "validate";
									$textfield .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[email][]\" value=\"";
									if ($answers && isset($answers[($signup-1)]->$cleanfield)) {
										$textfield .= $answers[($signup-1)]->$cleanfield;
									}
									else if ($user->get($field->redmember_field)) {
										$textfield .= $user->get($field->redmember_field);
									}
									else if ($signup == 1 && $user->email) {
										$textfield .= $user->email;
									}
									$textfield .= "\" /></div>\n";
									/* E-mail field let's see */
									if (strlen($value->listnames) > 0) {
										$listnames = explode(";", $value->listnames);
										if (count($listnames) > 0) {
											$textfield .= '<div id="signuptitle">'.JText::_('SIGN_UP_MAILINGLIST').'</div>';
											$textfield .= "<div class=\"field".$value->fieldtype."_listnames\">";
											foreach ($listnames AS $listkey => $listname) {
												$textfield .= "<div class=\"field_".$listkey."\"><input class=\"".$form->classname." ";
												$textfield .= "\" type=\"checkbox\" name=\"field".$field->id.'.'.$signup."[email][listnames][]\" value=\"".$listname."\" />".$listname.'</div>';
											}
											$textfield .= "</div>\n";
										}
									}
								}
								else {
									$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
									$pdfform->Ln();
								}
								break;
							case 'fullname':
								if (!$pdf) {
									$textfield .= "<div class=\"field".$value->fieldtype."\"><input class=\"".$form->classname." ";
									if ($field->validate) $textfield .= "validate";
									$textfield .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[fullname][]\" value=\"";
									if ($answers && isset($answers[($signup-1)]->$cleanfield)) {
										$textfield .= $answers[($signup-1)]->$cleanfield;
									}
									else if ($user->get($field->redmember_field)) {
										$textfield .= $user->get($field->redmember_field);
									}
									else if ($signup == 1 && $user->name) {
										$textfield .= $user->name;
									}
									$textfield .= "\" /></div>\n";
								}
								else {
									$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
									$pdfform->Ln();
								}
								break;
							case 'username':
								if (!$pdf) {
									$textfield .= "<div class=\"field".$value->fieldtype."\"><input class=\"".$form->classname." ";
									if ($field->validate) $textfield .= "validate";
									$textfield .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[username][]\" value=\"";
									if ($answers && isset($answers[($signup-1)]->$cleanfield)) {
										$textfield .= $answers[($signup-1)]->$cleanfield;
									}
									else if ($user->get($field->redmember_field)) {
										$textfield .= $user->get($field->redmember_field);
									}
									else if ($signup == 1 && $user->username) {
										$textfield .= $user->username;
									}
									$textfield .= "\" /></div>\n";
								}
								else {
									$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
									$pdfform->Ln();
								}
								break;
							case 'textfield':
								if (!$pdf) {
									$textfield .= "<div class=\"field".$value->fieldtype."\"><input class=\"".$form->classname." ";
									if ($field->validate) $textfield .= "validate";
									$textfield .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[text][]\" value=\"";
									if ($answers && isset($answers[($signup-1)]->$cleanfield)) {
										$textfield .= $answers[($signup-1)]->$cleanfield;
									}
									else if ($user->get($field->redmember_field)) {
										$textfield .= $user->get($field->redmember_field);
									}
									$textfield .= "\" /></div>\n";
								}
								else {
									$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
									$pdfform->Ln();
								}
								break;
							case 'checkbox':
								if (!$pdf) {
									$checkbox .= "<div class=\"field".$value->fieldtype."\"><input class=\"".$form->classname." ";
									if ($field->validate) $checkbox .= "validate";
									$checkbox .= "\"";
									if ($answers && stristr($answers[($signup-1)]->$cleanfield, $value->value)) {
										$checkbox .= ' checked="checked"';
									}				
									else if ($user->get($field->redmember_field)) 
									{
										$fvalues = explode(',', $user->get($field->redmember_field));
										if (in_array($value->value, $fvalues)) {
											$checkbox .= ' checked="checked"';
										}
									}
									$checkbox .= " type=\"checkbox\" name=\"field".$field->id.'.'.$signup."[checkbox][]\" value=\"".$value->value."\" />".$value->value."</div>\n";
								}
								else {
									$pdfform->Rect($pdfform->getX(), $pdfform->getY()+2, 5, 5);
									$pdfform->setX($pdfform->getX()+5);
									$pdfform->Write(10, $value->value);
									$pdfform->Ln();
								}
								break;
							case 'select':
								if ($select == '') $select = "<select name=\"field".$field->id.'.'.$signup."[select][]\">";
								$select .= "<option value=\"".$value->value."\"";
								if ($answers && isset($answers[($signup-1)]->$cleanfield)) {
									$select .= ' selected="selected"';
								}
								else if ($user->get($field->redmember_field) == $value->value) {
									$select .= ' selected="selected"';
								}
								$select .= " >".$value->value."</option>";
								if ($finalid == $id) $select .= '</select>';
								break;
								
							case 'multiselect':
								if ($select == '') $select = "<select name=\"field".$field->id.'.'.$signup."[multiselect][]\" multiple>";
								$select .= "<option value=\"".$value->value."\"";
								if ($answers && isset($answers[($signup-1)]->$cleanfield)) {
									$select .= ' selected="selected"';
								}
								else if ($user->get($field->redmember_field)) 
								{
									$fvalues = explode(',', $user->get($field->redmember_field));
									if (in_array($value->value, $fvalues)) {
										$select .= ' selected="selected"';
									}
								}
								$select .= " >".$value->value."</option>";
								if ($finalid == $id) $select .= '</select>';
								break;
              case 'recipients':
                if ($select == '') $select = "<select name=\"field".$field->id.'.'.$signup."[recipients][]\" multiple>";
                $select .= "<option value=\"".$value->value."\"";
                if ($answers && isset($answers[($signup-1)]->$cleanfield)) $select .= ' selected="selected"';
                $select .= " >".$value->value."</option>";
                if ($finalid == $id) $select .= '</select>';
                break;
							case 'fileupload':
								if ($submitter_id == 0) {
								$fileupload .= "<input type=\"file\" name=\"field".$field->id.'.'.$signup."[fileupload][]\" size=\"40\" class=\"fileupload\" id=\"fileupload_".$field->cssfield."\"/>";
								}
								break;
						}
					}
					
					if (!$pdf) {
						if (!empty($radio)) $html .= $radio;
						else if (!empty($textfield)) $html .= $textfield;
						else if (!empty($textarea)) $html .= $textarea;
						else if (!empty($checkbox)) $html .= $checkbox;
						else if (!empty($select)) $html .= $select;
						else if (!empty($fileupload)) $html .= $fileupload;
            else if (!empty($wysiwyg)) $html .= $wysiwyg;
					}
				}
				if (!$pdf) $html .= '</div>';
			}
			/* Close collapsable box */
			if (!$pdf) $html .= '</div>';
			else {
				if ($footnote) $pdfform->Write(10, JText::_('VALIDATE_FOOTNOTE'));
			}
		}
		
		if (!$pdf) {
			/* Add any redEVENT values */
			$redevent = JRequest::getVar('redevent', false);
			
			if ($redevent) {
				$html .= '<input type="hidden" name="event_task" value="'.$redevent->task.'" />';
				$html .= '<input type="hidden" name="event_id" value="'.$redevent->eventid.'" />';
				$html .= '<input type="hidden" name="xref" value="'.JRequest::getInt('xref').'" />';
			}
			else if (JRequest::getVar('redform_edit') || JRequest::getVar('redform_add')) {
				$html .= '<input type="hidden" name="xref" value="'.JRequest::getInt('xref').'" />';
			}
			
			/* Add any redCOMPETITION values */
			$redcompetition = JRequest::getVar('redcompetition', false);
			
			if ($redcompetition) {
				$html .= '<input type="hidden" name="competition_task" value="'.$redcompetition->task.'" />';
				$html .= '<input type="hidden" name="competition_id" value="'.$redcompetition->competitionid.'" />';
			}
			
			/* Add the captcha */
			if ($form->captchaactive && $submitter_id == 0) {
			$html .= '<div id="redformcaptcha"><img src="index.php?option=com_redform&task=displaycaptcha&controller=redform">';
			$html .= ' '.JHTML::tooltip(JText::_('CAPTCHA_TOOLTIP'), JText::_('CAPTCHA'), 'tooltip.png', '', '', false).'</div>';
				$html .= '<div id="redformcaptchaword"><input type="text" name="captchaword"></div>';
			}
			
			/* Get the user details form */
			if (!$answers && !JRequest::getVar('redform_edit') &&  !JRequest::getVar('redform_add')) {
				$html .= '<div id="submit_button" style="display: '.$display.';"><input type="submit" id="regularsubmit" name="submit" value="'.JText::_('Submit').'" />';
				if ( JRequest::getInt('xref', false) 
				     && isset($this->_rwfparams['show_submission_type_webform_formal_offer']) 
				     && $this->_rwfparams['show_submission_type_webform_formal_offer'] ) {
				  $html .= '<input type="submit" name="submit" id="printsubmit" value="'.JText::_('SUBMIT_AND_PRINT').'" />';
				}
				$html .= '</div>';
			}
			else if (!JRequest::getVar('redform_edit') &&  !JRequest::getVar('redform_add')) {
				$html .= '<div id="confirmbutton">';
				$html .= '<input type="submit" id="confirmreg" value="'.JText::_('EVENT_SUBMIT').'" name="submit[confirmreg]" />';
				$html .= '<input type="submit" id="cancelreg" value="'.JText::_('EVENT_CANCEL').'" name="submit[cancelreg]" />';
				$html .= '</div>';
				$html .= '<input type="hidden" name="submit_key" value="'.JRequest::getVar('submit_key').'" />';
			}
			$html .= '<input type="hidden" name="option" value="com_redform" />';
			$html .= '<input type="hidden" name="productid" value="'.JRequest::getInt('productid', 0).'" />';
			$html .= '<input type="hidden" name="Itemid" value="'.JRequest::getInt('Itemid', 1).'" />';
			$html .= '<input type="hidden" name="task" value="save" />';
			if ($submitter_id > 0) {
				$html .= '<input type="hidden" name="submitter_id" value="'.$submitter_id.'" />';
				if ($redevent->task == 'manageredit' || $redevent->task == 'edit') {
					$html .= '<input type="hidden" name="controller" value="redform" />';
				}
			}
			else $html .= '<input type="hidden" name="controller" value="redform" />';
			
			if (JRequest::getVar('redform_edit') || JRequest::getVar('redform_add')) {
				$html .= '<input type="hidden" name="controller" value="submitters" />';
			}
			
			if (JRequest::getVar('redform_edit')) {
				$html .= '<input type="hidden" name="event_task" value="review" />';
			}
			
			$html .= '<input type="hidden" name="curform" value="';
			if ($answers) $html .= count($answers)+1;
			else $html .= '1';
			$html .= '" />';
			$html .= '<input type="hidden" name="form_id" value="'.$form->id.'" />';
			$html .= '<input type="hidden" name="multi" value="'.$multi.'" />';
			if (JRequest::getVar('close_form', true)) $html .= '</form>';
		$html .= '</div>';
		if ($footnote) $html .= '<div id="fieldline_'.$field->cssfield.'" class="fieldline"><div id="validate_footnote">'.JText::_('VALIDATE_FOOTNOTE').'</div></div>';
		}
			
		if ($pdf) JRequest::setVar('pdfform', $pdfform);
		else return $html;
	}
	
	function getProductinfo() {
		$db = JFactory::getDBO();
		$q = "SELECT product_full_image, product_name FROM #__vm_product WHERE product_id = ".JRequest::getInt('productid');
		$db->setQuery($q);
		return $db->loadObject();
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
	
	function JsCheck() {
		/* Create some dynamic JS */
		?>
		<script type="text/javascript">
			function CheckSubmit() {
				if (document.adminForm.task.value == 'cancel') return true;
				var msg = '';
				var result = true;
				var newclass = 'emptyfield';
				var checkboxmsg = false;
				var radiomsg = false;

				// only check the form that were activated by the user
				var forms = jQuery('.formbox');
				var nb_active;
				// curform starts at 2 for first form in multiple signup... but 1 for single
				if (jQuery("input[name='multi']")[0].value > 1) {
				  var mycu = jQuery("input[name='curform']")[0];
					nb_active = mycu.value - 1;
				}
				else {
					nb_active = 1;
				}

				for (var j = 0 ; j < nb_active ; j++)
				{
					// get the input data of the form
					var formelements = jQuery(forms[j]).find(':input');

  				for(i=0; i < formelements.length; i++) {
  					var check_element = formelements[i];
  					/* Check field type */
  					/* Fullname */
  					if (check_element.name.match("fullname") && check_element.className.match("validate")) {
  						var fullresult = CheckFill(check_element);
  						if (result) result = fullresult;
  					}
  					
  					/* Text field */
  					if (check_element.name.match("text") && check_element.className.match("validate")) {
  						var textresult = CheckFill(check_element);
  						if (result) result = textresult;
  					}
  					
  					/* Textarea field */
  					if (check_element.name.match("textarea") && check_element.className.match("validate")) {
  						var textarearesult = CheckFill(check_element);
  						if (result) result = textarearesult;
  					}
  					
  					/* Username field */
  					if (check_element.name.match("username") && check_element.className.match("validate")) {
  						var usernameresult = CheckFill(check_element);
  						if (result) result = usernameresult;
  					}
  					
  					/* E-mail */
  					if (check_element.name.match("email") && check_element.className.match("validate")) {
  						if (CheckFill(check_element)) {
  							if (!CheckEmail(check_element.value)) {
  								msg = msg + '<?php echo JText::_('No valid e-mail address'); ?>\n';
  								if (result) result = false;
  							}
  						}
  						else {
  							msg = msg + '<?php echo JText::_('E-mail address is empty'); ?>\n';
  							if (result) result = false;
  						}
  					}
  					
		        /* Radio buttons */
	          if (check_element.name.match("radio") && check_element.className.match("validate")) {
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
	          if (check_element.name.match("checkbox") && check_element.className.match("validate")) {
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
					if (textresult == false || fullresult == false || textarearesult == false) msg = msg + '<?php echo JText::_('Text field is empty'); ?>\n';
					if (usernameresult == false) msg = msg + '<?php echo JText::_('Username field is empty'); ?>\n';
					if (radiomsg) msg = msg + '<?php echo JText::_('No radiobox has been chosen'); ?>\n';
					if (checkboxmsg) msg = msg + '<?php echo JText::_('No checkbox has been chosen'); ?>\n';
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
			
			function addClass(element, value) {
				if (!element.className) {
					element.className = value;
				} else {
					var newClassName = element.className;
					newClassName += " ";
					newClassName += value;
					element.className = newClassName;
				}
			}
			
			function CheckFill(element) {
				Trim(element);
				if (element.value.length == 0) {
					addClass(element, 'emptyfield');
					return false;
				}
				else return true;
			}
			
			function Trim(text) {
				while(text.value.charAt(0)==' ')
					text.value=text.value.substring(1,text.value.length )
						while(text.value.charAt(text.value.length-1)==' ')
							text.value=text.value.substring(0,text.value.length-1)
			}
			
			function CheckEmail(str) {
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
			
			function AddUser() {
				jQuery("div#submit_button").show();
				var curform = parseInt(jQuery("input[name='curform']").val());
				var maxform = parseInt(jQuery("input[name='multi']").val());
				if (curform > maxform) {
					alert('<?php echo JText::_('MAX_SIGNUP_REACHED'); ?>\n');
				}
				else {
					jQuery("[id^='formfield']").each(function(i) {
						jQuery(this).hide();
					})
					jQuery("#formfield"+curform).show();
					userlink = '<a href="#" onclick="ShowSingleForm(\'div#formfield'+curform+'\'); return false;">User '+curform+'</a><br />';
					jQuery("div#signedusers").append(userlink);
					jQuery("input[name='curform']").val(curform+1);
				}
			}
			
			function ShowSingleForm(showform) {
				jQuery("[id^='formfield']").each(function(i) {
					jQuery(this).hide();
				})
				jQuery(showform).show();
			}
			
			function ShowAllUsers(showhide) {
				var curform = parseInt(jQuery("input[name='curform']").val()) - 1;
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
}
?>