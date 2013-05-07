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
// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.mail.helper');

require_once('redform.defines.php');

require_once(RDF_PATH_SITE.DS.'classes'.DS.'field.php');
require_once(RDF_PATH_SITE.DS.'models'.DS.'redform.php');
require_once(RDF_PATH_SITE.DS.'helpers'.DS.'log.php');
require_once(RDF_PATH_SITE.DS.'helpers'.DS.'analytics.php');

class RedFormCore extends JObject {

	private $_form_id;

	private $_sids;

	private $_submit_key;

	private $_answers;

	private $_sk_answers;

	private $_fields;

	public function __construct()
	{
		parent::__construct();
		$lang = JFactory::getLanguage();
		$lang->load('com_redform', JPATH_SITE.DS.'components'.DS.'com_redform');
		$lang->load('com_redform', JPATH_SITE, null, true);
	}

	/**
	 * Returns a reference to the global User object, only creating it if it
	 * doesn't already exist.
	 *
	 *
	 * @param   int  $form_id  the form to use - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return 	RedFormCore		The  object.
	 *
	 * @since 	1.5
	 */
	public static function &getInstance($form_id = 0)
	{
		static $instances;

		if (!isset ($instances))
		{
			$instances = array ();
		}

		if (empty($instances[$form_id]))
		{
			$inst = new RedFormCore();
			$inst->setFormId($form_id);
			$instances[$form_id] = $inst;
		}

		return $instances[$form_id];
	}

	/**
	 * sets form id
	 *
	 * @param unknown $id
	 */
	public function setFormId($id)
	{
		if ($this->_form_id !== $id)
		{
			$this->_form_id = intval($id);
			$this->_fields = null;
		}
	}

	function setSids($ids)
	{
		JArrayHelper::toInteger($ids);
		if ($ids !== $this->_sids) {
			$this->_sids = $ids;
			$this->_answers = null;
			$this->_sids_answers = null;
		}
	}

	function setSubmitKey($submit_key)
	{
		if ($this->_submit_key !== $submit_key) {
			$this->_submit_key = $submit_key;
			$this->_sk_answers = null;
			$this->_answers = null;
		}
	}

	/**
	 * returns the html code for form elements (only the elements ! not the form itself, or the submit buttons...)
	 *
	 * @param int id of the form to display
	 * @param int/array optional id of submission_id, for example when we are modifying previous answers
	 * @param int optional number of instance of forms to display (1 is default)
	 * @return string html
	 */
	function displayForm($form_id, $reference = null, $multiple = 1, $options = array())
	{
		$this->setFormId($form_id);
		$uri 		= & JFactory::getURI();
		// was this form already submitted before (and there was an error for example, or editing)
		$answers    = $this->getAnswers($reference);
		if ($answers && $reference)	{
			$submit_key = $answers[0]->submit_key;
		}
		else {
			$submit_key = null;
			$answers = null;
		}

		$model_redform = new RedformModelRedform();
		$model_redform->setFormId($form_id);

		$form   = $model_redform->getForm();

		$html = '<form action="'.JRoute::_('index.php?option=com_redform').'" method="post" name="redform" class="'.$form->classname.'" enctype="multipart/form-data" onsubmit="return CheckSubmit();">';
		$html .= $this->getFormFields($form_id, $submit_key, $multiple, $options);

		/* Get the user details form */
		if (!$answers && !JRequest::getVar('redform_edit') &&  !JRequest::getVar('redform_add'))
		{
			$html .= '<div id="submit_button" style="display: block;" class="submitform'.$form->classname.'"><input type="submit" id="regularsubmit" name="submit" value="'.JText::_('COM_REDFORM_Submit').'" />';
			$html .= '</div>';
		}

		$html .= '<input type="hidden" name="task" value="save" />';
		if ($answers && $answers[0]->sid > 0)
		{
			$html .= '<input type="hidden" name="submitter_id" value="'.$answers[0]->sid.'" />';
		}

		if (JRequest::getVar('redform_edit') || JRequest::getVar('redform_add')) {
			$html .= '<input type="hidden" name="controller" value="submitters" />';
		}

		$html .= '<input type="hidden" name="controller" value="redform" />';
		$html .= '<input type="hidden" name="referer" value="'.htmlspecialchars($uri->toString()).'" />';

		$html .= '</form>';

		// Analytics
		if (redFORMHelperAnalytics::isEnabled())
		{
			$event = new stdclass;
			$event->category = 'form';
			$event->action = 'display';
			$event->label = "display form {$form->formname}";
			$event->value = null;
			redFORMHelperAnalytics::trackEvent($event);
		}

		return $html;
	}

	/**
	 * Returns html code for the specified form fields
	 * To modify previously posted data, the reference field must contain either:
	 * - submit_key as a string
	 * - an array of submitters ids
	 *
	 * @param   int    $form_id    form id
	 * @param   mixed  $reference  submit_key or array of submitters ids
	 * @param   int    $multi      number of instance of the form to display
	 * @param   array  $options    array of possible options: eventdetails, booking, extrafields
	 *
	 * @return string
	 */
	function getFormFields($form_id, $reference = null, $multi = 1, $options = array())
	{
		$uri       = JURI::getInstance();
		$user      = JFactory::getUser();
		$document  = &JFactory::getDocument();
		$app       = &Jfactory::getApplication();


		// was this form already submitted before (and there was an error for example, or editing)
		$answers    = $this->getAnswers($reference);
		if ($answers && $reference)	{
			$submit_key = $answers[0]->submit_key;
		}
		else {
			$submit_key = null;
		}

		$model_redform = new RedformModelRedform();
		$model_redform->setFormId($form_id);

		$form   = $model_redform->getForm();
		$fields = $model_redform->getFormFields();

	  // css
    $document->addStyleSheet(JURI::base().'components/com_redform/assets/css/tooltip.css');
    $document->addStyleSheet(JURI::base().'components/com_redform/assets/css/redform.css');

		// load jquery for the form javascript
		if (JRequest::getVar('format', 'html') == 'html') {
			JHTML::_('behavior.tooltip');
			jimport('joomla.html.html');
		}

  	// custom tooltip
  	$toolTipArray = array('className'=>'redformtip'.$form->classname);
    JHTML::_('behavior.tooltip', '.hasTipField', $toolTipArray);

    // currency for javascript
    $js = "var currency = \"".$form->currency."\";\n";
    $document->addScriptDeclaration($js);

 		self::JsCheck();
    if ($form->show_js_price)
    {
  		self::jsPrice();
    }

		// redmember integration: pull extra fields
		if ($user->get('id') && file_exists(JPATH_ROOT.DS.'components'.DS.'com_redmember')) {
			$this->getRedmemberfields($user);
		}

		/* Stuff to find and replace */
		$find = array(' ', '_', '-', '.', '/', '&', ';', ':', '?', '!', ',');
		$replace = '';

		$html = '<div id="redform'.$form->classname.'" class="redform-form">';

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
			$html .= '<div id="needlogin">'.JText::_('COM_REDFORM_LOGIN_BEFORE_MULTI_SIGNUP').'</div>';
			$multi = 1;
		}
		// limit to max 30 sumbissions at the same time...
		if ($multi > 30) {
			$multi = 30;
		}

		if ($multi > 1)
		{
			if (empty($answers)) {
				// link to add signups
				$html .= '<div id="signupuser"><a href="javascript: AddUser();">'.JText::_('COM_REDFORM_SIGN_UP_USER').'</a></div>';
			}

			// signups display controls
			$html .= '<div id="signedusers" style="float: right">';
			$html .=   '<div><a href="javascript: ShowAllUsers(true);" >'.JText::_('COM_REDFORM_SHOW_ALL_USERS').'</a></div>'
			         . '<div><a href="javascript: ShowAllUsers(false);" >'.JText::_('COM_REDFORM_HIDE_ALL_USERS').'</a></div>'
			         . '<ul>'.JText::_('COM_REDFORM_Signed_up').':';
			$html .= '<li><a href="javascript: ShowSingleForm(\'div#formfield1\');"># 1</a></li>';
			if ($answers)
			{
				for ($k = 2; $k < count($answers)+1; $k++) {
					$html .= '<li><a href="javascript: ShowSingleForm(\'div#formfield'.$k.'\');"># '.$k.'</a></li>';
				}
			}
			$html   .= '</ul>';
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
			if ($answers && $answers[($signup-1)]->sid) {
				$submitter_id = $answers[($signup-1)]->sid;
				$html .= '<input type="hidden" name="submitter_id'.$signup.'" value="'.$submitter_id.'" />';
			}
			else {
				$submitter_id = 0;
			}

			/* Make a collapsable box */
			$html .= '<div id="formfield'.$signup.'" class="formbox" style="display: '.($signup == 1 ? 'block' : 'none').';">';
			if ($multi > 1) {
				$html .= '<fieldset><legend>'.JText::sprintf('COM_REDFORM_FIELDSET_SIGNUP_NB', $signup).'</legend>';
			}

			if ($form->activatepayment && isset($options['eventdetails']) && $options['eventdetails']->course_price > 0) {
				$html .= '<div class="eventprice" price="'.$options['eventdetails']->course_price.'">'.JText::_('COM_REDFORM_Registration_price').': '.$form->currency.' '.$options['eventdetails']->course_price.'</div>';
			}
			if ($form->activatepayment && isset($options['booking']) && $options['booking']->course_price > 0) {
				$html .= '<div class="bookingprice" price="'.$options['booking']->course_price.'">'.JText::_('COM_REDFORM_Registration_price').': '.$form->currency.' '.$options['booking']->course_price.'</div>';
			}

			if (isset($options['extrafields']) && count($options['extrafields']))
			{
				foreach ($options['extrafields'] as $field)
				{
					$html .= '<div class="fieldline'.(isset($field['class']) && !empty($field['class']) ? ' '.$field['class'] : '' ).'">';
					$html .= '<div class="label">'.$field['label'].'</div>';
					$html .= '<div class="field">'.$field['field'].'</div>';
					$html .= '</div>';
				}
			}

			foreach ($fields as $key => $field)
			{
				if (!($app->isAdmin() || $field->published)) { // only display unpublished fields in backend form
					continue;
				}

				if ($field->fieldtype != 'hidden') {
					$html .= '<div id="fieldline_'.$field->id.'" class="fieldline type-'.$field->fieldtype.$field->parameters->get('class','').'">';
				}

				$values = $model_redform->getFormValues($field->id);

				if ($field->fieldtype == 'info')
				{
					if ($values && count($values))
					{
						$html .= '<div class="infofield">' . $values[0]->value . '</div>';
					}
					$html .= '</div>';
					continue;
				}

				$cleanfield = 'field_'. $field->id;
				if ($field->fieldtype == 'hidden') {
					$element = "";
				}
				else {
					$element = "<div class=\"field\">";
				}

				switch ($field->fieldtype)
				{
					case 'radio':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= '<div class="fieldoptions">';
						foreach ($values as $id => $value)
						{
							$element .= '<div class="fieldoption">';
							$element .= "<input class=\"".$field->parameters->get('class','')." ";
							if ($field->validate) $element .= "required";
							$element .= "\"";
							if ($field->readonly && !$app->isAdmin()) $element .= ' readonly="readonly"';
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
							else if ($field->default)
							{
								$def_vals = explode("\n", $field->default);
								foreach ($def_vals as $val)
								{
									if ($value->value == trim($val)) {
										$element .= ' checked="checked"';
										break;
									}
								}
							}
							$element .= ' type="radio" name="field'.$field->id.'.'.$signup.'[radio][]" value="'.$value->id.'" price="'.$value->price.'" />'.$value->label."\n";
							$element .= "</div>\n";
						}
						$element .= "</div>\n";
						break;

					case 'textarea':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= '<textarea class="'.$field->parameters->get('class','');
						if ($field->validate) $element .= ' required';
						$element .= '" name="field'.$field->id.'.'.$signup.'[textarea]"';
						$element .= ' id="field'.$field->id.'" ';
						$element .= ' cols="'.$field->parameters->get('cols',25).'" rows="'.$field->parameters->get('rows',6).'"';
						if ($field->readonly && !$app->isAdmin()) $element .= ' readonly="readonly"';
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
						else {
							$element .= $field->default;
						}
						$element .= "</textarea>\n";
						break;

					case 'wysiwyg':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
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
						else {
							$content = $field->default;
						}
						$editor = & JFactory::getEditor();

						// Cannot have buttons, it triggers an error with tinymce for unregistered users
						$element .= $editor->display( "field".$field->id.'.'.$signup."[wysiwyg]", $content, '100%;', '200', '75', '20', false ) ;
						$element .= "\n";
						break;

					case 'email':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= "<div class=\"emailfields\">";
						$element .= "<div class=\"emailfield\">";
						$element .= "<input class=\"".$field->parameters->get('class','')." ";
						if ($field->validate) $element .= "required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[email][]\"";
						$element .= ' id="field'.$field->id.'" ';
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						if ($field->readonly && !$app->isAdmin()) $element .= ' readonly="readonly"';
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
						else {
							$element .= $field->default;
						}
						$element .= "\" />\n";
						$element .= "</div>\n";

						/* check if there is a mailing list integration */
						if (strlen($field->listnames) > 0)
						{
							$listnames = explode(";", $field->listnames);
							if (count($listnames))
							{
								if ($field->parameters->get('force_mailing_list', 0))
								{
									// auto subscribe => use hidden field
									foreach ($listnames AS $listkey => $listname)
									{
										$element .= '<input type="hidden" name="field'.$field->id.'.'.$signup.'[email][listnames][]" value="'.$listname.'" />';
									}
								}
								else
								{
									$element .= "<div class=\"newsletterfields\">";
									$element .= '<div id="signuptitle">'.JText::_('COM_REDFORM_SIGN_UP_MAILINGLIST').'</div>';
									$element .= "<div class=\"field".$field->fieldtype."_listnames\">";
									foreach ($listnames AS $listkey => $listname)
									{
										$element .= "<div class=\"field_".$listkey."\">";
										$element .= "<input type=\"checkbox\" name=\"field".$field->id.'.'.$signup."[email][listnames][]\" value=\"".$listname."\" />".$listname.'</div>';
									}
									$element .= "</div>\n";
									$element .= "</div>\n";
								}
							}
						}
						$element .= "</div>\n";
						break;

					case 'fullname':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= "<input class=\"".$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[fullname][]\"";
						$element .= ' id="field'.$field->id.'" ';
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						if ($field->readonly && !$app->isAdmin()) $element .= ' readonly="readonly"';
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
						else {
							$element .= $field->default;
						}
						$element .= "\" />\n";
						break;

					case 'username':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= "<input class=\"".$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[username][]\"";
						$element .= ' id="field'.$field->id.'" ';
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						if ($field->readonly && !$app->isAdmin()) $element .= ' readonly="readonly"';
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
						else {
							$element .= $field->default;
						}
						$element .= "\" />\n";
						break;

					case 'textfield':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= "<input class=\"".$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"text\" name=\"field".$field->id.'.'.$signup."[text][]\"";
						$element .= ' id="field'.$field->id.'" ';
						$element .= ' size="'.$field->parameters->get('size', 25).'"';
						$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
						if ($field->readonly && !$app->isAdmin()) $element .= ' readonly="readonly"';
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
						else {
							$element .= $field->default;
						}
						$element .= "\" />\n";
						break;

					case 'hidden':
						$label = '';
						$element .= "<input class=\"".$field->parameters->get('class','');
						if ($field->validate) $element .= " required";
						$element .= "\" type=\"hidden\" name=\"field".$field->id.'.'.$signup."[hidden][]\"";
						$element .= ' id="field'.$field->id.'" ';
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
						else
						{
							switch ($field->parameters->get('valuemethod', 'constant'))
							{
								case 'jrequest':
									$varname= $field->parameters->get('jrequestvar');
									if (!$varname) {
										$app->enqueueMessage(JText::_('COM_REDFORM_ERROR_HIDDEN_FIELD_VARNAME_NOT_DEFINED'), 'warning');
									}
									else {
										$element .= JRequest::getVar($varname);
									}
									break;

								case 'phpeval':
									$code= $field->parameters->get('phpeval');
									$element .= eval($code);
									break;

								case 'constant':
								default:
									$element .= $field->default;
							}
						}
						$element .= "\" />\n";
						break;

					case 'date':
						JHTML::_('behavior.calendar');
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';

						if ($answers)
						{
							if (isset($answers[($signup-1)]->fields->$cleanfield)) {
								$val = $answers[($signup-1)]->fields->$cleanfield;
							}
							else {
								$val = null;
							}
						}
						else if ($user->get($field->redmember_field)) { // redmember uses unix timestamp
							$val = strftime($field->parameters->get('dateformat','%Y-%m-%d'), $user->get($field->redmember_field));
						}
						else {
							if ($field->default && strtotime($field->default)) {
								$val = strftime($field->parameters->get('dateformat','%Y-%m-%d'), strtotime($field->default));
							}
							else {
								$val = null;
							}
						}

						$class = $field->parameters->get('class','');
						if ($field->validate) $class .= " required";

						if ($field->readonly && !$app->isAdmin())
						{
							$element .= $val;
							$element .= '<input class="'.$class.'"';
							$element .= " type=\"hidden\" name=\"field".$field->id.'.'.$signup."[text][]\"";
							$element .= ' id="field'.$field->id.'" ';
							$element .= ' size="'.$field->parameters->get('size', strlen($val)+1).'"';
							$element .= ' maxlength="'.$field->parameters->get('maxlength', 25).'"';
							$element .= ' readonly="readonly"';
							$element .= ' value="'.$val."\" />\n";
						}
						else
						{
							$attribs = array('class' => $class);
							$element .= JHTML::_('calendar', $val, 'field'.$field->id.'.'.$signup.'[date]', 'field'.$field->id,
							               $field->parameters->get('dateformat','%Y-%m-%d'),
							               $attribs);
						}
						break;

					case 'price':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						// if has not null value, it is a fixed price, if not this is a user input price
						if (count($values) && $values[0]) // display price and add hidden field (shouldn't be used when processing as user could forge the form...)
						{
							$element .= $form->currency .' '.$values[0]->value;
							$element .= '<input type="hidden" class="rfprice" id="field'.$field->id.'" name="field'.$field->id.'.'.$signup.'[price][]" value="'.$values[0]->value.'" />';
						}
						else // like a text input
						{
							$element .= '<input class="rfprice '. $field->parameters->get('class','') .($field->validate ? " required" : '') .'"';
							$element .= ' type="text" name="field'.$field->id.'.'.$signup.'[price][]"';
							$element .= ' id="field'.$field->id.'" ';
							$element .= ' size="'.$field->parameters->get('size', 25).'"';
							$element .= ' maxlength="'.$field->parameters->get('maxlength', 250).'"';
							if ($field->readonly && !$app->isAdmin()) $element .= ' readonly="readonly"';
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
							else {
								$element .= $field->default;
							}
							$element .= '"';
							$element .= '/>';
						}
						$element .= "\n";
						break;

					case 'checkbox':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= '<div class="fieldoptions">';
						foreach ($values as $id => $value)
						{
							$element .= '<div class="fieldoption">';
							$element .= "<input class=\"".$field->parameters->get('class','')." ";
							if ($field->validate) $element .= "required";
							$element .= "\"";
							if ($field->readonly && !$app->isAdmin()) $element .= ' readonly="readonly"';
							if ($answers && isset($answers[($signup-1)]->fields->$cleanfield))
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
							else if ($field->default)
							{
								$def_vals = explode("\n", $field->default);
								foreach ($def_vals as $val)
								{
									if ($value->value == trim($val)) {
										$element .= ' checked="checked"';
										break;
									}
								}
							}
							$element .= ' type="checkbox" name="field'.$field->id.'.'.$signup.'[checkbox][]" value="'.$value->value.'" price="'.$value->price.'" /> '.$value->label."\n";
							$element .= "</div>\n";
						}
						$element .= "</div>\n";
						break;

					case 'select':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= "<select id=\"field".$field->id."\" name=\"field".$field->id.'.'.$signup."[select][]\" class=\"".$field->parameters->get('class','').($field->validate ?" required" : '')."\"";
						$element .= ">";
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
							else if ($field->default)
							{
								$def_vals = explode("\n", $field->default);
								foreach ($def_vals as $val)
								{
									if ($value->value == trim($val)) {
										$element .= ' selected="selected"';
										break;
									}
								}
							}
							$element .= ' price="'.$value->price.'" >'.$value->label."</option>";
						}
						$element .= '</select>';
						$element .= "\n";
						break;

					case 'multiselect':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= '<select id="field'.$field->id.'" name="field'.$field->id.'.'.$signup.'[multiselect][]"'
						          . ' multiple="multiple" size="'.$field->parameters->get('size',5).'"'
						          . ' class="'.trim($field->parameters->get('class','').($field->validate ?" required" : '')).'"';
						$element .= '>';
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
							else if ($field->default)
							{
								$def_vals = explode("\n", $field->default);
								foreach ($def_vals as $val)
								{
									if ($value->value == trim($val)) {
										$element .= ' selected="selected"';
										break;
									}
								}
							}
							$element .= ' price="'.$value->price.'" />'.$value->label."</option>";
						}
						$element .= '</select>';
						$element .= "\n";
						break;

					case 'recipients':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						$element .= "<select id=\"field".$field->id."\" name=\"field".$field->id.'.'.$signup."[recipients][]\""
						         . ($field->parameters->get('multiple', 1) ? ' multiple="multiple"' : '')
						         . ' size="'.$field->parameters->get('size', 5).'"'
						         . ' class="'.$field->parameters->get('class','').($field->validate ?" required" : '').'"';
						$element .= '>';
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
							else if ($field->default)
							{
								$def_vals = explode("\n", $field->default);
								foreach ($def_vals as $val)
								{
									if ($value->value == trim($val)) {
										$element .= ' selected="selected"';
										break;
									}
								}
							}
							$element .= " >".$value->label."</option>";
						}
						$element .= '</select>';
						$element .= "\n";
						break;

					case 'fileupload':
						$label = '<div id="field_'.$field->id.'" class="label"><label for="field'.$field->id.'">'.$field->field.'</label></div>';
						if ($submitter_id == 0) {
							$element .= "<input type=\"file\" id=\"field".$field->id."\" name=\"field".$field->id.'.'.$signup."[fileupload][]\" class=\"fileupload".$field->parameters->get('class','')."\" id=\"fileupload_".$field->id."\"/>";
						}
						$element .= "\n";
						break;
				}

				if ($field->fieldtype == 'hidden')
				{
					$html .= $element;
				}
				else
				{
					$html .= $label.$element;
					$html .= '</div>'; // fieldtype div

					if ($field->validate || strlen($field->tooltip))
					{
						$html .= '<div class="fieldinfo">';
						if ($field->validate) {
							$img = JHTML::image(JURI::root().'components/com_redform/assets/images/warning.png', JText::_('COM_REDFORM_Required'));
							$html .= ' <span class="editlinktip hasTipField" title="'.JText::_('COM_REDFORM_Required').'" style="text-decoration: none; color: #333;">'. $img .'</span>';
						}
						if (strlen($field->tooltip) > 0) {
							$img = JHTML::image(JURI::root().'components/com_redform/assets/images/info.png', JText::_('COM_REDFORM_ToolTip'));
							$html .= ' <span class="editlinktip hasTipField" title="'.htmlspecialchars($field->field).'::'.htmlspecialchars($field->tooltip).'" style="text-decoration: none; color: #333;">'. $img .'</span>';
						}
						$html .= '</div>';
					}
					$html .= '</div>'; // fieldline_ div
				}

			}
			if ($multi > 1) {
				$html .= '</fieldset>';
			}
			if (isset($this->_rwfparams['uid']))
			{
				$html .= '<div>'.JText::_('COM_REDFORM_JOOMLA_USER').': '. JHTML::_('list.users', 'uid', $this->_rwfparams['uid'], 1, NULL, 'name', 0 ).'</div>';
			}
			$html .= '</div>'; // formfield div
		}

		//TODO: redcompetition should use redform core directly
		/* Add any redCOMPETITION values */
		$redcompetition = JRequest::getVar('redcompetition', false);

		if ($redcompetition) {
			$html .= '<input type="hidden" name="competition_task" value="'.$redcompetition->task.'" />';
			$html .= '<input type="hidden" name="competition_id" value="'.$redcompetition->competitionid.'" />';
		}

		// Get an unique id just for the submission
		$uniq = uniqid();

		/* Add the captcha, only if initial submit */
		if ($form->captchaactive && empty($submit_key))
		{
			JPluginHelper::importPlugin( 'redform_captcha' );
			$captcha = '';
			$dispatcher =& JDispatcher::getInstance();
			$results = $dispatcher->trigger( 'onGetCaptchaField', array( &$captcha ) );

			if (count($results))
			{
				$html .= '<div class="fieldline">';
				$html .= '<div class="label"><label>'.JText::_('COM_REDFORM_CAPTCHA_LABEL').'</label></div>';
				$html .= '<div id="redformcaptcha">';
				$html .= $captcha;
				$html .= '</div>';
				$html .= '</div>';

				JFactory::getSession()->set('checkcaptcha' . $uniq, 1);
			}
		}

		if (!empty($submit_key))
		{
			// Link to add signups
			$html .= '<input type="hidden" name="submit_key" value="'.$submit_key.'" />';
		}

		$html .= '<input type="hidden" name="curform" value="'.($answers && count($answers) ? count($answers) : 1).'" />';
		$html .= '<input type="hidden" name="form_id" value="'.$form_id.'" />';
		$html .= '<input type="hidden" name="multi" value="'.$multi.'" />';
		$html .= '<input type="hidden" name="' . JSession::getFormToken() . '" value="' . $uniq . '" />';

		$html .= '</div>'; // div #redform

		return $html;
	}

	/**
	 * saves submitted form data
	 *
	 * @param string key, unique key for the 3rd party (allows to prevent deletions from within redform itself for 3rd party, and to find out which submission belongs to which 3rd party...)
	 * @param array data if empty, the $_POST variable is used
	 * @return int/array submission_id, or array of submission ids in case of success, 0 otherwise
	 */
	function saveAnswers($integration_key, $options = array(), $data = null)
	{
		require_once(RDF_PATH_SITE.DS.'models'.DS.'redform.php');
		$model = new RedformModelRedform();

		if (!$result = $model->apisaveform($integration_key, $options, $data))
		{
			$this->setError($model->getError());
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
		$app = &JFactory::getApplication();
		$params = JComponentHelper::getParams('com_redform');
		$uri = JFactory::getURI();
		$doc = &JFactory::getDocument();
		$doc->addScriptDeclaration('var totalpricestr = "'.JText::_('COM_REDFORM_Total_Price')."\";\n");
		$doc->addScriptDeclaration('var round_negative_price = '.($params->get('allow_negative_total', 1) ? 0 : 1).";\n");
		$doc->addScript(JRoute::_('index.php?option=com_redform&task=jsprice'));
	}

	function JsCheck()
	{
		$doc = &JFactory::getDocument();
		$doc->addScript(JRoute::_('index.php?option=com_redform&task=jscheck'));
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
	function getSubmitKeyAnswers($submit_key = null)
	{
		if ($submit_key)
		{
			$this->setSubmitKey($submit_key);
		}
		elseif (!$this->_submit_key)
		{
			JError::raiseWarning(0, 'COM_REDFORM_CORE_MISSING_SUBMIT_KEY');
			return false;
		}

		if (empty($this->_sk_answers))
		{
			$mainframe = &JFactory::getApplication();
			$db = JFactory::getDBO();
			// get form id and answer id
			$query = 'SELECT form_id, answer_id, submit_key, id '
			       . ' FROM #__rwf_submitters AS s '
			       . ' WHERE submit_key = '.$db->Quote($this->_submit_key)
			       ;
			$db->setQuery($query);
			$submitters = $db->loadObjectList();

			if (empty($submitters))
			{
				$answers = $mainframe->getUserState($this->_submit_key);
				if (!$answers)
				{
					return false;
				}
			}
			else
			{
				$sids = array();
				foreach ($submitters as $s)
				{
					$sids[] = $s->id;
				}
				$answers = $this->getSidsAnswers($sids);
			}

			$this->_sk_answers = $answers;
		}
		return $this->_sk_answers;
	}

	/**
	 * returns an array of objects with properties sid, submit_key, form_id, fields
	 *
	 * @param mixed submit_key string or array int submitter ids
	 */
	function getAnswers($reference)
	{
		if (!$this->_answers)
		{
			$app = JFactory::getApplication();
			if (is_array($reference)) // sids
			{
				$this->setSids($reference);
			}
			else
			{
				$this->setSubmitKey($reference);
			}

			if (is_array($reference)) // sids
			{
				$model_redform = new RedformModelRedform();
				$answers = $model_redform->getSidsAnswers($reference);
				$submit_key = $this->getSidSubmitKey(reset($reference));
			}
			else if (!empty($reference)) // submit_key
			{
				$submit_key = $reference;
				$answers = $this->getSubmitKeyAnswers($submit_key);
			}
			else
			{
				$submit_key = null;
				// look for submit data in session
				$answers = $app->getUserState('formdata'.$this->_form_id);
				// delete session data
				$app->setUserState('formdata'.$this->_form_id, null);
			}

			if (!$answers) {
				return false;
			}

			$results = array();
			foreach ($answers as $a)
			{
				$result = new formanswers();
				$result->sid        = (isset($a->sid) ? $a->sid : null);
				$result->submit_key = $submit_key;
				$result->fields     = $a;
				$results[] = $result;
			}
			$this->_answers = $results;
		}
		return $this->_answers;
	}

	function getFields($form_id= null)
	{
		if ($form_id) {
			$this->setFormId($form_id);
		}
		if (empty($this->_fields))
		{
			$model_redform = new RedformModelRedform();
			$model_redform->setFormId($this->_form_id);
			$this->_fields = $model_redform->getFormFields();
		}
		return $this->_fields;
	}

	/**
	 * return raw records from form table indexed by sids
	 *
	 * @param array int sids
	 * @return array
	 */
	function getSidsAnswers($sids)
	{
		if ($sids) {
			$this->setSids($sids);
		}
		if (empty($this->_sids_answers))
		{
			$model_redform = new RedformModelRedform();
			$this->_sids_answers = $model_redform->getSidsAnswers($this->_sids);
		}
		return $this->_sids_answers;
	}

	/**
	 * return fields with answers, indexed by sids
	 *
	 * @param array int sids
	 * @return array
	 */
	function getSidsFieldsAnswers($sids)
	{
		if ($sids) {
			$this->setSids($sids);
		}

		if (!is_array($this->_sids) || empty($this->_sids)) {
			return false;
		}
		$answers = $this->getSidsAnswers($this->_sids);
		$form_id = $this->getSidForm($this->_sids[0]);
		$fields  = $this->getFields($form_id);

		if (!$form_id) {
			$this->setError(JText::_('COM_REDFORM_FORM_NOT_FOUND'));
			return false;
		}

		$res = array();
		foreach ($answers as $sid => $answer)
		{
			$f = array();
			foreach ($fields as $field)
			{
				if ($field->fieldtype == 'info')
				{
					$val = $this->getFieldValues($field->id);
					$field->answer = (isset($val[0]) ? $val[0]->value : '');
				}
				else {
					$prop = 'field_'.$field->id;
					$field->answer = $answer->$prop;
				}
				$f[] = clone($field);
			}
			$res[$sid] = $f;
		}
		return $res;
	}

	/**
	 * return form_id associated to submitter id
	 *
	 * @param int sid
	 * @return int
	 */
	function getSidForm($sid)
	{
		$db = &JFactory::getDBO();

		$query = ' SELECT f.id '
		       . ' FROM #__rwf_forms AS f '
		       . ' INNER JOIN #__rwf_submitters AS s ON f.id = s.form_id '
		       . ' WHERE s.id = ' . $db->Quote($sid)
		       ;
		$db->setQuery($query);
		$res = $db->loadResult();
		return $res;
	}

	/**
	 * return form status (EXPIRED, REGISTER_ACCESS, SPECIAL_ACCESS)
	 *
	 * check error for status details
	 *
	 * @param int $form_id
	 * @return boolean
	 */
	function getFormStatus($form_id)
	{
		$db   = &JFactory::getDBO();
		$user = &JFactory::getUser();

		$query = ' SELECT f.* '
		       . ' FROM #__rwf_forms AS f '
		       . ' WHERE id = ' . (int) $form_id;
		$db->setQuery($query);
		$form = $db->loadObject();

		if (!$form->published)
		{
			$this->setError(JText::_('COM_REDFORM_STATUS_NOT_PUBLISHED'));
			return false;
		}
		if (strtotime($form->startdate) > time())
		{
			$this->setError(JText::_('COM_REDFORM_STATUS_NOT_STARTED'));
			return false;
		}
		if ($form->formexpires && strtotime($form->enddate) < time())
		{
			$this->setError( JText::_('COM_REDFORM_STATUS_EXPIRED'));
			return false;
		}
		if ($form->access > 1 && !$user->get('id'))
		{
			$this->setError( JText::_('COM_REDFORM_STATUS_REGISTERED_ONLY'));
			return false;
		}
		if ($form->access > max($user->getAuthorisedViewLevels()))
		{
			$this->setError( JText::_('COM_REDFORM_STATUS_SPECIAL_ONLY'));
			return false;
		}

		return true;
	}

	/**
	 * return values associated to a field
	 *
	 * @param int $field_id
	 * @return array
	 */
	function getFieldValues($field_id)
	{
		$db = &JFactory::getDBO();

		$query = " SELECT v.id, v.value, v.field_id, v.price "
		       . " FROM #__rwf_values AS v "
		       . " WHERE v.published = 1 "
		       . " AND v.field_id = ".$field_id
		       . " ORDER BY v.ordering";
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * return virtuemart form redirect
	 *
	 * @param int $form_id
	 * @return string url
	 */
	function getFormRedirect($form_id)
	{
		$model_redform = new RedformModelRedform();
		$model_redform->setFormId($form_id);


		$settings = $model_redform->getVmSettings();
		if (!$settings->virtuemartactive) {
			return false;
		}
		return JRoute::_('index.php?page=shop.product_details&product_id='.$settings->vmproductid.'&option=com_virtuemart&Itemid='.$settings->vmitemid);
	}

	/**
	 * get emails associted to submission key or sids
	 * @param mixed submit_key or array of sids
	 * @param boolean email required, returns false if no email field
	 * @return array or false
	 */
	function getSubmissionContactEmail($reference, $requires_email = true)
	{
		if (!is_array($reference)) {
			$sids = $this->getSids($reference);
		}
		else {
			$sids = $reference;
		}
		$answers = $this->getSidsFieldsAnswers($sids);

		$results = array();
		foreach ((array) $answers as $sid => $fields)
		{
			$emails = array();
			$fullnames = array();
			$usernames = array();
			foreach ((array) $fields as $f) // first look for email fields
			{
				if ($f->fieldtype == 'email')
				{
					if ($f->parameters->get('notify', 1)) { // set to receive notifications ?
						$emails[] = $f->answer;
					}
				}
				if ($f->fieldtype == 'username')
				{
					$usernames[] = $f->answer;
				}
				if ($f->fieldtype == 'fullname')
				{
					$fullnames[] = $f->answer;
				}
			}

			if (!count($emails) && $requires_email) {
				// no email field
				return false;
			}
			$result = array();
			foreach ($emails as $k => $val)
			{
				$result[$k]['email']    = $val;
				$result[$k]['username'] = isset($usernames[$k]) ? $usernames[$k] : '';
				$result[$k]['fullname'] = isset($fullnames[$k]) ? $fullnames[$k] : '';

				if (!isset($result[$k]['fullname']) && isset($result[$k]['username'])) {
					$result[$k]['fullname'] = $result[$k]['username'];
				}
			}
			$results[$sid] = $result;
		}
		return $results;
	}

	/**
	* get emails associted to sid
	* @param int submit_key or array of sids
	* @param boolean email required, returns false if no email field
	* @return array or false
	*/
	public function getSidContactEmails($sid)
	{
		$res = $this->getSubmissionContactEmail(array($sid), $requires_email = true);
		if ($res) {
			return $res[$sid];
		}
		return false;
	}

	function getSids($key)
	{
		$db = &JFactory::getDBO();

		$query = " SELECT s.id "
		       . " FROM #__rwf_submitters as s "
		       . " WHERE submit_key = ".$db->quote($key)
		       ;
		$db->setQuery($query);
		return $db->loadResultArray();
	}

	function getSidSubmitKey($sid)
	{
		$db = &JFactory::getDBO();

		$query = " SELECT s.submit_key "
		       . " FROM #__rwf_submitters as s "
		       . " WHERE id = ".$db->quote($sid)
		       ;
		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	 * get form object
	 *
	 * @param int $form_id
	 * @return object or false if not found
	 */
	public function getForm($form_id)
	{
		if (!isset($this->_form) || $this->_form->id <> $form_id)
		{

			$model_redform = new RedformModelRedform();
			$model_redform->setFormId($form_id);

			$this->_form = $model_redform->getForm();
		}
		return $this->_form;
	}

	/**
	 * return conditional recipients for specified answers
	 *
	 * @param object $form
	 * @param object $answers
	 * @return array|boolean false if no answer
	 */
	public static function getConditionalRecipients($form, $answers)
	{
		if (!$form->cond_recipients) {
			return false;
		}
		$recipients = array();
		$conds = explode("\n", $form->cond_recipients);
		foreach ($conds as $c)
		{
			if ($res = self::_parseCondition($c, $answers)) {
				$recipients[] = $res;
			}
		}
		return $recipients;
	}

	/**
	 * returns email if answers match the condition
	 *
	 * @param string $conditionline
	 * @param object $answers
	 * @return string|boolean email or false
	 */
	protected static function _parseCondition($conditionline, $answers)
	{
		$parts = explode(";", $conditionline);
		if (!count($parts)) {
			return false;
		}
		// cleanup
		array_walk($parts, 'trim');

		if (count($parts) < 5) { // invalid condition...
			RedformHelperLog::simpleLog('invalid condition formatting'. $conditionline);
			return false;
		}

		// first should be the email address
		if (!JMailHelper::isEmailAddress($parts[0])) {
			RedformHelperLog::simpleLog('invalid email in conditional recipient: '. $parts[0]);
			return false;
		}
		$email = $parts[0];

		// then the name of the recipient
		if (!$parts[1]) {
			RedformHelperLog::simpleLog('invalid name in conditional recipient: '. $parts[0]);
			return false;
		}
		$name = $parts[1];

		// then, we shoulg get the field
		$field_id = intval($parts[2]);
		$answer = $answers->getFieldAnswer($field_id);
		if ($answer === false) {
			RedformHelperLog::simpleLog('invalid field id for conditional recipient: '. $parts[1]);
			return false;
		}
		$value = $answer['value'];

		$isvalid = false;
		// then, we should get the function
		switch ($parts[3])
		{
			case 'between':
				if (!isset($parts[5])) {
					RedformHelperLog::simpleLog('missing max value in between conditional recipient: '. $conditionline);
				}
				if (is_numeric($value))
				{
					$value = floatval($value);
					$min = floatval($parts[4]);
					$max = floatval($parts[5]);
					$isvalid = ($value >= $min && $value <= $max ? $email : false);
				}
				else
				{
					$isvalid = strcasecmp($value, $parts[4]) >= 0 && strcasecmp($value, $parts[5]) <= 0;
				}
				break;

			case 'inferior':
				if (is_numeric($value))
				{
					$value = floatval($value);
					$max = floatval($parts[4]);
					$isvalid =  ($value <= $max ? $email : false);
				}
				else
				{
					$isvalid = strcasecmp($value, $parts[4]) <= 0;;
				}
				break;

			case 'superior':
				if (is_numeric($value))
				{
					$value = floatval($value);
					$min = floatval($parts[4]);
					$isvalid =  ($value >= $min ? $email : false);
				}
				else
				{
					$isvalid = strcasecmp($value, $parts[4]) >= 0;;
				}
				break;

			default:
				RedformHelperLog::simpleLog('invalid email in conditional recipient: '. $parts[0]);
				return false;
		}
		if ($isvalid) {
			return array($email, $name);
		}
	}

	/**
	 * submit form using user data from redmember
	 *
	 * @param   int     $user_id      user id
	 * @param   string  $integration  integration key
	 * @param   array   $options      extra submission data
	 *
	 * @return boolean true on success
	 */
	public function quickSubmit($user_id, $integration = null, $options = null)
	{
		if (!$user_id)
		{
			$this->setError('user id is required');
			return false;
		}

		if (!$this->_form_id)
		{
			$this->setError('form id not set');
			return false;
		}

		// Get User data
		$userData = $this->getUserData($user_id);

		$data = $this->prepareUserData($userData);

		return $this->saveAnswers($integration, $options, $data);
	}

	/**
	 * pulls users data
	 *
	 * should get it from redmember
	 *
	 * @param   int  $user_id  user id
	 *
	 * @return object
	 */
	protected function getUserData($user_id)
	{
		// for now, just get data from native joomla users
		return JFactory::getUser($user_id);
	}

	/**
	 * prepares data for saving
	 *
	 * @param   int     $xref      session id
	 * @param   object  $form      form data
	 * @param   object  $userData  user data
	 *
	 * @return array
	 */
	protected function prepareUserData($userData, $form_index = 1)
	{
		$data = array('form_id' => $this->_form_id);

		$fields = $this->getFields();

		foreach ($fields as $field)
		{
			$key   = 'field' . $field->id . '_' . $form_index;

			if ($field->redmember_field)
			{
				// waiting for redmember coding !
			}

			switch ($field->fieldtype)
			{
				case 'fullname':
					$data[$key]['fullname'][] = $userData->name;
					break;

				case 'username':
					$data[$key]['username'][] = $userData->username;
					break;

				case 'email':
					$data[$key]['email'][] = $userData->email;
					break;

				case 'textarea':
				case 'date':
				case 'wysiwyg':
					$data[$key][$field->fieldtype] = $field->default;
					break;

				case 'text':
				case 'hidden':
				case 'select':
					$data[$key][$field->fieldtype][] = $field->default;
					break;

				case 'checkbox':
				case 'multiselect':
				case 'recipients':
				case 'radio':
					$data[$key][$field->fieldtype] = explode("\n", $field->default);
					break;

				case 'price':
					if (count($field->options))
					{
						$value = $field->options[0]->value;
					}
					else
					{
						$value = $field->default;
					}
					$data[$key][$field->fieldtype][] = $value;
					break;


				default:
					// Unknown field...Do nothing
					break;
			}
		}

		return $data;
	}
}

class formanswers
{
	var $sid;
	var $submit_key;
	var $fields;
}