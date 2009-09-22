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
class RedformModelForms extends JModel 
{
	/** @var integer Total entries */
	protected $_total = null;
	
	/** @var integer pagination limit starter */
	protected $_limitstart = null;
	
	/** @var integer pagination limit */
	protected $_limit = null;
	   
	/**
	 * Show all orders for which an invitation to fill in
	 * a testimonal has been sent
	 */
	function getForms() {
		/* Get all the orders based on the limits */
		$query = "SELECT * 
				FROM #__rwf_forms";
		return $this->_getList($query, $this->_limitstart, $this->_limit);
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
	 * Method to get the total number of testimonial items for the category
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal() {
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = "SELECT *"
			. "\n FROM #__rwf_forms";
			$this->_total = $this->_getListCount($query);
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
  function publish($cid = array(), $publish = 1)
  {
    $user   =& JFactory::getUser();

    $table = & $this->getTable('redform');
    if (!$table->publish($cid, $publish)) {
    	$this->setError($table->getError());
    	return false;
    }
    
    return true;
  }
          
  /**
   * Delete forms
   */
  function delete($cid)
  {
  	global $mainframe;
  	$database = JFactory::getDBO();
  	JArrayHelper::toInteger( $cid );
  	 
  	$cids = 'id=' . implode( ' OR id=', $cid );
  	$query = "DELETE FROM #__rwf_forms"
  	. "\n  WHERE ( $cids )";
  	$database->setQuery( $query );
  	if (!$database->query()) {
  		$mainframe->enqueueMessage(JText::_('A problem occured when deleting the form'));
  	}
  	else {
  		if (count($cid) > 1) $mainframe->enqueueMessage(JText::_('Forms have been deleted'));
  		else $mainframe->enqueueMessage(JText::_('Form has been deleted'));

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
  				$mainframe->enqueueMessage(JText::_('A problem occured when deleting the form fields'));
  			}
  			else {
  				$mainframe->enqueueMessage(JText::_('Form fields have been deleted'));

  				/* Delete the values */
  				$cids = 'field_id=' . implode( ' OR field_id=', $fieldids );
  				$q = "DELETE FROM #__rwf_values
  				WHERE ( $cids )";
  				$database->setQuery($q);
  				if (!$database->query()) {
  					$mainframe->enqueueMessage(JText::_('A problem occured when deleting the field values'));
  				}
  				else {
  					$mainframe->enqueueMessage(JText::_('Field values have been deleted'));
  				}
  			}
  		}
  		else $mainframe->enqueueMessage(JText::_('No fields found'));

  		/* Delete the submitters */
  		$cids = 'form_id=' . implode( ' OR form_id=', $cid );
  		$q = "DELETE FROM #__rwf_submitters
  		WHERE ( $cids )";
  		$database->setQuery($q);
  		if (!$database->query()) {
  			$mainframe->enqueueMessage(JText::_('A problem occured when deleting the submitters'));
  		}
  		else {
  			$mainframe->enqueueMessage(JText::_('Submitters fields have been deleted'));
  		}

  		/* Now delete the submitter values */
  		$cids = 'form_id=' . implode( ' OR form_id=', $cid );
  		foreach ($cid as $key => $fid) {
  			$q = "DROP TABLE #__rwf_forms_".$fid;
  			$database->setQuery($q);
  			if (!$database->query()) {
  				$mainframe->enqueueMessage(JText::_('A problem occured when deleting the submitters answers'));
  			}
  			else {
  				$mainframe->enqueueMessage(JText::_('Submitters answers have been deleted'));
  			}
  		}
  	}
  }
   
   /**
    * Convert a calendar date to MySQL date format
	*/
	function ConvertCalendarDate(&$dtstamp) {
		/* Conver the date to MySQL format */
		$datetime = split(" ", $dtstamp);
		$dates = split("-", $datetime[0]);
		$times = split(":", $datetime[2]);
		
		$date = JFactory::getDate(strtotime($dates[2].'-'.$dates[1].'-'.$dates[0].' '.$times[0].':'.$times[1].':'.$times[2]));
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
	private function AddFormTable($formid) 
	{
		$db = JFactory::getDBO();
		/* construct form name */
		$q = "SHOW TABLES LIKE ".$db->Quote($db->replacePrefix('#__rwf_forms_'.$formid));
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
	public function getVmInstalled() 
	{
		 $db = JFactory::getDBO();
		 $q = "SELECT COUNT(*) FROM #__components WHERE link = ".$db->Quote('option=com_virtuemart');
		 $db->setQuery($q);
		 $result = $db->loadResult();
		 if ($result > 0) return true;
		 else return false;
	}
	
	/**
	 * Get a list of VirtueMart products
	 */
	public function getVmProducts() 
	{
		$db = JFactory::getDBO();
		$q = "SELECT product_id, CONCAT(product_name, ' :: ', product_sku) AS product_name
			FROM #__vm_product
			ORDER BY product_name";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
}
?>
