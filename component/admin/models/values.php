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
class RedformModelValues extends JModel 
{

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
	 * return values
	 *
	 * @return array
	 */
	function getValues() 
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
    /* Get all the orders based on the limits */
    $q = ' SELECT v.*, fd.field, fd.fieldtype, CONCAT(f.formname, " :: ", fd.field) AS fieldname '
       . ' FROM #__rwf_values AS v '
       . ' INNER JOIN #__rwf_fields AS fd ON fd.id = v.field_id '
       . ' INNER JOIN #__rwf_forms AS f ON f.id = fd.form_id '
       ;
    $form_id = JRequest::getInt('form_id', false);
    if ($form_id) $q .= ' WHERE f.id = '. $this->_db->Quote($form_id);
    $q .= ' ORDER BY f.id, fd.ordering, v.ordering ';
    return $q;
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
   * returns options for forms selecto list
   *
   * @return array
   */
  function getFormsOptions()
  {
    $query = "SELECT id AS value, formname AS text FROM #__rwf_forms";
    $this->_db->setQuery($query);
    return $this->_db->loadObjectList();
  }  

  /**
   * returns options for forms selecto list
   *
   * @return array
   */
  function getTotalFields()
  {
    $query = "SELECT COUNT(*) FROM #__rwf_fields";
    $this->_db->setQuery($query);
    return $this->_db->loadResult();
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

    $table = & $this->getTable('Values', 'RedformTable');
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
	$mainframe = JFactory::getApplication();
  	$database = & JFactory::getDBO();
  	JArrayHelper::toInteger( $cid );
  	 
  	$cids = 'id=' . implode( ' OR id=', $cid );
  	$query = "DELETE FROM #__rwf_values"
  	. "\n  WHERE ( $cids )";
  	$database->setQuery( $query );
  	if (!$database->query()) {
  		$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_value'));
  		return false;
  	}
  	
  	if (count($cid) > 1) $mainframe->enqueueMessage(JText::_('COM_REDFORM_Values_have_been_deleted'));
  	else $mainframe->enqueueMessage(JText::_('COM_REDFORM_Value_has_been_deleted'));
  		
  	return true;
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
			return JError::raiseWarning( 500, JText::_('COM_REDFORM_No_items_selected' ) );
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
    * Reorder values
	*/
	function saveorder() 
	{
		$db =& JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		$order = JRequest::getVar('order');
		$total = count($cid);
		$row =& $this->getTable();
		
		if (empty( $cid )) {
			return JError::raiseWarning( 500, JText::_('COM_REDFORM_No_items_selected' ) );
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
}
?>