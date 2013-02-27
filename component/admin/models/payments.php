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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Joomla redform Component Model
 *
 * @package		redform
 * @since 2.0
 */
class RedformModelPayments extends JModelLegacy 
{
	/**
	 * key for which we want to display payments
	 * 
	 * @var unknown_type
	 */
	var $_key = null;
   /**
   * list data array
   *
   * @var array
   */
  var $_data = null;

  /**
   * total
   *
   * @var integer
   */
  var $_total = null;

  /**
   * Pagination object
   *
   * @var object
   */
  var $_pagination = null;
  
  /**
   * Constructor
   *
   * @since 0.1
   */
  function __construct()
  {
    parent::__construct();
    $mainframe = JFactory::getApplication();
    $option = JRequest::getVar('option');

    // Get the pagination request variables
    $limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
    $limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

    $this->setState('limit', $limit);
    $this->setState('limitstart', $limitstart);
    
    $key = JRequest::getVar('submit_key', null, 'request', 'string');
    if ($key) {
    	$this->setKey($key);
    }
  }
  
  function setKey($key)
  {
  	$this->_key = $key;
  	$this->_data = null;
  }
  
  /**
   * Method to get List data
   *
   * @access public
   * @return array
   */
  function getData()
  {
    // Lets load the content if it doesn't already exist
    if (empty($this->_data))
    {
      $query = $this->_buildQuery();
      if (!$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit')))
      echo $this->_db->getErrorMsg();
    }
    
    return $this->_data;
  }
  
  function getFormId()
  {
  	$query = ' SELECT form_id ' 
  	       . ' FROM #__rwf_submitters ' 
  	       . ' WHERE submit_key = ' . $this->_db->Quote($this->_key);
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadResult();
  	return $res;
  }
  
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT obj.* '
			. ' FROM #__rwf_payment AS obj '
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar('option');

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.payments.filter_order',		'filter_order',		'obj.date',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.payments.filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		if ($filter_order == 'obj.date'){
			$orderby 	= ' ORDER BY obj.date DESC'.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , obj.date DESC';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar('option');

		$where = array();
		if ($this->_key) {
			$where[] = ' submit_key = '. $this->_db->Quote($this->_key);
		}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}
	
  /**
   * Method to get a pagination object
   *
   * @access public
   * @return integer
   */
  function getPagination()
  {
    // Lets load the content if it doesn't already exist
    if (empty($this->_pagination))
    {
      jimport('joomla.html.pagination');
      $this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
    }

    return $this->_pagination;
  }
  

  /**
   * Total nr of items
   *
   * @access public
   * @return integer
   */
  function getTotal()
  {
    // Lets load the total nr if it doesn't already exist
    if (empty($this->_total))
    {
      $query = $this->_buildQuery();
      $this->_total = $this->_getListCount($query);
    }

    return $this->_total;
  }
}
?>
