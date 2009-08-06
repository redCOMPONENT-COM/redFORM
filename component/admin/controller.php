<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * redFORM default controller
 */

jimport('joomla.application.component.controller');

/**
 * redFORM Component Controller
 */
class RedformController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
  function display()
  {
    // set a default view
    if (JRequest::getVar('view', '') == '') {
      JRequest::setVar('view', 'forms');    
    }
    parent::display();
  }
  
  /**
   * Clears log file
   *
   */
  function clearlog()
  {
    RedformHelperLog::clear();
    $msg = JText::_('LOG CLEARED');
    $this->setRedirect('index.php?option=com_redform&view=log', $msg);
    $this->redirect();
  }
  
  /**
   * restore the rwf_forms_x tables after bug in sanitize function (4.0 and 4.0.1)
   *
   */
  function unsanitize()
  {
    require_once JPATH_COMPONENT_SITE.DS.'classes'.DS.'answers.php';
    $db = &JFactory::getDBO();
    
    $query = ' SELECT form_id, answer_id, rawformdata FROM #__rwf_submitters ';
    $db->setQuery($query);
    $records = $db->loadObjectList();
    
    foreach ($records as $r)
    {
      $posted = unserialize($r->rawformdata);
//      print_r($posted);
      foreach ($posted as $key => $value) 
      {
        if ((strpos($key, 'field') === 0)) {
        	$new_key = explode('_', $key);
          $posted[$new_key[0]] = $value;
        }
      }
      
      // new answers object
      $answers = new rfanswers();
      $answers->setFormId($r->form_id);
      $answers->setAnswerId($r->answer_id);
      
      /* Load the fields */
	    $q = "SELECT id 
	        FROM ".$db->nameQuote('#__rwf_fields')."
	        WHERE form_id = ".$r->form_id
	         ;
	    $db->setQuery($q);
    
      $fieldlist = $db->loadObjectList('id');
      
      /* Build up field list */
      foreach ($fieldlist as $key => $field)
      {
        if (isset($posted['field'.$key]))
        {
          /* Get the answers */
          $answers->addPostAnswer($field, $posted['field'.$key]);
        }
      }
      // this 'anwers' were already posted
//      print_r($answers);exit;
      // update answers
      $answers->save();
    }
  }
}
?>
