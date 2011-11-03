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
class RedformModelFields extends JModel {

	/**
	 * the data
	 *
	 * @var array
	 */
	protected $_data = null;
	
	/** @var integer Total entries */
	protected $_total = null;
	
	/** @var integer pagination limit starter */
	protected $_limitstart = null;
	
	/** @var integer pagination limit */
	protected $_limit = null;
	   
  /**
   * field id
   *
   * @var int
   */
  protected $_id = null;
  
  /**
   * Constructor
   *
   * @since 0.9
   */
  function __construct()
  {
    parent::__construct();

	$mainframe = JFactory::getApplication();
	$option = JRequest::getVar('option');

    $limit      = $mainframe->getUserStateFromRequest( $option.'.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
    $limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
    $form_id    = $mainframe->getUserStateFromRequest( $option.'.fields.form_id', 'form_id', 0, 'int');

    $this->setState('limit', $limit);
    $this->setState('limitstart', $limitstart);
    $this->setState('form_id',   $form_id);

    $array = JRequest::getVar('cid',  0, '', 'array');
    $this->setId((int)$array[0]);
  }
  

  /**
   * Method to set the category identifier
   *
   * @access  public
   * @param int Category identifier
   */
  function setId($id)
  {
    // Set id and wipe data
    $this->_id   = $id;
    $this->_data = null;
  }
  
	/**
	 * Show all fields that can be used for a value
	 */
	function getFields() 
	{
		if (empty($this->_data))
		{
			$db = & JFactory::getDBO();

			// first, call the pagination to set the limits
			$this->getPagination();
			
			$db->setQuery($this->_buildQuery(), $this->_limitstart, $this->_limit);
			$this->_data = $db->loadObjectList();
		}
		return $this->_data;
	}
	
	function _buildQuery()
	{
    $form_id = $this->getState('form_id');
		$order   = $this->_buildContentOrderBy();
      
		/* Get all the fields based on the limits */
		$query = ' SELECT fd.*, f.formname, CONCAT(f.formname, " :: ", fd.field) AS fieldname '
		       . ' FROM #__rwf_fields AS fd '
		       . ' LEFT JOIN #__rwf_forms AS f ON f.id = fd.form_id '
		       ;
		if ($form_id && $form_id > 0) {
			$query .= ' WHERE f.id = '.$form_id;
		}
		$query .= $order;
		return $query;
	}

	/**
	 * Method to build the orderby clause of the query for the categories
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildContentOrderBy()
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar('option');

		$filter_order		  = $mainframe->getUserStateFromRequest( $option.'.values.filter_order',     'filter_order', 	'ordering', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.values.filter_order_Dir', 'filter_order_Dir',	'', 'word' );

		switch ($filter_order) 
		{
			case 'field':
				$orderby 	= ' ORDER BY f.formname, fd.field '.$filter_order_Dir.', fd.ordering';
				break;
				
			case 'fieldtype':
				$orderby 	= ' ORDER BY f.formname, fd.fieldtype '.$filter_order_Dir.', fd.ordering';
				break;
				
			case 'id':
				$orderby 	= ' ORDER BY f.formname, fd.id '.$filter_order_Dir;
				break;
				
			default:				
			case 'ordering':
				$orderby 	= ' ORDER BY f.formname, fd.ordering '.$filter_order_Dir;
				break;
		}

		return $orderby;
	}
	
	function getFormsOptions()
	{
		$query = "SELECT id AS value, formname AS text FROM #__rwf_forms";
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	function getPagination() {
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar('option');
		
		/* Lets load the pagination if it doesn't already exist */
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
			$this->_limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
			$this->_pagination = new JPagination( $this->getTotal(), $this->_limitstart, $this->_limit );
		}
		
		return $this->_pagination;
	}
	
	/**
	 * Method to get the total number of items return by the query
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal() 
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$this->_total = $this->_getListCount($this->_buildQuery());
		}

		return $this->_total;
	}
	 
 /**
   * Method to (un)publish
   *
   * @access  public
   * @return  boolean True on success
   * @since 0.9
   */
  function publish($cid = array(), $publish = 1, $user_id = 0)
  {
    $user   =& JFactory::getUser();

    $table = & $this->getTable('Fields', 'RedformTable');
    if (!$table->publish($cid, $publish)) {
      $this->setError($table->getError());
      return false;
    }
    
    return true;
  }
	  
   /**
    * Delete items
    */
	function delete($cid) 
	{
		$mainframe = & JFactory::getApplication();
		$db = JFactory::getDBO();
		JArrayHelper::toInteger( $cid );

		$cids = 'id=' . implode( ' OR id=', $cid );
		/* Check each field the form it belongs to and delete the column */
		$q = "SELECT id, field, form_id
					FROM #__rwf_fields
					WHERE ( $cids )";
		$db->setQuery($q);
		$fields = $db->loadObjectList();
			
		foreach ($fields as $key => $field) 
		{
			$tablefield = 'field_'. $field->id;
			$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$field->form_id)." DROP ".$db->nameQuote($tablefield);
			$db->setQuery($q);
			if (!$db->query()) {
				JError::raiseWarning('error', JText::_('COM_REDFORM_Cannot_remove_field_from_old_form').' '.$db->getErrorMsg());
			}
		}
			
		/* Delete the fields */
		$query = "DELETE FROM #__rwf_fields"
		       . "\n  WHERE ( $cids )";
		$db->setQuery( $query );
		if (!$db->query()) {
			$this->setError(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_field'));
			return false;
		}
		else {
			/* Delete the values */
			$cids = 'field_id=' . implode( ' OR id=', $cid );
			$q = "DELETE FROM #__rwf_values
			WHERE ( $cids )";
			$db->setQuery($q);
			if (!$db->query()) {
				$this->setError(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_field_values'));
			}
			else {
				$mainframe->enqueueMessage(JText::_('COM_REDFORM_Field_values_have_been_deleted'));
			}
		}
		return true;
	}
   
   /**
    * Reorder fields
	*/
	function saveorder() 
	{
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		$order = JRequest::getVar('order');
		$total = count($cid);
		$row = $this->getTable('fields', 'redformtable');
		
		if (empty( $cid )) {
			return JError::raiseWarning( 500, JText::_('COM_REDFORM_No_items_selected' ) );
		}
		// update ordering values
		for ($i = 0; $i < $total; $i++) {
			$row->load( (int) $cid[$i] );
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					JError::raiseError( 500, $db->getErrorMsg() );
					return false;
				}
			}
		}
		return true;
	}
			
	/**
	 * Function to clean up the database of unused fields
	 */
	public function sanitize() 
	{
		$db = JFactory::getDBO();
		/* Get the form IDs */
		$q = "SELECT id FROM #__rwf_forms";
		$db->setQuery($q);
		$forms = $db->loadResultArray();
		
		/* Go through all the forms */
		foreach ($forms as $key => $form_id) 
		{
			/* Load the list of fields used for this form */
			$fields = array();
			$q = "SELECT CONCAT('field_', id) AS field
				FROM #__rwf_fields
				WHERE form_id = ".$form_id;
			$db->setQuery($q);
			$fields = $db->loadResultArray();
			/* ID field is required */
			$fields[] = 'id';
			
			/* Load the columns that are created for this form */
			$columns = array();
			$q = "SHOW COLUMNS FROM ".$db->nameQuote('#__rwf_forms_'.$form_id);
			$db->setQuery($q);
			$db->query();
			$columns = $db->loadResultArray();
			/* Get the columns to be deleted */
			$del_columns = array_diff($columns, $fields);
			
			/* Remove any columns that are no longer fields */
			foreach ($del_columns as $ckey => $column) {
				$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$form_id)." DROP `".$column."`";
				$db->setQuery($q);
				$db->query();
			}
		}
		return true;
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
    $row =& JTable::getInstance('Fields', 'RedformTable');

    if (!$row->load( $this->_id ) ) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    if (!$row->move( $direction )) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    return true;
  }
}
?>