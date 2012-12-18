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
 */
class RedformModelValue extends JModel 
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
  
  function getFieldsOptions()
  {
    $query = ' SELECT fd.id AS value, CONCAT(f.formname, "::", fd.field) AS text '
           . ' FROM #__rwf_fields AS fd '
           . ' INNER JOIN #__rwf_forms AS f ON f.id = fd.form_id '
           . ' ORDER BY f.formname, fd.field, fd.ordering '
           ;
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
        $query = 'SELECT v.*, f.fieldtype '
            . ' FROM #__rwf_values AS v '
            . ' INNER JOIN #__rwf_fields AS f ON f.id = v.field_id '
            . ' WHERE v.id = '.$this->_id
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
    $this->_data = & JTable::getInstance('Values', 'RedformTable');
    $this->_data->published = 1;
    
    if ($field_id = JRequest::getInt('fieldid')) 
    {
    	$query = ' SELECT fieldtype ' 
    	       . ' FROM #__rwf_fields ' 
    	       . ' WHERE id = ' . $this->_db->Quote($field_id);
    	$this->_db->setQuery($query);
    	$res = $this->_db->loadResult();
    	$this->_data->fieldtype = $res;
    }
    else
    {
    	$this->_data->fieldtype = '';
    }
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
      $row = & $this->getTable('Fields', 'RedformTable');
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
      $row = & $this->getTable('Values', 'RedformTable');
      return $row->checkin($this->_id);
    }
    return false;
  }
  
   /**
    * Save an value
    */
   function store($data) 
   {
      $mainframe = JFactory::getApplication();
      $row = $this->getTable('Values', 'RedformTable');
	  
	  /* Get the posted data */
	  $post = $data;
	  
	  $row->load($post['id']);
	  
	  /* Get the posted data */
      if (!$row->bind($post)) {
         $mainframe->enqueueMessage(JText::_('COM_REDFORM_There_was_a_problem_binding_the_value_data').' '.$row->getError(), 'error');
         return false;
      }
      
		  if (empty($row->ordering)) {
		  	$row->ordering = $row->getNextOrder('field_id = '.$row->field_id);
		  }
	  
      /* pre-save checks */
      if (!$row->check()) {
         $mainframe->enqueueMessage(JText::_('COM_REDFORM_There_was_a_problem_checking_the_value_data').' '.$row->getError(), 'error');
         return false;
      }

      /* save the changes */
      if (!$row->store()) {
         $mainframe->enqueueMessage(JText::_('COM_REDFORM_There_was_a_problem_storing_the_value_data').' '.$row->getError(), 'error');
         return false;
      }
	  
      $row->reorder('field_id = '.$row->field_id);
	  
      $mainframe->enqueueMessage(JText::_('COM_REDFORM_The_value_has_been_saved'));
      return $row;
   }
   
   /**
    * Delete an value
    */
   function getRemoveValue() 
   {
      $mainframe = JFactory::getApplication();
      $database = JFactory::getDBO();
      $cid = JRequest::getVar('cid');
      JArrayHelper::toInteger( $cid );
	  
      if (!is_array( $cid ) || count( $cid ) < 1) {
         $mainframe->enqueueMessage(JText::_('COM_REDFORM_No_value_found_to_delete'));
         return false;
      }
      if (count($cid)) 
      {
         $cids = 'id=' . implode( ' OR id=', $cid );
         $query = "DELETE FROM #__rwf_values"
         . "\n  WHERE ( $cids )";
         $database->setQuery( $query );
         if (!$database->query()) {
            $mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_value'));
         }
         else {
            if (count($cid) > 1) $mainframe->enqueueMessage(JText::_('COM_REDFORM_Values_have_been_deleted'));
            else $mainframe->enqueueMessage(JText::_('COM_REDFORM_Value_has_been_deleted'));
         }
      }
   }
	
	/**
	 * Check if a field type is already existing
	 */
	function getCheckFieldType() 
	{
		$db = JFactory::getDBO();
		$qid = JRequest::getInt('field_id');
		
		$q = ' SELECT fieldtype	FROM #__rwf_fields WHERE id = '.$db->Quote($qid);
		$db->setQuery($q, 0, 1);
		
		return $db->loadResult();
	}
	
  /**
   * Method to move
   *
   * @access  public
   * @return  boolean True on success
   * @since 0.9
   */
  function move($direction)
  {
    $row =& JTable::getInstance('Values', 'RedformTable');

    if (!$row->load( $this->_id ) ) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    if (!$row->move( $direction, 'field_id = '.$row->field_id )) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    return true;
  }
}
