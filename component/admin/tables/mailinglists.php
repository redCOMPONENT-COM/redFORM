<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 *
 */

/* No direct access */
defined('_JEXEC') or die('Restricted access');

/**
 */
class TableMailinglists extends JTable {
	/** @var int Primary key */
	var $id = null;
	/** @var string The value for the field */
	var $mailinglist = null;
	/** @var string Set to true if the value is published */
	var $listnames = null;
	
	/**
	* @param database A database connector object
	*/
	function __construct( &$db ) {
		parent::__construct('#__rwf_mailinglists', 'id', $db );
	}
	
	public function store() {
		$k = $this->check();
		
		if( $k)
		{
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key );
		}
		else
		{
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret )
		{
			$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Check if there is an entry for this email field
	 */
	public function check() {
		$db = JFactory::getDBO();
		
		$q = "SELECT COUNT(*) AS total FROM ".$this->_tbl." WHERE id = ".$this->id;
		$db->setQuery($q);
		$result = $db->loadResult();
		if ($result > 0) return true;
		else return false;
		
	}
}
?>