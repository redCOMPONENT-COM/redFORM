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
 * redFORM Model
 */
class RedformModelForm extends JModel 
{
  /**
   * Form id
   *
   * @var int
   */
  var $_id = null;

  /**
   * Form data array
   *
   * @var array
   */
  var $_data = null;

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
   * Method to load content event data
   *
   * @access  private
   * @return  boolean True on success
   * @since 0.9
   */
  function _loadData()
  {
    // Lets load the content if it doesn't already exist
    if (empty($this->_data))
    {
      $query = 'SELECT *'
          . ' FROM #__rwf_forms'
          . ' WHERE id = '.$this->_id
          ;
      $this->_db->setQuery($query);
      $this->_data = $this->_db->loadObject();
      
      if (!$this->_data) {
      	return false;
      }
    
			if (strtotime($this->_data->startdate) > time() || ($this->_data->formexpires && strtotime($this->_data->enddate) < time())) {
				$this->_data->formstarted = false;
			}
			else {				
				$this->_data->formstarted = true;
			}
      
      return (boolean) $this->_data;
    }
    return true;
  }
  
  function _initData()
  {
    $this->_data = & JTable::getInstance('redform', 'RedformTable');
		$this->_data->formstarted = false;
  	return $this->_data;
  }
  
  /**
   * Tests if the form is checked out
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
      $row = & JTable::getInstance('redform', 'RedformTable');
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
      $row = & JTable::getInstance('redform', 'RedformTable');
      return $row->checkin($this->_id);
    }
    return false;
  }
  
  /**
   * method to store data in db
   *
   * @param unknown_type $data
   * @return unknown
   */
  function store($post)
  {
  	$row = $this->getTable('redform', 'RedformTable');

  	/* Get the posted data */
  	if (!$row->bind($post)) {
  		$this->setError(JText::_('COM_REDFORM_There_was_a_problem_binding_the_form_data'), 'error');
  		return false;
  	}

  	/* Convert the dates to MySQL dates */
  	$row->startdate = $this->ConvertCalendarDate($row->startdate);
  	$row->enddate = $this->ConvertCalendarDate($row->enddate);

  	/* pre-save checks */
  	if (!$row->check()) {
  		$this->setError(JText::_('COM_REDFORM_There_was_a_problem_checking_the_form_data'), 'error');
  		return false;
  	}

  	/* save the changes */
  	if (!$row->store()) {
  		$this->setError(JText::_('COM_REDFORM_There_was_a_problem_storing_the_form_data'), 'error');
  		return false;
  	}

  	$row->checkin();

  	/* Add form table */
  	$this->AddFormTable($row->id);

  	return $row;
  }   
   
   /**
    * Delete a competition
    */
   function getRemoveForm() {
	$mainframe = JFactory::getApplication();
      $database = JFactory::getDBO();
      $cid = JRequest::getVar('cid');
      JArrayHelper::toInteger( $cid );
	  
      if (!is_array( $cid ) || count( $cid ) < 1) {
         $mainframe->enqueueMessage(JText::_('COM_REDFORM_No_form_found_to_delete'));
         return false;
      }
      if (count($cid)) {
         $cids = 'id=' . implode( ' OR id=', $cid );
         $query = "DELETE FROM #__rwf_forms"
         . "\n  WHERE ( $cids )";
         $database->setQuery( $query );
         if (!$database->query()) {
            $mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_form'));
         }
         else {
            if (count($cid) > 1) $mainframe->enqueueMessage(JText::_('COM_REDFORM_Forms_have_been_deleted'));
            else $mainframe->enqueueMessage(JText::_('COM_REDFORM_Form_has_been_deleted'));
			
			/* Get the field ids */
			$cids = 'form_id=' . implode( ' OR form_id=', $cid );
			$q = "SELECT id FROM #__rwf_fields
				WHERE ( $cids )";
			$database->setQuery($q);
			$fieldids = $database->loadResultArray();
			
			/* See if there is any data */
			
			if (count($fieldids) > 0) {
				/* Now delete the fields */
				$cids = 'form_id=' . implode( ' OR form_id=', $cid );
				$q = "DELETE FROM #__rwf_fields
					WHERE ( $cids )";
				$database->setQuery($q);
				if (!$database->query()) {
					$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_form_fields'));
				}
				else {
					$mainframe->enqueueMessage(JText::_('COM_REDFORM_Form_fields_have_been_deleted'));
					
					/* Delete the values */
					$cids = 'field_id=' . implode( ' OR field_id=', $fieldids );
					$q = "DELETE FROM #__rwf_values
						WHERE ( $cids )";
					$database->setQuery($q);
					if (!$database->query()) {
						$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_field_values'));
					}
					else {
						$mainframe->enqueueMessage(JText::_('COM_REDFORM_Field_values_have_been_deleted'));
					}
				}
			}
			else $mainframe->enqueueMessage(JText::_('COM_REDFORM_No_fields_found'));
			
			/* Delete the submitters */
			$cids = 'form_id=' . implode( ' OR form_id=', $cid );
			$q = "DELETE FROM #__rwf_submitters
				WHERE ( $cids )";
			$database->setQuery($q);
			if (!$database->query()) {
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_submitters'));
			}
			else {
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_Submitters_fields_have_been_deleted'));
			}
			
			/* Now delete the submitter values */
			$cids = 'form_id=' . implode( ' OR form_id=', $cid );
			foreach ($cid as $key => $fid) {
				$q = "DROP TABLE #__rwf_forms_".$fid;
				$database->setQuery($q);
				if (!$database->query()) {
					$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_submitters_answers'));
				}
				else {
					$mainframe->enqueueMessage(JText::_('COM_REDFORM_Submitters_answers_have_been_deleted'));
				}
			}
         }
      }
   }
   
   /**
    * Convert a calendar date to MySQL date format
	*/
	function ConvertCalendarDate(&$dtstamp) {		
		$date = JFactory::getDate($dtstamp);
		return $date->toMySQL();
	}
	 
	/**
	 * Get the number of contestants
	 */
	public function getCountSubmitters() {
		$db = JFactory::getDBO();
		$q = "SELECT form_id, COUNT(id) AS total
			FROM #__rwf_submitters
			GROUP BY form_id";
		$db->setQuery($q);
		return $db->loadObjectList('form_id');
	}
	
	/**
	 * Adds a table if it doesn't exist yet
	 */
	private function AddFormTable($formid) {
		$db = JFactory::getDBO();
		/* construct form name */
		$q = "SHOW TABLES LIKE ".$db->Quote($db->getPrefix().'rwf_forms_'.$formid);
		$db->setQuery($q);
		$result = $db->loadResultArray();
		if (count($result) == 0) {
			/* Table doesn't exist, need to create it */
			$q = "CREATE TABLE ".$db->nameQuote('#__rwf_forms_'.$formid)." (";
			$q .= $db->nameQuote('id')." INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ";
			$q .= ") COMMENT = ".$db->Quote('redFORMS Form '.$formid);
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', $db->getErrorMsg());
		}
	}
	
	/**
	  * Check if VirtueMart is installed
	  */
	public function getVmInstalled() {
		 $db = JFactory::getDBO();
		 $q = "SELECT extension_id FROM #__extensions WHERE name = ".$db->Quote('com_virtuemart');
		 $db->setQuery($q);
		 $result = $db->loadResult();
		 if ($result) return true;
		 else return false;
	}
	
	/**
	 * Get a list of VirtueMart products
	 */
	public function getVmProducts() {
		$db = JFactory::getDBO();
		$q = "SELECT product_id, CONCAT(product_name, ' :: ', product_sku) AS product_name
			FROM #__vm_product
			ORDER BY product_name";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	

  /**
   * Clones the forms and their fields
   *
   * @param int array of form ids
   */
  function copy($cids = array())
  {
    foreach ($cids as $cid)
    {
      // get the form
    	$form = & $this->getTable('redform', 'RedformTable');
      $form->load($cid);
      // get associated fields
	    $fields = $form->getFormFields();
      
      // copy the form
	    $form->id = null;
      $form->formname =  JText::_('COM_REDFORM_Copy_of') .' '. $form->formname;
      $form->store();      
      
	    /* Add form table */
	    $this->AddFormTable($form->id);
                      
	    // now copy the fields
	    foreach ($fields as $field_id)
	    {
	    	// get field
	    	$field = & $this->getTable('fields', 'RedformTable');
	      $field->load($field_id);
	      
	      // get associated values
	      $values = $field->getValues();
	      
	      // copy the form
	      $field->id = null;
        $field->form_id = $form->id;
        
        $fieldmodel = & JModel::getInstance('field', 'RedformModel');
        $newfield = $fieldmodel->store($field->getProperties());
                
        // copy associated values
        foreach ($values as $v)
        {
        	// get value
        	$value = & $this->getTable('values', 'RedformTable');
        	$value->load($v);
        	
        	$value->id = null;
        	$value->field_id = $newfield->id;
	        $valuemodel = & JModel::getInstance('value', 'RedformModel');
	        $data = $value->getProperties();
	        $data['form_id'] = $form->id;
	        unset($data['ordering']);
	        $newvalue = $valuemodel->store($data);        	
        }
	    }
    }
    return true;
  }
  
  /**
   * returns form fields as options
   * @return array
   */
  public function getFieldsOptions()
  {
  	$query = ' SELECT f.id AS value, f.field AS text ' 
  	       . ' FROM #__rwf_fields AS f ' 
  	       . ' WHERE f.form_id = '.$this->_id
  	       . ' ORDER BY f.field ';
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();
  	return $res;
  }
}
?>
