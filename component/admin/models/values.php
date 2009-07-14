<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
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
    $q = "SELECT a.*, q.field, CONCAT(c.formname, ' :: ', q.field) AS fieldname
        FROM #__rwf_values a, #__rwf_fields q, #__rwf_forms c
        WHERE a.field_id = q.id
        AND q.form_id = c.id ";
    $form_id = JRequest::getInt('form_id', false);
    if ($form_id) $q .= "AND c.id = ".$form_id." ";
    $q .= "ORDER BY c.formname, ordering";
    return $q;
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

    $table = & $this->getTable('Values');
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
  	global $mainframe;
  	$database = & JFactory::getDBO();
  	JArrayHelper::toInteger( $cid );
  	 
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
	function getCheckFieldType() {
		$db = JFactory::getDBO();
		$qid = JRequest::getVar('field_id');
		
		$q = "SELECT fieldtype
			FROM #__rwf_values
			WHERE field_id = ".$qid."
			GROUP BY fieldtype
			LIMIT 1";
		$db->setQuery($q);
		
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
	function getUseMailinglists() {
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