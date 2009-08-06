<?php

class rfanswers
{
	public $_answer_id = 0;
	
	public $_fields = null;
	
	public $_values = null;
	
	public $_form_id = 0;
	
	public $_submitter_email = null;
	
  public $_fullname = null;
	
	public $_listnames = array();
	
	public function __construct()
	{
		
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

  public function getFullname()
  {
    return $this->_fullname;
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
      case 'wysiwyg':
        $answer = $postedvalue['wysiwyg'];
        break;
      case 'fullname':
        $answer = $postedvalue['fullname'][0];
        $this->_fullname = $answer;
        break;
      case 'username':
        $answer = $postedvalue['username'][0];
        break;
      case 'email':
        // TODO: store submitter email and listnames
        $answer = $postedvalue['email'][0];
        $this->_submitter_email = $answer;
        if (array_key_exists('listnames', $postedvalue['email'])) {
          $this->_listnames[] = $postedvalue['email']['listnames'];
        }
        break;
      case 'text':
        $answer = $postedvalue['text'][0];
        break;
      case 'select':
        $answer = $postedvalue['select'][0];
        break;
      case 'checkbox':
        $submittervalues = '';
        foreach ($postedvalue['checkbox'] as $key => $submitteranswer) {
          $submittervalues .= $submitteranswer."~~~";
        }
        $answer = substr($submittervalues, 0, -3);
        break;
      case 'multiselect':
        $submittervalues = '';
        foreach ($postedvalue['multiselect'] as $key => $submitteranswer) {
          $submittervalues .= $submitteranswer."~~~";
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
    }
    $this->_fields[] = $field;
    $this->_values[] = $answer;
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
    $db = &JFactory::getDBO();
    $answer = '';
    
    /* Get the file path for file upload */
    $query = "SELECT value
          FROM #__rwf_configuration
          WHERE name = ".$db->Quote('filelist_path');
    $db->setQuery($query);
    $filepath = $db->loadResult();
    
    /* Check if the folder exists */
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');
    
    $fullpath = $filepath.DS.'redform_'.$this->_form_id;
    if (!JFolder::exists($fullpath)) {
      if (!JFolder::create($fullpath)) {
        JError::raiseWarning(0, JText::_('CANNOT_CREATE_FOLDER').' '.$fullpath);
        $status = false;
        return false;
      }
    }
    clearstatcache();
    if (JFolder::exists($fullpath)) {
      if (JFile::exists($fullpath.DS.basename($field['name']['fileupload'][0]))) {
        JError::raiseWarning(0, JText::_('FILENAME_ALREADY_EXISTS').': '.basename($field['name']['fileupload'][0]));
        return false;
      }
      else {
        /* Start processing uploaded file */
        if (is_uploaded_file($field['tmp_name']['fileupload'][0])) {
          if (JFolder::exists($fullpath) && is_writable($fullpath)) {
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
   * sve the answer
   *
   * @param array $params: submit_key, xref, etc...
   * @return true on success
   */
  public function save($params = array())
  {
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
      
    if ($this->_answer_id) // answers were already recorder, update them
    {
    	$q = "UPDATE ".$db->nameQuote('#__rwf_forms_'. $this->_form_id);
    	$set = array();
    	foreach ($this->_fields as $ukey => $col) {
    		$set[] = $db->nameQuote('field_'. $col->id) ." = ". $db->Quote($this->_values[$ukey]);
    	}
    	$q .= ' SET '. implode(', ', $set);
    	$q .= " WHERE ID = ". $this->_answer_id;
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
    		JError::raiseWarning('error', JText::_('Cannot save form answers'));
    		RedformHelperLog::simpleLog(JText::_('Cannot save form answers').' '.$db->getErrorMsg());
    		return false;
    	}
    	$this->_answer_id = $db->insertid();

    	return $this->updateSubmitter($params);
    }
    return true;
  }
  
  function updateSubmitter($params = array())
  {
    $db = &JFactory::getDBO();
    
  	// prepare data for submitter record
  	$submitterdata['answer_id'] = $this->_answer_id;
  	if (empty($params['submit_key'])) {
  		JError::raiseError(0, JText::_('ERROR SUBMIT KEY MISSING'));
  	}
  	$submitterdata['submit_key']  = $params['submit_key'];
  	$submitterdata['rawformdata'] = $params['rawformdata'];
    $submitterdata['form_id'] = $this->_form_id;

  	/* Store the submitter details */
  	$row = & JTable::getInstance('Submitters', 'Table');

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
  	return true;
  }
  
  function getAnswers()
  {
  	$answers = array();
  	foreach ($this->_fields as $k => $field)
  	{
  		$answers[] = array( 'field' => $field->field, 'value' => $this->_values[$k] );
  	}
  	return $answers;
  }
}
?>