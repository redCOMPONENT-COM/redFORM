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
jimport( 'joomla.html.parameter' );

class plgContentRedform extends JPlugin {
	/**
	 * specific redform plugin parameters
	 *
	 * @var JParameter object
	 */
	var $_rwfparams = null;
	
	var $_rfcore = null;
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct( &$subject, $config = array() )
	{
		parent::__construct( $subject, $config );     
	}	
	
	
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	boolean true on success
	 * @since	1.6
	 */
	public function onContentPrepare($context,&$row, &$params, $page = 0)
	{
    return $this->_process($row, array());		
	}
	
	protected function _process(&$row, $params = array()) 
	{
		if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'redform.core.php')) {
			JError::raiseWarning(0, JText::_('COM_REDFORM_COMPONENT_REQUIRED_FOR_REDFORM_PLUGIN'));
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

			// load form javascript
			if (JRequest::getVar('format', 'html') == 'html') {
				JHTML::_('behavior.tooltip');
				jimport('joomla.html.html');
			}
			
			/* Execute the code */
			$row->text = preg_replace_callback( $regex, array($this, 'FormPage'), $row->text );
		}
		return true;
	}
	
	/**
	 * Create the forms
	 *
	 * $matches[0] = form ID
	 * $matches[1] = Number of sign ups
	 */
	protected function FormPage ($matches) 
	{
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
			if (max($user->get('_authLevels')) < $form->access) {
				return JText::_('COM_REDFORM_LOGIN_REQUIRED');
			}		
	
			/* Check if the number of sign ups is set, otherwise default to 1 */
			if (!isset($matches[1])) $matches[1] = 1;
			
			if (!isset($form->id)) {
				return JText::_('COM_REDFORM_No_active_form_found');
			}
			else {				
				/* Draw the form form */
				return $this->getFormForm($form, $matches[1]);
	
			}
		}
	}
		
	/**
	 * returns form object
	 * 
	 * @param int $form_id
	 * @return object
	 */
	protected function getForm($form_id) 
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
	protected function _checkFormActive($form)
	{
		if (strtotime($form->startdate) > time()) {
			return JText::_('COM_REDFORM_FORM_NOT_STARTED');
		}
		else if ($form->formexpires && strtotime($form->enddate) < time()) {
			return JText::_('COM_REDFORM_FORM_EXPIRED');
		}
		return true;
	}
	
	protected function getFormFields($form_id) 
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
	
	protected function getFormValues($field_id) 
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
	
	protected function replace_accents($str) 
	{
	  $str = htmlentities($str, ENT_COMPAT, "UTF-8");
	  $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|elig|slash|ring);/','$1',$str);
	  return html_entity_decode($str);
	}
	
	protected function getFormForm($form, $multi=1) 
	{    
		if (JRequest::getVar('format', 'html') == 'html') 
		{
			return $this->_rfcore->displayForm($form->id, null, $multi);
		}
		else if (JRequest::getVar('format', 'html') == 'pdf')
		{
			JRequest::setVar('pdfform', $this->getPDFForm($form, $multi));
		}
	}
	
	protected function getProductinfo() 
	{
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
	protected function getRedmemberfields(&$user)
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
	
	protected function getPDFForm($form, $multi = 1)
	{	
		/* Get the field details */
		$fields = $this->getFormFields($form->id);
		
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
			$pdfform->Cell(0, 10, JText::_('COM_REDFORM_ATTENDEE').' '.$signup, 0, 1, 'L');
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