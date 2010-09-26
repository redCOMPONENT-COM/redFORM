<?php
/**
 * @version 1.0 $Id: archive.php 217 2009-06-06 20:04:26Z julien $
 * @package Joomla
 * @subpackage redform
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
 * Joomla redFORM Component Model
 *
 * @author Julien Vonthron <julien@redweb.dk>
 * @package   redform
 * @since 2.0
 */
class RedformModelPayment extends JModel
{
  /**
   * item id
   *
   * @var int
   */
  var $_id = null;

  /**
   * Project data
   *
   * @var array
   */
  var $_data = null;

  /**
   * Constructor
   *
   * @since 0.1
   */
  function __construct()
  {
    parent::__construct();

    $array = JRequest::getVar('cid', array(0), '', 'array');
    $this->setId((int)$array[0]);
  }

  /**
   * Method to set the item identifier
   *
   * @access  public
   * @param int item identifier
   */
  function setId($id)
  {
    // Set item id and wipe data
    $this->_id    = $id;
    $this->_data  = null;
  }
  


  /**
   * Method to get an item
   *
   * @since 0.1
   */
  function &getData()
  {
    // Load the item data
    if (!$this->_loadData()) $this->_initData();

    return $this->_data;
  }
  
	/**
	 * Method to remove an item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__rwf_payment'
				. ' WHERE id IN ( '.$cids.' )';
			
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to load content data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT *'.
					' FROM #__rwf_payment ' .
          ' WHERE id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the competition data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$row = & $this->getTable('Payments', 'RedFormTable');
			if (!$row->date) {
				$row->date = strftime('%Y-%m-%d %H:%M:%S');
			}
			$this->_data					= $row;
			return (boolean) $this->_data;
		}
		return true;
	}
	
  /**
   * Method to store the item
   *
   * @access  public
   * @return  false|int id on success
   * @since 1.5
   */
  function store($data)
  {
  	$array = JRequest::getVar('cid', array(0), '', 'array');
  	$cid = intval($array[0]);
  	
		$row = & $this->getTable('Payments', 'RedFormTable');
		
    // Bind the form fields to the items table
    if (!$row->bind($data)) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    $row->id = $cid;
    
    // Make sure the item is valid
    if (!$row->check()) {
      $this->setError($row->getError());
      return false;
    }

    // Store the item to the database
    if (!$row->store()) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }
    
    return $row;
  }
}
?>
