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
    $this->_data->published = 1;
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

  	if (!$row->bind($post)) {
  		$this->setError(JText::_('There was a problem binding the field data'), 'error');
  		return false;
  	}
  	
  	if (empty($row->ordering)) $row->ordering = $row->getNextOrder('form_id = '.$row->form_id);

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
  	
  	$this->_id = $row->id;
  	 
  	/* Add form table */
  	$this->AddFieldTable($row, $oldrow);

  	/* mailing list handling in case of email field type */
  	if ($row->fieldtype == 'email') 
	  {		 
		  /* Load the table */
		  $mailinglistrow = $this->getTable('Mailinglists');
		  	
		  /* Fix up the mailinglist */
		  if (isset($post['listname'])) {
		  	$post['listnames'] = implode(';', $post['listname']);
		  }
		  else {
		  	$post['listnames'] = '';
		  }
		  $post['field_id'] = $row->id;
		  if (!$mailinglistrow->bind($post)) {
		  	$mainframe->enqueueMessage(JText::_('There was a problem binding the mailinglist data').' '.$row->getError(), 'error');
		  	return false;
		  }

		  /* Pass on the ID */
		  $mailinglistrow->field_id = $row->id;

		  /* save the changes */
		  if (!$mailinglistrow->store()) {
		  	$mainframe->enqueueMessage(JText::_('There was a problem storing the mailinglist data').' '.$row->getError(), 'error');
		  	return false;
		  }
		  	
	  }
		  
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
		
		/* column name for this field */
		$field = 'field_'. $row->id;
		
		/* Get columns from the active form */
		$q = "SHOW COLUMNS FROM ".$db->nameQuote($db->replacePrefix('#__rwf_forms_'.$row->form_id))." WHERE  ".$db->nameQuote('Field')." = ".$db->Quote($field);
		$db->setQuery($q);
		$db->query();
		$result = $db->loadResult();
		
		/* Check if the field already exists */
		if (!$result) {
			/* Field doesn't exist, need to create it */
			$q = ' ALTER TABLE '. $db->nameQuote('#__rwf_forms_'.$row->form_id) .' ADD '. $db->nameQuote($field) .' TEXT NULL';
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', $db->getErrorMsg());
		}
		
		/* Check if the field moved form */
		if ($oldrow->form_id && $row->form_id <> $oldrow->form_id) 
		{
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
		if ($row->unique && !isset($indexresult[$field])) 
		{
			$q .= ' ADD UNIQUE ('. $db->nameQuote($field) .' (255))';
			$db->setQuery($q);
			if (!$db->query()) 
			{
				JError::raiseWarning('error', JText::_('Cannot make the field unique').' '.$db->getErrorMsg());
				/* Remove unique status */
				$q = "UPDATE ".$db->nameQuote('#__rwf_fields')."
					SET ".$db->nameQuote('unique')." = 0
					WHERE id = ".$row->id;
				$db->setQuery($q);
				$db->query();
			}
		}
		else if (isset($indexresult[$field])) 
		{
			$q .= ' DROP INDEX' . $db->nameQuote($field);
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', JText::_('Cannot remove the field unique status').' '.$db->getErrorMsg());
		}
	}
	
	/**
	 * returns redmember fields as options
	 * @return aray
	 */
	public function getRedmemberFieldsOptions()
	{
		$query = ' SELECT f.field_dbname AS value, CONCAT(t.tab_name, " - ", f.field_name) AS text '
		       . ' FROM #__redmember_fields AS f '
		       . ' INNER JOIN #__redmember_tab AS t ON t.tab_id = f.field_tabid '
		       . ' ORDER BY t.tab_name, f.field_name '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}
	
	public function getValues()
	{
		$field = $this->getData();
		
		$query = ' SELECT v.* ' 
		       . ' FROM #__rwf_values AS v ' 
		       . ' WHERE v.field_id = ' . $this->_db->Quote($field->id)
		       . ' ORDER BY v.ordering '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}
	
	/**
	 * Get the mailingslists for the e-mail field
	 */
	function getActiveMailinglists() 
	{
		$res = array();
		JPluginHelper::importPlugin( 'redform_mailing' );
		$dispatcher =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'getIntegrationName', array( &$res ) );
		return $res;
	}

	/**
	 * Get the current mailingslist settings for this field
	 */
	function getMailinglist() 
	{
		/* Load the table */
		$mailinglistrow = $this->getTable('Mailinglists');
		$mailinglistrow->load($this->_id);
		return $mailinglistrow;
	}
}
?>