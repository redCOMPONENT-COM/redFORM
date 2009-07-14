<?php
/**
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 * Fields model
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.application.component.model' );

/**
 * Fields Model
 */
class RedformModelField extends JModel 
{
  /**
   * Field id
   *
   * @var int
   */
  protected $_id = null;

  /**
   * Form data array
   *
   * @var array
   */
  protected $_data = null;

  /**
   * Constructor
   *
   * @since 0.9
   */
  function __construct()
  {
    parent::__construct();

    $cid = JRequest::getVar( 'cid', array(0), '', 'array' );
    JArrayHelper::toInteger($cid, array(0));
    $this->setId($cid[0]);
  }

  /**
   * Method to set the identifier
   *
   * @access  public
   * @param int event identifier
   */
  function setId($id)
  {
    // Set event id and wipe data
    $this->_id      = $id;
    $this->_data  = null;
  }
  	
	function getFormsOptions()
	{
		$query = "SELECT id AS value, formname AS text, startdate FROM #__rwf_forms";
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
  /**
   * get the data
   *
   * @return object
   */
  function &getData()
  {
    if ($this->_loadData())
    {

    }
    else  $this->_initData();

    return $this->_data;
  }
	   
   /**
    * Retrieve a field to edit
    */
   function _loadData() 
   {
	    // Lets load the content if it doesn't already exist
	    if (empty($this->_data))
	    {
	      $query = 'SELECT *'
	          . ' FROM #__rwf_fields'
	          . ' WHERE id = '.$this->_id
	          ;
	      $this->_db->setQuery($query);
	      $this->_data = $this->_db->loadObject();
	      return (boolean) $this->_data;
	    }
	    return true;
   }
   
  /**
   * load default data
   *
   * @return unknown
   */
  function _initData()
  {
    $this->_data = & JTable::getInstance('Fields', 'Table');
    return $this->_data;
  }

  /**
   * Tests if the element is checked out
   *
   * @access  public
   * @param int A user id
   * @return  boolean True if checked out
   * @since 0.9
   */
  function isCheckedOut( $uid=0 )
  {
    if ($this->_loadData())
    {
      if ($uid) {
        return ($this->_data->checked_out && $this->_data->checked_out != $uid);
      } else {
        return $this->_data->checked_out;
      }
    } elseif ($this->_id < 1) {
      return false;
    } else {
      JError::raiseWarning( 0, 'Unable to Load Data');
      return false;
    }
  }

  /**
   * Method to checkout/lock the item
   *
   * @access  public
   * @param int $uid  User ID of the user checking the item out
   * @return  boolean True on success
   * @since 0.9
   */
  function checkout($uid = null)
  {
    if ($this->_id)
    {
      // Make sure we have a user id to checkout the event with
      if (is_null($uid)) {
        $user =& JFactory::getUser();
        $uid  = $user->get('id');
      }
      // Lets get to it and checkout the thing...
      $row = & $this->getTable('Fields', 'Table');
      return $row->checkout($uid, $this->_id);
    }
    return false;
  }
  

  /**
   * Method to checkin/unlock the item
   *
   * @access  public
   * @return  boolean True on success
   * @since 0.9
   */
  function checkin()
  {
    if ($this->_id)
    {
      $row = & $this->getTable('Fields', 'Table');
      return $row->checkin($this->_id);
    }
    return false;
  }

  /**
   * Save a field
   */
  function store($data)
  {
  	$row = $this->getTable('Fields', 'Table');
  	$oldrow = $this->getTable('Fields', 'Table');
  	$field_id = JRequest::getInt('id', false);
  	/* Check if a field moved form */
  	if ($field_id)  {
  		$oldrow->load($field_id);
  	}

  	/* Get the posted data */
  	$post = $data;

  	/* Check field order */
  	$row->load($field_id);
  	if (empty($row->ordering)) $post['ordering'] = $row->getNextOrder($row->form_id);

  	if (!$row->bind($post)) {
  		$this->setError(JText::_('There was a problem binding the field data'), 'error');
  		return false;
  	}
  	 
  	/* pre-save checks */
  	if (!$row->check()) {
  		$this->setError(JText::_('There was a problem checking the field data'), 'error');
  		return false;
  	}

  	/* save the changes */
  	if (!$row->store()) {
  		$this->setError(JText::_('There was a problem storing the field data'), 'error');
  		return false;
  	}
  	 
  	/* Add form table */
  	$this->AddFieldTable($row, $oldrow);

  	return $row;
  }
      	
	/**
	 * Adds a table if it doesn't exist yet
	 *
	 * @param object field table record object with updated value
	 * @param object previously recorded field table record object corresponding to current field id
	 */
	private function AddFieldTable($row, $oldrow) 
	{
		$db = & JFactory::getDBO();
		/* Make sure that field name is valid */
		$field = str_replace(' ', '', strtolower($row->field));
		$oldfield = str_replace(' ', '', strtolower($oldrow->field));
		
		/* Get columns from the active form */
		$q = "SHOW COLUMNS FROM ".$db->nameQuote($db->replacePrefix('#__rwf_forms_'.$row->form_id))." WHERE  ".$db->nameQuote('Field')." = ".$db->Quote($oldfield);
		$db->setQuery($q);
		$db->query();
		$result = $db->loadResult();
		
		/* Check if the name has changed */
		if ($result && $row->field != $oldrow->field) {
			$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$row->form_id)."
				CHANGE `".$oldfield."` `".$field."` TEXT";
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', JText::_('Cannot rename fieldname').' '.$db->getErrorMsg());
		}
		else {
			/* Check if the field already exists */
			if (!$result) {
				/* Field doesn't exist, need to create it */
				$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$row->form_id). " ADD `".$field."` TEXT NULL";
				$db->setQuery($q);
				if (!$db->query()) JError::raiseWarning('error', $db->getErrorMsg());
			}
		}
		
		/* Check if the field moved form */
		if ($oldrow->form_id && $row->form_id <> $oldrow->form_id) {
			$result = array();
			/* Check if the column exists on the old table */
			$q = "SHOW COLUMNS FROM ".$db->nameQuote($db->replacePrefix('#__rwf_forms_'.$oldrow->form_id))." WHERE  ".$db->nameQuote('Field')." = ".$db->Quote($field);
			$db->setQuery($q);
			$db->query();
			$result = $db->loadResult();
			
			/* Check if the field already exists */
			if ($result) {
				/* Drop the old column */
				$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$oldrow->form_id)." DROP ".$db->nameQuote($field);
				$db->setQuery($q);
				if (!$db->query()) JError::raiseWarning('error', JText::_('Cannot remove field from old form').' '.$db->getErrorMsg());
			}
		}
		
		/* Get indexes from the active form */
		$indexresult = null;
		$q = "SHOW KEYS FROM ".$db->nameQuote($db->replacePrefix('#__rwf_forms_'.$row->form_id))." WHERE key_name = ".$db->Quote($field);
		$db->setQuery($q);
		$db->query();
		$indexresult = $db->loadAssocList('Key_name');
		
		/* Check if the field has to be unique */
		$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$row->form_id);
		if ($row->unique && !isset($indexresult[$field])) {
			$q .= " ADD UNIQUE (`".$field."` (255))";
			$db->setQuery($q);
			if (!$db->query()) {
				JError::raiseWarning('error', JText::_('Cannot make the field unique').' '.$db->getErrorMsg());
				/* Remove unique status */
				$q = "UPDATE ".$db->nameQuote('#__rwf_fields')."
					SET ".$db->nameQuote('unique')." = 0
					WHERE id = ".$row->id;
				$db->setQuery($q);
				$db->query();
			}
		}
		else if (isset($indexresult[$field])) {
			$q .= " DROP INDEX `".$field."`";
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', JText::_('Cannot remove the field unique status').' '.$db->getErrorMsg());
		}
	}
}
?>