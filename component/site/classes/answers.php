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

class rfanswers
{
  public $_test = 0;
  
	public $_answer_id = 0;
	
	public $_fields = null;
	
	public $_values = null;
	
  public $_types = null;
	
	public $_form_id = 0;
	
	public $_submitter_email = null;
	
  public $_fullname = null;
  
  public $_username = null;
	
	public $_listnames = array();
	
  public $_recipients = array();
  
  private $_price = 0;
  
  private $_answers = null;
  
  private $_isnew = true;
  
  private $_db;
  
	public function __construct()
	{
		$this->_db = & JFactory::getDBO();
	}
	
	public function setFormId($id)
	{
		$this->_form_id = (int) $id;
	}
	

  public function setAnswerId($id)
  {
    $this->_answer_id = (int) $id;
  }

  public function getAnswerId($id)
  {
    return $this->_answer_id;
  }
  
  public function getListNames()
  {
  	return $this->_listnames;
  }
  
  public function getSubmitterEmail()
  {
    return $this->_submitter_email;
  }

  public function getRecipients()
  {
    return $this->_recipients;
  }
  
  public function getFullname()
  {
    return $this->_fullname;
  }
  
  public function getUsername()
  {
    return $this->_username;
  }
  
  public function initPrice($initial)
  {
  	$this->_price = $initial;
  }
  
  public function getPrice()
  {
  	return $this->_price;
  }
  
  public function isNew()
  {
  	return $this->_isnew;
  }
  public function setNew($val)
  {
  	$this->_isnew = $val ? true : false;
  }
	
  public function addPostAnswer($field, $postedvalue)
  {
    $db = &JFactory::getDBO();
    // get the type, it is the field first and only key
    $keys = array_keys($postedvalue);
    $answer = '';
    switch ($keys[0])
    {
      case 'textarea':
        $answer = $postedvalue['textarea'];
        break;
      case 'date':
        $answer = $postedvalue['date'];
        break;
      case 'wysiwyg':
        $answer = $postedvalue['wysiwyg'];
        break;
      case 'fullname':
        $answer = $postedvalue['fullname'][0];
        $this->_fullname = $answer;
        break;
      case 'username':
        $answer = $postedvalue['username'][0];
        $this->_username = $answer;
        break;
      case 'email':
        // TODO: store submitter email and listnames
        $answer = $postedvalue['email'][0];
        $this->_submitter_email = $answer;
        if (array_key_exists('listnames', $postedvalue['email'])) {
          $this->_listnames[$field->id] = array('email' => $answer, 'lists' => $postedvalue['email']['listnames']);
        }
        break;
      case 'text':
        $answer = $postedvalue['text'][0];
        break;
      case 'select':
        $answer = $postedvalue['select'][0];
      	foreach ($field->values as $v)
      	{
      		if ($v->value == $answer) {
        		$this->_price += $v->price;
      		}
      	}
        break;
      case 'checkbox':
        $submittervalues = '';
        foreach ($postedvalue['checkbox'] as $key => $submitteranswer) {
          $submittervalues .= $submitteranswer."~~~";
	      	foreach ($field->values as $v)
	      	{
	      		if ($v->value == $submitteranswer) {
	        		$this->_price += $v->price;
	      		}
	      	}
        }
        $answer = substr($submittervalues, 0, -3);
        break;
      case 'multiselect':
        $submittervalues = '';
        foreach ($postedvalue['multiselect'] as $key => $submitteranswer) {
          $submittervalues .= $submitteranswer."~~~";
	      	foreach ($field->values as $v)
	      	{
	      		if ($v->value == $submitteranswer) {
	        		$this->_price += $v->price;
	      		}
	      	}
        }
        $answer = substr($submittervalues, 0, -3);
        break;
      case 'recipients':
        $submittervalues = '';
        foreach ($postedvalue['recipients'] as $key => $submitteranswer) {
          $submittervalues .= $submitteranswer."~~~";
          $this->_recipients[] = $submitteranswer;
        }
        $answer = substr($submittervalues, 0, -3);
        break;
      case 'name':
        if (in_array('fileupload', array_keys($postedvalue['name']))) {
          $answer = $this->_fileupload($postedvalue);
        }
        break;        
      case 'radio':
      	/* Get the real value from the database */
      	$q = "SELECT value
                FROM #__rwf_values
                WHERE id = ".$postedvalue['radio'][0];
      	$db->setQuery($q);
      	$answer = $db->loadResult();
      	
      	foreach ($field->values as $v)
      	{
      		if ($v->id == $postedvalue['radio'][0]) {
        		$this->_price += $v->price;
      		}
      	}
      	break;
      	
      case 'price':
        if (count($field->values)) {
	        $answer = $field->values[0]->value;         	
        }
        else {
        	$answer = $postedvalue['price'][0];
        }
        $this->_price += $answer;
        break;
    }
    $this->_fields[] = $field;
    $this->_values[] = $answer;
    $this->_types[] = ($keys[0] == 'name' ? 'file' : $keys[0]);
    return $answer;
  }
  
  /**
   * manages file post field
   *
   * @param array $field
   * @return string answer
   */
  function _fileupload($field)
  {    
    /* Check if the folder exists */
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');
    
    $db = &JFactory::getDBO();
    $answer = '';
    
    /* Get the file path for file upload */
    $query = ' SELECT c.value, f.formname '
           . ' FROM #__rwf_configuration AS c, #__rwf_forms AS f '
           . ' WHERE name = '.$db->Quote('filelist_path')
           . '   AND f.id = '.$db->Quote($this->_form_id)
          ;
    $db->setQuery($query);
    $res = $db->loadObject();
    $filepath = $res->value;
    $folder   = JFile::makeSafe($res->formname);
    
    $fullpath = $filepath.DS.$folder;
    if (!JFolder::exists($fullpath)) 
    {
      if (!JFolder::create($fullpath)) 
      {
        JError::raiseWarning(0, JText::_('CANNOT_CREATE_FOLDER').': '.$fullpath);
        $status = false;
        return false;
      }
    }
    clearstatcache();
    if (JFolder::exists($fullpath)) 
    {
      if (JFile::exists($fullpath.DS.basename($field['name']['fileupload'][0]))) {
        JError::raiseWarning(0, JText::_('FILENAME_ALREADY_EXISTS').': '.basename($field['name']['fileupload'][0]));
        return false;
      }
      else 
      {
        /* Start processing uploaded file */
        if (is_uploaded_file($field['tmp_name']['fileupload'][0])) 
        {
          if (JFolder::exists($fullpath) && is_writable($fullpath)) 
          {
            if (move_uploaded_file($field['tmp_name']['fileupload'][0], $fullpath.DS.basename($field['name']['fileupload'][0]))) {
              $answer = $fullpath.DS.basename($field['name']['fileupload'][0]);
            }
            else {
              JError::raiseWarning(0, JText::_('CANNOT_UPLOAD_FILE'));
              return false;
            }
          }
          else {
            JError::raiseWarning(0, JText::_('FOLDER_DOES_NOT_EXIST'));
            return false;
          }
        }
      }
    }
    else {
      JError::raiseWarning(0, JText::_('FOLDER_DOES_NOT_EXIST'));
      return false;
    }
    return $answer;
  }
  
  /**
   * save the answer
   *
   * @param array $params: submit_key, xref, etc...
   * @return true on success
   */
  public function save($params = array())
  {
  	$mainframe = Jfactory::getApplication();
  	$db = & JFactory::getDBO();
  	
  	if (empty($this->_form_id)) {
  		JError::raiseError(0, JText::_('ERROR NO FORM ID'));
  	}
    
  	if (!count($this->_fields)) {
  		return true;
  	}
  	
  	$values = array();
  	$fields = array();
  	foreach ($this->_fields as $v) {
  		$fields[] = $db->nameQuote('field_'. $v->id);
  	}
  	foreach ($this->_values as $v) {
  		$values[] = $db->Quote($v);
  	}
  	
  	// we need to make sure all table fields are updated: typically, if a field is of type checkbox, if not checked it won't be posted, hence we have to set the value to empty
  	$q = " SHOW COLUMNS FROM " . $db->nameQuote('#__rwf_forms_'. $this->_form_id);
  	$db->setQuery($q);
  	$columns = $db->loadResultArray();
  	foreach ($columns as $col)
  	{
  		if (strstr($col, 'field_') && !in_array($db->nameQuote($col), $fields)) 
  		{
  			$fields[] = $db->nameQuote($col);
  			$values[] = $db->Quote('');  			
  		}
  	}
  	
    if ($this->_answer_id) // answers were already recorder, update them
    {
    	$q = "UPDATE ".$db->nameQuote('#__rwf_forms_'. $this->_form_id);
    	$set = array();
    	foreach ($fields as $ukey => $col) {
    		$set[] = $col ." = ". $values[$ukey];
    	}
    	$q .= ' SET '. implode(', ', $set);
    	$q .= " WHERE ID = ". $this->_answer_id;
    	$db->setQuery($q);
    	
    	if (!$db->query()) {
    		JError::raiseError(0, JText::_('UPDATE ANSWERS FAILED'));
        RedformHelperLog::simpleLog(JText::_('Cannot update answers').' '.$db->getErrorMsg());
    	}
    	$this->setPrice();
    }
    else
    {
    	/* Construct the query */
    	$q = "INSERT INTO ".$db->nameQuote('#__rwf_forms_'. $this->_form_id)."
            (" . implode(', ', $fields) . ")
            VALUES (" . implode(', ', $values) . ")";
    	$db->setQuery($q);
    	
    	if (!$db->query()) {
    		/* We cannot save the answers, do not continue */
			if (stristr($db->getErrorMsg(), 'duplicate entry')) {
				JRequest::setVar('ALREADY_ENTERED', true);
				$mainframe->enqueueMessage(JText::_('ALREADY_ENTERED'), 'error');
			}
			else $mainframe->enqueueMessage(JText::_('Cannot save form answers').' '.$db->getErrorMsg(),'error');
    		/* We cannot save the answers, do not continue */
    		RedformHelperLog::simpleLog(JText::_('Cannot save form answers').' '.$db->getErrorMsg());
    		return false;
    	}
    	$this->_answer_id = $db->insertid();
    	$res = $this->updateSubmitter($params);
    	$this->setPrice();
    	return $res;
    }
    return true;
  }
  
  /**
   * new save function for new lib
   * @param $params
   * @return int submitter_id
   */
  function savedata($params = array())
  {
  	$mainframe = Jfactory::getApplication();
  	$db = & JFactory::getDBO();
  	
  	
  	if (empty($this->_form_id)) {
  		JError::raiseError(0, JText::_('ERROR NO FORM ID'));
  	}
    
  	if (!count($this->_fields)) {
  		return true;
  	}
  	
  	if ( isset($params['sid']) )
  	{
  		$this->_isnew = intval($params['sid']) == 0;
  		$sid = intval($params['sid']);
  	}
  	else {
  		$this->_isnew = true;
  		$sid = 0;
  	}

  	$values = array();
  	$fields = array();
  	foreach ($this->_fields as $v) {
  		$fields[] = $db->nameQuote('field_'. $v->id);
  	}
  	foreach ($this->_values as $v) {
  		$values[] = $db->Quote($v);
  	}
  	
  	// we need to make sure all table fields are updated: typically, if a field is of type checkbox, if not checked it won't be posted, hence we have to set the value to empty
  	$q = " SHOW COLUMNS FROM " . $db->nameQuote('#__rwf_forms_'. $this->_form_id);
  	$db->setQuery($q);
  	$columns = $db->loadResultArray();
  	foreach ($columns as $col)
  	{
  		if (strstr($col, 'field_') && !in_array($db->nameQuote($col), $fields)) 
  		{
  			$fields[] = $db->nameQuote($col);
  			$values[] = $db->Quote('');  			
  		}
  	}  	
  	
    if ($sid) // answers were already recorded, update them
    {
    	$submitter = $this->getSubmitter($sid);
    	
    	$q = "UPDATE ".$db->nameQuote('#__rwf_forms_'. $this->_form_id);
    	$set = array();
    	foreach ($fields as $ukey => $col) {
    		$set[] = $col ." = ". $values[$ukey];
    	}
    	$q .= ' SET '. implode(', ', $set);
    	$q .= " WHERE ID = ". $submitter->answer_id;
    	$db->setQuery($q);
    	
    	if (!$db->query()) {
    		JError::raiseError(0, JText::_('UPDATE ANSWERS FAILED'));
        RedformHelperLog::simpleLog(JText::_('Cannot update answers').' '.$db->getErrorMsg());
    	}
    }
    else
    {
    	/* Construct the query */
    	$q = "INSERT INTO ".$db->nameQuote('#__rwf_forms_'. $this->_form_id)."
            (" . implode(', ', $fields) . ")
            VALUES (" . implode(', ', $values) . ")";
    	$db->setQuery($q);
    	
    	if (!$db->query()) {
    		/* We cannot save the answers, do not continue */
			if (stristr($db->getErrorMsg(), 'duplicate entry')) {
				JRequest::setVar('ALREADY_ENTERED', true);
				$mainframe->enqueueMessage(JText::_('ALREADY_ENTERED'), 'error');
			}
			else $mainframe->enqueueMessage(JText::_('Cannot save form answers').' '.$db->getErrorMsg(),'error');
    		/* We cannot save the answers, do not continue */
    		RedformHelperLog::simpleLog(JText::_('Cannot save form answers').' '.$db->getErrorMsg());
    		return false;
    	}
    	$this->_answer_id = $db->insertid();
    	$sid = $this->updateSubmitter($params);
    }
    $this->setPrice();
    return $sid;
  }
  
  function updateSubmitter($params = array())
  {
    $db = &JFactory::getDBO();
    $mainframe = & JFactory::getApplication();
    
  	// prepare data for submitter record
  	$submitterdata['answer_id'] = $this->_answer_id;
  	if (empty($params['submit_key'])) {
  		JError::raiseError(0, JText::_('ERROR SUBMIT KEY MISSING'));
  	}
  	$submitterdata = array_merge($submitterdata, $params);
    $submitterdata['form_id'] = $this->_form_id;

  	/* Store the submitter details */
  	$row = & JTable::getInstance('Submitters', 'RedformTable');

  	if (isset($params['xref']) && (int) $params['xref'])
  	{
  		$submitterdata['xref'] = $params['xref'];
  		/* Add some settings */
  		/* Get activate setting for event */
  		$q = "SELECT activate
              FROM #__redevent_events AS e
              LEFT JOIN #__redevent_event_venue_xref AS x
              ON e.id = x.eventid
              WHERE x.id = ". $params['xref'];
  		$db->setQuery($q);
  		$activate = $db->loadResult();

  		/* Check if the user needs to confirm */
  		if ($activate) {
  			$submitterdata['confirmed'] = 0;
  		}
  		else {
  			/* Automatically confirm user */
  			$submitterdata['confirmed'] = 1;
  			$submitterdata['confirmdate'] = gmdate('Y-m-d H:i:s');
  		}
  	}
  	else {
  		/* Automatically confirm user */
  		$submitterdata['confirmed'] = 1;
  		$submitterdata['confirmdate'] = gmdate('Y-m-d H:i:s');
  	}

  	if (!$row->bind($submitterdata)) {
  		$mainframe->enqueueMessage(JText::_('There was a problem binding the submitter data'), 'error');
  		RedformHelperLog::simpleLog(JText::_('There was a problem binding the submitter data').': '.$row->getError());
  		return false;
  	}
  	/* Set the date */
  	$row->submission_date = date('Y-m-d H:i:s' , time());
  	$row->submitternewsletter = ($this->_listnames && count($this->_listnames)) ? 1 : 0;

  	/* pre-save checks */
  	if (!$row->check()) {
  		$mainframe->enqueueMessage(JText::_('There was a problem checking the submitter data'), 'error');
  		RedformHelperLog::simpleLog(JText::_('There was a problem checking the submitter data').': '.$row->getError());
  		return false;
  	}

  	/* save the changes */
  	if (!$row->store()) {
  		if (stristr($db->getErrorMsg(), 'Duplicate entry')) {
  			$mainframe->enqueueMessage(JText::_('You have already entered this form'), 'error');
  			RedformHelperLog::simpleLog(JText::_('You have already entered this form'));
  		}
  		else {
  			$mainframe->enqueueMessage(JText::_('There was a problem storing the submitter data'), 'error');
  			RedformHelperLog::simpleLog(JText::_('There was a problem storing the submitter data').': '.$row->getError());
  		}
  		return false;
  	}
  	return $row->id;
  }
  
  // set price corresponding to answers in submitters table
  function setPrice()
  {
  	if (!$this->_price || !$this->_answer_id || !$this->_form_id) {
  		return false;
  	}
  	
    $db = &JFactory::getDBO();
  	$query = ' UPDATE #__rwf_submitters SET price = '. $db->Quote($this->_price)
  	       . ' WHERE form_id = '.$db->Quote($this->_form_id)
  	       . '   AND answer_id = '.$db->Quote($this->_answer_id)
  	       ;
  	$db->setQuery($query);
  	$res = $db->query();
  	if (!$res) {
  		echo '<pre>';print_r($db->getErrorMsg()); echo '</pre>';exit;
  	}
//  	exit($db->getQuery());
  	return $res;
  }
  
  function getAnswers()
  {
  	$answers = array();
  	foreach ($this->_fields as $k => $field)
  	{
  		$answers[] = array( 'field' => $field->field, 'field_id' => $field->id, 'value' => $this->_values[$k], 'type' => $this->_types[$k] );
  	}
  	return $answers;
  }
  
  /**
   * loads answers of specified submitter
   * 
   * @param int $submitter_id
   * @return true on success
   */
  function getSubmitterAnswers($submitter_id)
  {
    $db = &JFactory::getDBO();
  	$sid = (int) $submitter_id;
  	
  	// get submission details first, to get the fieds
  	$query = ' SELECT s.* FROM #__rwf_submitters AS s WHERE s.id = '.$db->quote($sid);
  	$db->setQuery($query);
  	$submitter = $db->loadObject();
  	
  	if (!$submitter) {
  		Jerror::raisewarning(0, Jtext::_('unknown submitter'));
  		return false;
  	}
  	
  	// get fields
  	$query = ' SELECT f.* FROM #__rwf_fields AS f '
  	       . ' WHERE f.form_id = '.$db->quote($submitter->form_id)
  	       . ' AND f.published = 1 '
  	       . ' ORDER BY f.ordering ';
  	$db->setQuery($query);
  	$fields = $db->loadObjectList('id');
  	
  	$fnames = array();
  	foreach ($fields as $f) {
  		$fnames[] = $db->namequote('f.field_'.$f->id);
  	}
  	
  	// get values
  	$query = ' SELECT '.implode(',' , $fnames)
  	       . ' FROM #__rwf_forms_'.$submitter->form_id.' AS f '
  	       . ' WHERE f.id = '.$db->quote($submitter->answer_id)
  	       ;
  	$db->setQuery($query);
  	$answers = $db->loadObject();
  
  	if (!$answers) {
  		Jerror::raisewarning(0, Jtext::_('error getting submitter answers'));
  		return false;
  	}
  	
  	foreach ($fields as $id => $f)
  	{
  		$property = 'field_'.$f->id;
  		if (isset($answers->$property)) {
  			$fields[$id]->answer = $answers->$property;
  		}
  	}
  	return $fields;
  }
  
  function getSubmitter($id)
  {  	
  	$query = ' SELECT s.* ' 
  	       . ' FROM #__rwf_submitters AS s ' 
  	       . ' WHERE s.id = ' . $this->_db->Quote($id);
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObject();
  	return $res;
  }
  
  function toSession()
  {
  	$answers = new stdclass();
  	foreach ($this->_fields as $k => $field)
  	{
  		$tablefield = 'field_'.$field->id;
  		$answers->$tablefield = $this->_values[$k];
  	}
  	return $answers;  	
  }
}
?>