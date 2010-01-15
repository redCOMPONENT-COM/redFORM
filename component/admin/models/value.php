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
    $this->_data = & JTable::getInstance('Values', 'Table');
    $this->_data->published = 1;
    $this->_data->fieldtype = '';
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
      $row = & $this->getTable('Values', 'Table');
      return $row->checkin($this->_id);
    }
    return false;
  }
  
   /**
    * Save an value
    */
   function store($data) 
   {
      global $mainframe;
      $row = $this->getTable('Values', 'Table');
	  
	  /* Get the posted data */
	  $post = $data;
	  
	  $row->load($post['id']);
	  if (empty($row->ordering)) $post['ordering'] = $row->getNextOrder((int)$post['form_id']);
	  
	  /* Get the posted data */
      if (!$row->bind($post)) {
         $mainframe->enqueueMessage(JText::_('There was a problem binding the value data').' '.$row->getError(), 'error');
         return false;
      }
	  
      /* pre-save checks */
      if (!$row->check()) {
         $mainframe->enqueueMessage(JText::_('There was a problem checking the value data').' '.$row->getError(), 'error');
         return false;
      }

      /* save the changes */
      if (!$row->store()) {
         $mainframe->enqueueMessage(JText::_('There was a problem storing the value data').' '.$row->getError(), 'error');
         return false;
      }
	  
      $row->reorder();
	  
      // TODO: move to fields ??
      // special treatment if value belongs to an 'email' field
		  /* Store the mailinglists */
      $query = ' SELECT fieldtype FROM #__rwf_fields WHERE id = '. $row->field_id;
      $this->_db->setQuery($query);
      $type = $this->_db->loadResult();      
		  if ($type == 'email') 
		  {
			 
			  /* Load the table */
			  $mailinglistrow = $this->getTable('Mailinglists');
			 
			 /* Fix up the mailinglist */
			 if (isset($post['listname'])) $post['listnames'] = implode(';', $post['listname']);
			 else $post['listnames'] = '';
			 
			 if (!$mailinglistrow->bind($post)) {
				 $mainframe->enqueueMessage(JText::_('There was a problem binding the mailinglist data').' '.$row->getError(), 'error');
				 return false;
			  }
			  
			  /* Pass on the ID */
			 $mailinglistrow->id = $row->id;
			  
			  /* save the changes */
			  if (!$mailinglistrow->store()) {
				 $mainframe->enqueueMessage(JText::_('There was a problem storing the mailinglist data').' '.$row->getError(), 'error');
				 return false;
			  }
			 
		  }
      $mainframe->enqueueMessage(JText::_('The value has been saved'));
      return $row;
   }
   
   /**
    * Delete an value
    */
   function getRemoveValue() {
      global $mainframe;
      $database = JFactory::getDBO();
      $cid = JRequest::getVar('cid');
      JArrayHelper::toInteger( $cid );
	  
      if (!is_array( $cid ) || count( $cid ) < 1) {
         $mainframe->enqueueMessage(JText::_('No value found to delete'));
         return false;
      }
      if (count($cid)) {
         $cids = 'id=' . implode( ' OR id=', $cid );
         $query = "DELETE FROM #__rwf_values"
         . "\n  WHERE ( $cids )";
         $database->setQuery( $query );
         if (!$database->query()) {
            $mainframe->enqueueMessage(JText::_('A problem occured when deleting the value'));
         }
         else {
			 $query = "DELETE FROM #__rwf_mailinglists"
			 . "\n  WHERE ( $cids )";
			 $database->setQuery( $query );
			 $database->query();
            if (count($cid) > 1) $mainframe->enqueueMessage(JText::_('Values have been deleted'));
            else $mainframe->enqueueMessage(JText::_('Value has been deleted'));
         }
      }
   }
   
   /**
    * Reorder values
	*/
	function getSaveOrder() {
		$db =& JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		$order = JRequest::getVar('order');
		$total = count($cid);
		$row =& $this->getTable();
		
		if (empty( $cid )) {
			return JError::raiseWarning( 500, JText::_( 'No items selected' ) );
		}
		// update ordering values
		for ($i = 0; $i < $total; $i++) {
			$row->load( (int) $cid[$i] );
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					return JError::raiseError( 500, $db->getErrorMsg() );
				}
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
	 * Get the mailingslists for the e-mail field
	 */
	function getMailinglists() {
		/* Load the table */
		$mailinglistrow = $this->getTable('Mailinglists');
		$cid = JRequest::getVar('cid', false);
		if (!$cid) {
			$post = JRequest::get('post');
			$id = $post['id'];
		}
		else $id = $cid[0];
		$mailinglistrow->load($id);
		return $mailinglistrow;
	}
	
	/**
	 * Get the mailingslists for the e-mail field
	 */
	function getUseMailinglists() 
	{
		$db = JFactory::getDBO();
		$q = "SELECT name
			FROM #__rwf_configuration
			WHERE name IN ('use_phplist', 'use_ccnewsletter', 'use_acajoom')
			AND value = 1";
		$db->setQuery($q);
		return $db->loadResultArray();
	}
}
?>