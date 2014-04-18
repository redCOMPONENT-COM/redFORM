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

require_once(JPATH_SITE . '/components/com_redform/redform.defines.php');

require_once(Rdf_PATH_SITE .'/models/redform.php');

class RdfCore extends JObject {

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
		$lang->load('com_redform', JPATH_SITE . '/components/com_redform');
		$lang->load('com_redform', JPATH_SITE, null, true);
	}

	/**
	 * Returns a reference to the global User object, only creating it if it
	 * doesn't already exist.
	 *
	 *
	 * @param   int  $form_id  the form to use - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return 	RedformCore		The  object.
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
			$inst = new RdfCore();
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

		$html = '<form action="'.JRoute::_('index.php?option=com_redform').'" method="post" name="redform" class="form-validate '.$form->classname.'" enctype="multipart/form-data">';
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
		if (RdfHelperAnalytics::isEnabled())
		{
			$event = new stdclass;
			$event->category = 'form';
			$event->action = 'display';
			$event->label = "display form {$form->formname}";
			$event->value = null;
			RdfHelperAnalytics::trackEvent($event);
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
		$document  = JFactory::getDocument();
		$app       = Jfactory::getApplication();


		// Was this form already submitted before (and there was an error for example, or editing)
		$answers    = $this->getAnswers($reference);

		if ($answers && $reference)
		{
			$submit_key = $answers[0]->submit_key;
		}
		else
		{
			$submit_key = null;
		}

		$model_redform = new RedformModelRedform();
		$model_redform->setFormId($form_id);

		$form   = $model_redform->getForm();
		$fields = $model_redform->getFormFields();

		// css
		$document->addStyleSheet(JURI::base() . 'components/com_redform/assets/css/tooltip.css');
		$document->addStyleSheet(JURI::base() . 'components/com_redform/assets/css/redform.css');

		if (isset($options['currency']) && $options['currency'])
		{
			$currency = $options['currency'];
		}
		else
		{
			$currency = $form->currency;
		}

		// custom tooltip
		$toolTipArray = array('className' => 'redformtip' . $form->classname);
		JHTML::_('behavior.tooltip', '.hasTipField', $toolTipArray);

		$this->loadCheckScript();

		if ($multi)
		{
			$this->loadMultipleFormScript();
		}

		if ($form->show_js_price)
		{
			$this->loadPriceScript();
		}

		// Redmember integration: pull extra fields
		if ($user->get('id') && file_exists(JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php'))
		{
			$this->getRedmemberfields($user);
		}

		$html = '<div class="redform-form' . $form->classname . '">';

		if ($form->showname)
		{
			$html .= '<div class="formname">' . $form->formname . '</div>';
		}

		if ($answers)
		{
			// set multi to number of answers...
			$multi = count($answers);
		}
		else
		{
			// limit to max 30 sumbissions at the same time...
			$multi = min($multi, 30);
		}

		if ($multi > 1 && $user->id == 0)
		{
			$html .= '<div class="needlogin">' . JText::_('COM_REDFORM_LOGIN_BEFORE_MULTI_SIGNUP') . '</div>';
			$multi = 1;
		}

		if ($multi > 1)
		{
			if (empty($answers))
			{
				// Link to add signups
				$html .= '<div class="add-instance">' . JText::_('COM_REDFORM_SIGN_UP_USER') . '</div>';
			}
		}

		$initialActive = $answers ? count($answers) : 1;

		/* Loop through here for as many forms there are */
		for ($signup = 1; $signup <= $initialActive; $signup++)
		{
			if ($answers && $answers[($signup-1)]->sid)
			{
				$submitter_id = $answers[($signup-1)]->sid;
				$html .= '<input type="hidden" name="submitter_id' . $signup . '" value="' . $submitter_id . '" />';
			}

			/* Make a collapsable box */
			$html .= '<div id="formfield' . $signup . '" class="formbox" style="display: ' . ($signup == 1 ? 'block' : 'none') . ';">';

			if ($multi > 1)
			{
				$html .= '<fieldset><legend>' . JText::sprintf('COM_REDFORM_FIELDSET_SIGNUP_NB', $signup) . '</legend>';
			}

			if ($form->activatepayment && isset($options['eventdetails']) && $options['eventdetails']->course_price > 0)
			{
				$html .= '<div class="eventprice" price="' . $options['eventdetails']->course_price . '">'
					. JText::_('COM_REDFORM_Registration_price') . ': ' . $currency . ' ' . $options['eventdetails']->course_price
					. '</div>';
			}

			if ($form->activatepayment && isset($options['booking']) && $options['booking']->course_price > 0)
			{
				$html .= '<div class="bookingprice" price="' . $options['booking']->course_price . '">'
					. JText::_('COM_REDFORM_Registration_price') . ': ' . $currency . ' ' . $options['booking']->course_price
					. '</div>';
			}

			if (isset($options['extrafields']) && count($options['extrafields']))
			{
				foreach ($options['extrafields'] as $field)
				{
					$html .= '<div class="fieldline' . (isset($field['class']) && !empty($field['class']) ? ' ' . $field['class'] : '' ) . '">';
					$html .= '<div class="label">' . $field['label'] . '</div>';
					$html .= '<div class="field">' . $field['field'] . '</div>';
					$html .= '</div>';
				}
			}

			foreach ($fields as $field)
			{
				if (!($app->isAdmin() || $field->published))
				{
					// Only display unpublished fields in backend form
					continue;
				}

				// Init rfield
				$rfield = RdfRfieldFactory::getField($field->id);
				$rfield->setFormIndex($signup);
				$rfield->setUser($user);
				$cleanfield = 'field_' . $field->id;

				// Set value if editing
				if ($answers && isset($answers[($signup-1)]->fields->$cleanfield))
				{
					$value = $answers[($signup-1)]->fields->$cleanfield;
					$rfield->setValue($value, true);
				}

				if (!$rfield->isHidden())
				{
					$html .= '<div class="fieldline type-' . $field->fieldtype . $field->getParam('class', '') . '">';
				}

				if (!$rfield->isHidden())
				{
					$element = "<div class=\"field\">";
				}
				else
				{
					$element = '';
				}

				if (!$rfield->isHidden() && $rfield->displayLabel())
				{
					$label = '<div class="label">' . $rfield->getLabel() . '</div>';
				}
				else
				{
					$label = '';
				}

				$element .= $rfield->getInput();

				if ($rfield->isHidden())
				{
					$html .= $element;
				}
				else
				{
					$html .= $label . $element;
					$html .= '</div>'; // Fieldtype div

					if ($rfield->isRequired() || strlen($field->tooltip))
					{
						$html .= '<div class="fieldinfo">';

						if ($rfield->isRequired())
						{
							$img = JHTML::image(JURI::root() . 'components/com_redform/assets/images/warning.png', JText::_('COM_REDFORM_Required'));
							$html .= ' <span class="editlinktip hasTipField" title="' . JText::_('COM_REDFORM_Required') . '" style="text-decoration: none; color: #333;">' . $img . '</span>';
						}

						if (strlen($field->tooltip) > 0)
						{
							$img = JHTML::image(JURI::root().'components/com_redform/assets/images/info.png', JText::_('COM_REDFORM_ToolTip'));
							$html .= ' <span class="editlinktip hasTipField" title="' . htmlspecialchars($field->field) . '::' . htmlspecialchars($field->tooltip) . '" style="text-decoration: none; color: #333;">' . $img . '</span>';
						}

						$html .= '</div>';
					}

					$html .= '</div>'; // fieldline_ div
				}

			}

			if ($multi > 1)
			{
				$html .= '</fieldset>';
			}

			if (isset($this->_rwfparams['uid']))
			{
				$html .= '<div>' . JText::_('COM_REDFORM_JOOMLA_USER') . ': ' . JHTML::_('list.users', 'uid', $this->_rwfparams['uid'], 1, NULL, 'name', 0 ) . '</div>';
			}
			$html .= '</div>'; // formfield div
		}

		//TODO: redcompetition should use redform core directly
		/* Add any redCOMPETITION values */
		$redcompetition = JRequest::getVar('redcompetition', false);

		if ($redcompetition)
		{
			$html .= '<input type="hidden" name="competition_task" value="' . $redcompetition->task . '" />';
			$html .= '<input type="hidden" name="competition_id" value="' . $redcompetition->competitionid . '" />';
		}

		if ($form->activatepayment && isset($options['selectPaymentGateway']) && $options['selectPaymentGateway'])
		{
			$html .= $this->getGatewaySelect($currency);
		}

		// Get an unique id just for the submission
		$uniq = uniqid();

		/* Add the captcha, only if initial submit */
		if ($form->captchaactive && empty($submit_key))
		{
			JPluginHelper::importPlugin( 'redform_captcha' );
			$captcha = '';
			$dispatcher = JDispatcher::getInstance();
			$results = $dispatcher->trigger( 'onGetCaptchaField', array( &$captcha ) );

			if (count($results))
			{
				$html .= '<div class="fieldline">';
				$html .= '<div class="label"><label>' . JText::_('COM_REDFORM_CAPTCHA_LABEL') . '</label></div>';
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
			$html .= '<input type="hidden" name="submit_key" value="' . $submit_key . '" />';
		}

		$html .= '<input type="hidden" name="nbactive" value="' . $initialActive . '" />';
		$html .= '<input type="hidden" name="form_id" value="' . $form_id . '" />';
		$html .= '<input type="hidden" name="multi" value="' . $multi . '" />';
		$html .= '<input type="hidden" name="' . JSession::getFormToken() . '" value="' . $uniq . '" />';

		if ($currency)
		{
			$html .= '<input type="hidden" name="currency" value="' . $currency . '" />';
		}

		$html .= '</div>'; // div #redform

		return $html;
	}

	/**
	 * saves submitted form data
	 *
	 * @param   string  $integration_key  key, unique key for the 3rd party (allows to prevent deletions from within redform itself for 3rd party, and to find out which submission belongs to which 3rd party...)
	 * @param   array   $options          options for registration
	 * @param   array   $data             data if empty, the $_POST variable is used
	 *
	 * @return   int/array submission_id, or array of submission ids in case of success, 0 otherwise
	 */
	public function saveAnswers($integration_key, $options = array(), $data = null)
	{
		require_once Rdf_PATH_SITE . '/models/redform.php';
		$model = new RedformModelRedform();

		if (!$result = $model->apisaveform($integration_key, $options, $data))
		{
			$this->setError($model->getError());
			return false;
		}

		return $result;
	}


	protected function jsPrice()
	{
		$params = JComponentHelper::getParams('com_redform');

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration('var totalpricestr = "' . JText::_('COM_REDFORM_Total_Price') . "\";\n");
		$doc->addScriptDeclaration('var round_negative_price = ' . ($params->get('allow_negative_total', 1) ? 0 : 1) . ";\n");
		$doc->addScript(JURI::root() . 'media/com_redform/js/form-price.js');
	}

	protected function JsCheck()
	{
		JHtml::_('behavior.formvalidation');
		$doc = JFactory::getDocument();
		$doc->addScript(JURI::root() . 'media/com_redform/js/redform-validate.js');
	}

	/**
	 * adds extra fields from redmember to user object
	 *
	 * @param   object  &$user  object user
	 *
	 * @return object user
	 */
	protected function getRedmemberfields(&$user)
	{
		$path = JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

		if (!file_exists($path))
		{
			return $user;
		}

		require_once $path;

		$all = RedmemberLib::getUserData($user->id);

		$fields = get_object_vars($all);

		foreach ($fields as $key => $value)
		{
			$user->{$key} = $value;
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
	public function getAnswers($reference)
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
				$result = new RdfCoreFormAnswers;
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
		if ($form_id)
		{
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
		if ($sids)
		{
			$this->setSids($sids);
		}

		if (!is_array($this->_sids) || empty($this->_sids))
		{
			return false;
		}

		$answers = $this->getSidsAnswers($this->_sids);
		$form_id = $this->getSidForm($this->_sids[0]);
		$fields  = $this->getFields($form_id);

		if (!$form_id)
		{
			$this->setError(JText::_('COM_REDFORM_FORM_NOT_FOUND'));
			return false;
		}

		$res = array();

		foreach ($answers as $sid => $answer)
		{
			$f = array();

			foreach ($fields as $field)
			{
				$prop = 'field_'.$field->id;
				$field->setValue($answer->$prop);
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
	public function getSubmissionContactEmail($reference, $requires_email = true)
	{
		if (!is_array($reference))
		{
			$sids = $this->getSids($reference);
		}
		else
		{
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
					if ($f->getParam('notify', 1))
					{
						// set to receive notifications ?
						$emails[] = $f->getValue();
					}
				}

				if ($f->fieldtype == 'username')
				{
					$usernames[] = $f->getValue();
				}

				if ($f->fieldtype == 'fullname')
				{
					$fullnames[] = $f->getValue();
				}
			}

			if (!count($emails) && $requires_email)
			{
				// No email field
				return false;
			}

			$result = array();

			foreach ($emails as $k => $val)
			{
				$result[$k]['email']    = $val;
				$result[$k]['username'] = isset($usernames[$k]) ? $usernames[$k] : '';
				$result[$k]['fullname'] = isset($fullnames[$k]) ? $fullnames[$k] : '';

				if (!isset($result[$k]['fullname']) && isset($result[$k]['username']))
				{
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
		$db = JFactory::getDBO();

		$query = " SELECT s.id "
		       . " FROM #__rwf_submitters as s "
		       . " WHERE submit_key = " . $db->quote($key)
		       ;
		$db->setQuery($query);

		return $db->loadColumn();
	}

	function getSidSubmitKey($sid)
	{
		$db = JFactory::getDBO();

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
			RdfHelperLog::simpleLog('invalid condition formatting'. $conditionline);
			return false;
		}

		// first should be the email address
		if (!JMailHelper::isEmailAddress($parts[0])) {
			RdfHelperLog::simpleLog('invalid email in conditional recipient: '. $parts[0]);
			return false;
		}
		$email = $parts[0];

		// then the name of the recipient
		if (!$parts[1]) {
			RdfHelperLog::simpleLog('invalid name in conditional recipient: '. $parts[0]);
			return false;
		}
		$name = $parts[1];

		// then, we shoulg get the field
		$field_id = intval($parts[2]);
		$answer = $answers->getFieldAnswer($field_id);
		if ($answer === false) {
			RdfHelperLog::simpleLog('invalid field id for conditional recipient: '. $parts[1]);
			return false;
		}
		$value = $answer['value'];

		$isvalid = false;
		// then, we should get the function
		switch ($parts[3])
		{
			case 'between':
				if (!isset($parts[5])) {
					RdfHelperLog::simpleLog('missing max value in between conditional recipient: '. $conditionline);
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
				RdfHelperLog::simpleLog('invalid email in conditional recipient: '. $parts[0]);
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
	 * @return int/array submission_id, or array of submission ids in case of success, 0 otherwise
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

		$fields = $this->prepareUserData($userData);

		require_once Rdf_PATH_SITE . '/models/redform.php';
		$model = new RedformModelRedform();
		$model->setFormId($this->_form_id);

		if (!$result = $model->quicksubmit($fields, $integration, $options))
		{
			$this->setError($model->getError());
			return false;
		}

		return $result;
	}

	/**
	 * pulls users data
	 *
	 * should get it from redmember
	 *
	 * @param   int  $user_id  user id
	 *
	 * @return JUser
	 */
	protected function getUserData($user_id)
	{
		$user = JFactory::getUser($user_id);
		$this->getRedmemberfields($user);

		return $user;
	}

	/**
	 * prepares data for saving
	 *
	 * @param   JUser  $userData    user
	 * @param   int    $form_index  form index
	 *
	 * @return array
	 */
	protected function prepareUserData($userData, $form_index = 1)
	{
		$fields = $this->getFields();

		foreach ($fields as $field)
		{
			$field->setFormIndex($form_index);
			$field->setUser($userData);
			$field->setValue(null, true);
		}

		return $fields;
	}

	/**
	 * Return submission(s) price(s) associated to a submit_key
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return array indexed by submitter_id
	 */
	public static function getSubmissionPrice($submit_key)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('s.id, s.submit_key, s.price, s.currency');
		$query->from('#__rwf_submitters AS s');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = s.form_id');
		$query->where('s.submit_key = ' . $db->q($submit_key));

		$db->setQuery($query);
		$res = $db->loadObjectList('s.id');

		return ($res);
	}

	/**
	 * Return field for gateway select
	 *
	 * @param string   $currency  currency to use as filtering
	 *
	 * @return bool|string
	 */
	protected function getGatewaySelect($currency)
	{
		$helper = new RdfCorePaymentGateway;

		$config = new stdclass;
		$config->currency = $currency;
		$options = $helper->getOptions($config);

		if (!$options)
		{
			return false;
		}

		if (count($options) == 1)
		{
			// Just a hidden field
			$html = '<input name="gw" type="hidden" value="' . $options[0]->value . '"/>';
		}
		else
		{
			$select = JHtml::_('select.genericlist', $options, 'gw');

			$html = '<div class="fieldline gateway-select">';
			$html .= '<div class="label">' . JText::_('COM_REDFORM_SELECT_PAYMENT_METHOD') . '</div>';
			$html .= '<div class="field">' . $select . '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Return true if submission was paid
	 *
	 * @param   string  $submit_key  submission submit key
	 *
	 * @return mixed
	 */
	public function isPaidSubmitkey($submit_key)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('p.paid, p.status');
		$query->from('#__rwf_payment AS p');
		$query->where('p.submit_key = ' . $db->quote($submit_key));
		$query->order('p.id DESC');

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res->paid;
	}

	/**
	 * Load javascript for multiple form
	 *
	 * @return void
	 */
	protected function loadMultipleFormScript()
	{
		JText::script('COM_REDFORM_MAX_SIGNUP_REACHED');
		JText::script('COM_REDFORM_FIELDSET_SIGNUP_NB');
		JFactory::getDocument()->addScript(JURI::root() . '/media/com_redform/js/form-multiple.js');
	}

	/**
	 * Load javascript for form price
	 *
	 * @return void
	 */
	protected function loadPriceScript()
	{
		$params = JComponentHelper::getParams('com_redform');

		JText::script('COM_REDFORM_Total_Price');
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration('var round_negative_price = ' . ($params->get('allow_negative_total', 1) ? 0 : 1) . ";\n");
		$doc->addScript(JURI::root() . '/media/com_redform/js/form-price.js');
	}

	/**
	 * Load javascript for form validation
	 *
	 * @return void
	 */
	protected function loadCheckScript()
	{
		JHtml::_('behavior.formvalidation');
	}
}
