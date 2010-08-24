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
	
	var $_rfcore = null;
	
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
		if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'redform.core.php')) {
			JError::raiseWarning(0, JText::_('REDFORM_COMPONENT_REQUIRED_FOR_REDFORM_PLUGIN'));
			return false;
		}
		include_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'redform.core.php');
		$this->_rfcore = new RedFormCore();
		
    JPlugin::loadLanguage( 'plg_content_redform', JPATH_ADMINISTRATOR );
    
		$this->_rwfparams = $params;
				
		/* Regex to find categorypage references */
		$regex = "#{redform}(.*?){/redform}#s";
		
		if (preg_match($regex, $row->text, $matches)) 
		{						
			/* Hook up other red components */
			if (isset($row->eventid)) JRequest::setVar('redevent', $row);
			else if (isset($row->competitionid)) JRequest::setVar('redcompetition', $row);

			// load jquery for the form javascript
			if (JRequest::getVar('format', 'html') == 'html') {
				JHTML::_('behavior.tooltip');
				jimport('joomla.html.html');
				$document = JFactory::getDocument();
				$document->addScript(JURI::root().'components/com_redform/assets/js/jquery-1.4.min.js' );
				//$document->addCustomTag( '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.js"></script>' ); // for debugging...
				$document->addScriptDeclaration( 'jQuery.noConflict();' );
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
			$check = $this->_checkFormActive($form);
			if (!($check === true)) {
				return $check;
			}
	
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
	 * returns form object
	 * 
	 * @param int $form_id
	 * @return object
	 */
	function getForm($form_id) 
	{
		$db = JFactory::getDBO();
		
		$q = ' SELECT f.* '
		   . ' FROM #__rwf_forms AS f '
		   . ' WHERE f.id = '.$db->Quote($form_id)
		   . '   AND published = 1 '
		   ;
		$db->setQuery($q);
		return $db->loadObject();
	}
	
	/**
	 * checks if the form is active
	 * 
	 * @param object $form
	 * @return true if active, error message if not
	 */
	function _checkFormActive($form)
	{
		if (strtotime($form->startdate) > time()) {
			return JText::_('REDFORM_FORM_NOT_STARTED');
		}
		else if ($form->formexpires && strtotime($form->enddate) < time()) {
			return JText::_('REDFORM_FORM_EXPIRED');
		}
		return true;
	}
	
	function getFormFields($form_id) 
	{
		$db = JFactory::getDBO();
		
		$q = ' SELECT f.id, f.field, f.validate, f.tooltip, f.redmember_field, f.fieldtype, f.params, m.listnames '
		   . ' FROM #__rwf_fields AS f '
		   . ' LEFT JOIN #__rwf_mailinglists AS m ON f.id = m.field_id'
		   . ' WHERE f.published = 1 '
		   . ' AND f.form_id = '.$form_id
		   . ' ORDER BY f.ordering'
		   ;
		$db->setQuery($q);
		$fields = $db->loadObjectList();
		
		foreach ($fields as $k => $field)
		{
			$paramsdefs = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redform' . DS . 'models' . DS . 'field_'.$field->fieldtype.'.xml';
			if (!empty($field->params) && file_exists($paramsdefs))
			{
				$fields[$k]->parameters = new JParameter( $field->params, $paramsdefs );
			}
			else {
				$fields[$k]->parameters = new JRegistry();
			}
		}
		return $fields;
	}
	
	function getFormValues($field_id) 
	{
		$db = JFactory::getDBO();
		
		$q = "SELECT q.id, value, field_id, price 
			FROM #__rwf_values q
			WHERE published = 1
			AND q.field_id = ".$field_id."
			ORDER BY ordering";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	function replace_accents($str) 
	{
	  $str = htmlentities($str, ENT_COMPAT, "UTF-8");
	  $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|elig|slash|ring);/','$1',$str);
	  return html_entity_decode($str);
	}
	
	function getFormForm($form, $fields, $multi=1) 
	{    
		if (JRequest::getVar('format', 'html') == 'html') 
		{
			return $this->getHTMLForm($form, $fields, $multi);
		}
		else if (JRequest::getVar('format', 'html') == 'pdf')
		{
			JRequest::setVar('pdfform', $this->getPDFForm($form, $fields));
		}
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
		
	function getHTMLForm($form, $fields, $multi=1)
	{
		$uri       = JURI::getInstance();
		$user      = JFactory::getUser();
		$document  = &JFactory::getDocument();
				
	  // css
    $document->addStyleSheet(JURI::base().'components/com_redform/assets/css/tooltip.css');
    $document->addStyleSheet(JURI::base().'plugins/content/redform/plgredform.css');
  	
  	// custom tooltip
  	$toolTipArray = array('className'=>'redformtip'.$form->classname);
    JHTML::_('behavior.tooltip', '.hasTipField', $toolTipArray);

    // currency for javascript
    $js = "var currency = \"".$form->currency."\";\n";
    $document->addScriptDeclaration($js);
    
  	$this->_rfcore->JsCheck();
  	$this->_rfcore->jsPrice();
		
		// redmember integration: pull extra fields
		if ($user->get('id') && file_exists(JPATH_ROOT.DS.'components'.DS.'com_redmember')) {
			$this->getRedmemberfields($user);
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
		
		/* Stuff to find and replace */
		$find = array(' ', '_', '-', '.', '/', '&', ';', ':', '?', '!', ',');
		$replace = '';
		
		$form->classname = strtolower($this->replace_accents(str_replace($find, $replace,$form->classname)));
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
			
		$html .= '<form action="'.JRoute::_('index.php?option=com_redform').'" method="post" name="redform" enctype="multipart/form-data" onsubmit="return CheckSubmit();">';

		$footnote = false;

		if ($multi > 1 && $user->id == 0)
		{
			$html .= '<div id="needlogin">'.JText::_('LOGIN_BEFORE_MULTI_SIGNUP').'</div>';
			$multi = 1;
		}
			
		if ($multi > 1)
		{
			if (!$answers) {
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
			/* Make a collapsable box */
			$html .= '<div id="formfield'.$signup.'" class="formbox" style="display: '.($signup == 1 ? 'block' : 'none').';">';
			if ($multi > 1) {
				$html .= '<fieldset><legend>'.JText::sprintf('REDFORM_FIELDSET_SIGNUP_NB', $signup).'</legend>';
			}
			$footnote = false;
				
			if ($answers && $multi > 1) {
				$html .= '<div class="confirmbox"><input type="checkbox" name="confirm[]" value="'.$answers[($signup-1)]->id.'" checked="checked" />'.JText::_('INCLUDE_REGISTRATION').'</div>';
			}
			else if ($answers) {
				$html .= '<input type="hidden" name="confirm[]" value="'.$answers[($signup-1)]->id.'" />';
			}
		
			if ($form->activatepayment && isset($this->_rwfparams['eventdetails']) && $this->_rwfparams['eventdetails']->course_price) {
				$html .= '<div class="eventprice" price="'.$this->_rwfparams['eventdetails']->course_price.'">'.JText::_('Registration price').': '.$form->currency.' '.$this->_rwfparams['eventdetails']->course_price.'</div>';
			}
							
			if (isset($this->_rwfparams['extrafields']) && count($this->_rwfparams['extrafields']))
			{
				foreach ($this->_rwfparams['extrafields'] as $field)
				{
					$html .= '<div class="fieldline">';
					$html .= '<div class="label">'.$field['label'].'</div>';
					$html .= '<div class="field">'.$field['field'].'</div>';
					$html .= '</div>';
				}
			}

			foreach ($fields as $key => $field)
			{
				$field->cssfield = strtolower($this->replace_accents(str_replace($find, $replace, $field->field)));
				$html .= '<div id="fieldline_'.$field->cssfield.'" class="fieldline">';

				$values = $this->getFormValues($field->id);

				if ($field->fieldtype == 'info' && count($values))
				{
					$html .= '<div class="infofield">' . $values[0]->value . '</div>';
					$html .= '</div>';
					continue;
				}
					
				$cleanfield = 'field_'. $field->id;
				$element = "<div class=\"field".$field->fieldtype."\">";
				$label = '';
				
				switch ($field->fieldtype)
				{
					case 'radio':
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[radio][]">'.$field->field.'</label></div>';
						$element .= '<div class="fieldoptions">';		
						foreach ($values as $id => $value)
						{
							$element .= '<div class="fieldoption">';
							$element .= "<input class=\"".$form->classname.$field->parameters->get('class','')." ";
							if ($field->validate) $element .= "required";
							$element .= "\"";
							if ($answers)
							{
								if (in_array($value->value, explode('~~~', $answers[($signup-1)]->$cleanfield))) {
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[textarea]">'.$field->field.'</label></div>';
						$element .= '<textarea class="'.$form->classname.$field->parameters->get('class','');
						if ($field->validate) $element .= ' required';
						$element .= '" name="field'.$field->id.'.'.$signup.'[textarea]"';
						$element .= ' cols="'.$field->parameters->get('cols',25).'" rows="'.$field->parameters->get('rows',6).'"';
						$element .= ">";
						if ($answers)
						{
							if (isset($answers[($signup-1)]->$cleanfield)) {
								$element .= $answers[($signup-1)]->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$element .= $user->get($field->redmember_field);
						}
						$element .= "</textarea>\n";
						break;
	
					case 'wysiwyg':
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[wysiwyg]">'.$field->field.'</label></div>';
						$content = '';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->$cleanfield)) {
								$element .= $answers[($signup-1)]->$cleanfield;
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[email][]">'.$field->field.'</label></div>';
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
							if (isset($answers[($signup-1)]->$cleanfield)) {
								$element .= $answers[($signup-1)]->$cleanfield;
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
						if (strlen($field->listnames) > 0)
						{
							$listnames = explode(";", $field->listnames);
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
						$element .= "</div>\n";
						$element .= "</div>\n";
						break;
	
					case 'fullname':
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[fullname][]">'.$field->field.'</label></div>';
						$element .= "<input class=\"".$form->classname.$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[fullname][]\"";
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						$element .= ' value="';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->$cleanfield)) {
								$element .= $answers[($signup-1)]->$cleanfield;
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[username][]">'.$field->field.'</label></div>';
						$element .= "<input class=\"".$form->classname.$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[username][]\"";
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						$element .= ' value="';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->$cleanfield)) {
								$element .= $answers[($signup-1)]->$cleanfield;
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[text][]">'.$field->field.'</label></div>';
						$element .= "<input class=\"".$form->classname.$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[text][]\"";
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						$element .= ' value="';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->$cleanfield)) {
								$element .= $answers[($signup-1)]->$cleanfield;
							}
						}
						else if ($user->get($field->redmember_field)) {
							$element .= $user->get($field->redmember_field);
						}
						$element .= "\" />\n";
						break;
	
					case 'date':
						JHTML::_('behavior.calendar');
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[date]">'.$field->field.'</label></div>';
						if ($answers)
						{
							if (isset($answers[($signup-1)]->$cleanfield)) {
								$val = $answers[($signup-1)]->$cleanfield;
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[price][]">'.$field->field.'</label></div>';
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
								if (isset($answers[($signup-1)]->$cleanfield)) {
									$element .= $answers[($signup-1)]->$cleanfield;
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[checkbox][]">'.$field->field.'</label></div>';
						$element .= '<div class="fieldoptions">';						
						foreach ($values as $id => $value)
						{
							$element .= '<div class="fieldoption">';
							$element .= "<input class=\"".$form->classname.$field->parameters->get('class','')." ";
							if ($field->validate) $element .= "required";
							$element .= "\"";
							if ($answers)
							{
								if (in_array($value->value, explode('~~~', $answers[($signup-1)]->$cleanfield))) {
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[select][]">'.$field->field.'</label></div>';
						$element .= "<select name=\"field".$field->id.'.'.$signup."[select][]\" class=\"".$form->classname.$field->parameters->get('class','')."\">";
						foreach ($values as $id => $value)
						{
							$element .= "<option value=\"".$value->value."\"";
							if ($answers) 
							{
								if ($answers[($signup-1)]->$cleanfield == $value->value) {
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[multiselect][]">'.$field->field.'</label></div>';
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
								if (in_array($value->value, explode('~~~', $answers[($signup-1)]->$cleanfield))) {
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[recipients][]">'.$field->field.'</label>/div>';
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
								if (in_array($value->value, explode('~~~', $answers[($signup-1)]->$cleanfield))) {
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
						$label = '<div id="field_'.$field->cssfield.'" class="label"><label for="field'.$field->id.'.'.$signup.'[fileupload][]">'.$field->field.'</label></div>';
						if ($submitter_id == 0) {
							$element .= "<input type=\"file\" name=\"field".$field->id.'.'.$signup."[fileupload][]\" class=\"fileupload".$field->parameters->get('class','')."\" id=\"fileupload_".$field->cssfield."\"/>";
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

		/* Add any redEVENT values */
		$redevent = JRequest::getVar('redevent', false);
		if ($redevent) 
		{
			$html .= '<input type="hidden" name="integration" value="redevent" />';
			$html .= '<input type="hidden" name="event_task" value="'.$redevent->task.'" />';
			$html .= '<input type="hidden" name="event_id" value="'.$redevent->eventid.'" />';
			$html .= '<input type="hidden" name="xref" value="'.JRequest::getInt('xref').'" />';
		}
		else if (JRequest::getVar('integration') == 'redevent')
		{
			$html .= '<input type="hidden" name="integration" value="redevent" />';
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
		if ($form->captchaactive && $submitter_id == 0) 
		{
//			$html .= '<div id="redformcaptcha"><img src="index.php?option=com_redform&task=displaycaptcha&controller=redform">';
//			$html .= ' '.JHTML::tooltip(JText::_('CAPTCHA_TOOLTIP'), JText::_('CAPTCHA'), 'tooltip.png', '', '', false).'</div>';
//			$html .= '<div id="redformcaptchaword"><input type="text" name="captchaword"></div>';

	
			if (file_exists(JPATH_PLUGINS.DS.'system'.DS.'Captcha04'.DS."CaptchaImage.php"))
			{
				$html .= '<div class="fieldline">';
				$html .= '<div class="label"><label for="captchaword">'.JText::_('REDFORM_CAPTCHA_LABEL').'</label></div>';
				$html .= '<div id="redformcaptcha">';
				$html .= '<img src="index.php?option=com_redform&task=displaycaptcha&controller=redform"><br/>';
				$html .= '<input type="text" name="captchaword">';
				$img = JHTML::image(JURI::root().'components/com_redform/assets/images/info.png', JText::_('ToolTip'));
				$html .= ' <span class="editlinktip hasTipField" title="'.JText::_('REDFORM_CAPTCHA_LABEL').'::'.JText::_('REDFORM_CAPTCHA_TOOLTIP').'">'. $img .'</span>';
				$html .= '</div>';
				$html .= '</div>';
			}
	}
		
		/* Get the user details form */
		if (!$answers && !JRequest::getVar('redform_edit') &&  !JRequest::getVar('redform_add')) 
		{
			$html .= '<div id="submit_button" style="display: block;"><input type="submit" id="regularsubmit" name="submit" value="'.JText::_('Submit').'" />';
			if ( JRequest::getInt('xref', false)
			     && isset($this->_rwfparams['show_submission_type_webform_formal_offer'])
			     && $this->_rwfparams['show_submission_type_webform_formal_offer'] ) 
			{
				$html .= '<input type="submit" name="submit[print]" id="printsubmit" value="'.JText::_('SUBMIT_AND_PRINT').'" />';
			}
			$html .= '</div>';
		}
		else if (!JRequest::getVar('redform_edit') &&  !JRequest::getVar('redform_add')) 
		{
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
		if ($submitter_id > 0) 
		{
			$html .= '<input type="hidden" name="submitter_id" value="'.$submitter_id.'" />';			
			if ($redevent && ($redevent->task == 'manageredit' || $redevent->task == 'edit')) {
				$html .= '<input type="hidden" name="controller" value="redform" />';
			}
		}
		else {
			$html .= '<input type="hidden" name="controller" value="redform" />';
		}
		
		if (JRequest::getVar('redform_edit') || JRequest::getVar('redform_add')) {
			$html .= '<input type="hidden" name="controller" value="submitters" />';
		}
			
		if (JRequest::getVar('redform_edit')) {
			$html .= '<input type="hidden" name="event_task" value="review" />';
		}
		
		$html .= '<input type="hidden" name="curform" value="'.($answers && count($answers) ? count($answers) : 1).'" />';
		$html .= '<input type="hidden" name="form_id" value="'.$form->id.'" />';
		$html .= '<input type="hidden" name="multi" value="'.$multi.'" />';
		
		if (JRequest::getVar('close_form', true)) {
			$html .= '</form>';
		}
		
		$html .= '</div>'; // div #redform
		
		if ($footnote) $html .= '<div id="footnote"><div id="validate_footnote">'.JText::_('VALIDATE_FOOTNOTE').'</div></div>';

		return $html;
	}
	
	function getPDFForm($form, $fields, $multi = 1)
	{
		$pdfform = JRequest::getVar('pdfform');
		$footnote = false;
		$multi = max($multi, 1); // make sure we display at least one form
		 		 
		/* Stuff to find and replace */
		$find = array(' ', '_', '-', '.', '/', '&', ';', ':', '?', '!', ',');
		$replace = '';
		
		/* display forms */
		for ($signup = 1; $signup <= $multi; $signup++)
		{
			if ($signup > 1) $pdfform->Addpage('P');
			$pdfform->Cell(0, 10, JText::_('ATTENDEE').' '.$signup, 0, 1, 'L');
			$footnote = false;
	
			foreach ($fields as $key => $field)
			{
				$field->cssfield = strtolower($this->replace_accents(str_replace($find, $replace, $field->field)));
	
				$values = $this->getFormValues($field->id);
	
				if ($field->fieldtype == 'info' && count($values))
				{
					$pdfform->Cell(0, 10, $values[0]->value, 0, 1, 'L');
					continue;
				}
					
				$pdfform->Cell(0, 10, $field->field, 0, 1, 'L');
	
				$cleanfield = 'field_'. $field->id;
				$element = '';
				switch ($field->fieldtype)
				{
					case 'radio':
						foreach ($values as $id => $value)
						{
							$pdfform->setX($pdfform->getX()+2);
							$pdfform->Circle($pdfform->getX(), $pdfform->getY(), 2);
							$pdfform->setXY($pdfform->getX()+3, $pdfform->getY()-5);
							$pdfform->Write(10, $value->value);
							$pdfform->Ln();
						}
						break;
	
					case 'textarea':
						$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 100, 15);
						$pdfform->Ln();
						break;
	
					case 'wysiwyg':
						$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 100, 15);
						$pdfform->Ln();
						break;
	
					case 'price':
						// if has not null value, it is a fixed price, if not this is a user input price
						if (count($values) && $values[0]) // display price and add hidden field (shouldn't be used when processing as user could forge the form...)
						{
							$pdfform->Write(10, $form->currency .' '.$values[0]->value);
							$pdfform->Ln();
						}
						else // like a text input
						{
							$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
							$pdfform->Ln();
						}
						break;
						
					case 'email':
						$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
						$pdfform->Ln();
						break;
	
					case 'fullname':
						$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
						$pdfform->Ln();
						break;
	
					case 'username':
						$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
						$pdfform->Ln();
						break;
	
					case 'textfield':
					case 'birthday':
						$pdfform->Rect($pdfform->getX(), $pdfform->getY(), 50, 7);
						$pdfform->Ln();
						break;
	
					case 'checkbox':
						foreach ($values as $id => $value)
						{
							$pdfform->Rect($pdfform->getX(), $pdfform->getY()+2, 5, 5);
							$pdfform->setX($pdfform->getX()+5);
							$pdfform->Write(10, $value->value);
							$pdfform->Ln();
						}
						break;
	
					case 'select':
						foreach ($values as $id => $value)
						{
							$pdfform->Rect($pdfform->getX(), $pdfform->getY()+2, 5, 5);
							$pdfform->setX($pdfform->getX()+5);
							$pdfform->Write(10, $value->value);
							$pdfform->Ln();
						}
						break;
	
					case 'multiselect':
						foreach ($values as $id => $value)
						{
							$pdfform->Rect($pdfform->getX(), $pdfform->getY()+2, 5, 5);
							$pdfform->setX($pdfform->getX()+5);
							$pdfform->Write(10, $value->value);
							$pdfform->Ln();
						}
						break;
	
				}
	
			}
		}
		/* Close collapsable box */
		if ($footnote) $pdfform->Write(10, JText::_('VALIDATE_FOOTNOTE'));

		return $pdfform;
	}
	
	
}
?>