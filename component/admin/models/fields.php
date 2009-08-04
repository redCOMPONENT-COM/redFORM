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
		$form_id = JRequest::getInt('form_id', false);
      
		/* Get all the fields based on the limits */
		$query = ' SELECT q.*, c.formname, CONCAT(c.formname, " :: ", q.field) AS fieldname '
		       . ' FROM #__rwf_fields q, #__rwf_forms c '
		       . ' WHERE q.form_id = c.id '
		       ;
		if ($form_id && $form_id > 0) {
			$query .= ' AND c.id = '.$form_id;
		}
		$query .= ' ORDER BY c.id, q.ordering ';
		return $query;
	}
	
	function getFormsOptions()
	{
		$query = "SELECT id AS value, formname AS text FROM #__rwf_forms";
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	function getPagination() {
		global $mainframe, $option;
		
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

    $table = & $this->getTable('Fields');
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
				JError::raiseWarning('error', JText::_('Cannot remove field from old form').' '.$db->getErrorMsg());
			}
		}
			
		/* Delete the fields */
		$query = "DELETE FROM #__rwf_fields"
		       . "\n  WHERE ( $cids )";
		$db->setQuery( $query );
		if (!$db->query()) {
			$this->setError(JText::_('A problem occured when deleting the field'));
			return false;
		}
		else {
			/* Delete the values */
			$cids = 'field_id=' . implode( ' OR id=', $cid );
			$q = "DELETE FROM #__rwf_values
			WHERE ( $cids )";
			$db->setQuery($q);
			if (!$db->query()) {
				$this->setError(JText::_('A problem occured when deleting the field values'));
			}
			else {
				$mainframe->enqueueMessage(JText::_('Field values have been deleted'));
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
		$row = $this->getTable();
		
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
}
?>