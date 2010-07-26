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
class RedformModelRedform extends JModel {
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
    * Publish or Unpublish testimonials
    */
   function getPublish() {
      global $mainframe;

      $cids = JRequest::getVar('cid');
      $task = JRequest::getCmd('task');
      $state = ($task == 'publish') ? 1 : 0;
      $user = &JFactory::getUser();
      $row = $this->getTable();
	  
      if ($row->Publish($cids, $state, $user->id)) {
         if ($state == 1) $mainframe->enqueueMessage(JText::_('Forms have been published'));
         else $mainframe->enqueueMessage(JText::_('Forms have been unpublished'));
      }
      else {
         if ($state == 1) $mainframe->enqueueMessage(JText::_('Forms could not be published'));
         else $mainframe->enqueueMessage(JText::_('Forms could not be unpublished'));
      }
   }
   
   /**
    * get the form record
    */
   function getForm() 
   {
      $row = $this->getTable();
      $my = JFactory::getUser();
      $id = JRequest::getVar('cid', false);
	  
	  if (!$id) $id = array(JRequest::getVar('form_id'));
	  
      /* load the row from the db table */
      $row->load($id[0]);
	  
      if ($id[0]) {
         // do stuff for existing records
         $result = $row->checkout( $my->id );
      } 
	  else {
         // do stuff for new records
         $row->published    = 1;
      }
      return $row;
   }
	
   /**
    * Save a competition
    */
	function getSaveForm() 
  {
		global $mainframe;
    $row = $this->getTable();
	  $post = JRequest::get('post', 4);
	  
	  /* Get the posted data */
		if (!$row->bind($post)) {
			$mainframe->enqueueMessage(JText::_('There was a problem binding the form data'), 'error');
			return false;
		}
	  
		/* Convert the dates to MySQL dates */
		$row->startdate = $this->ConvertCalendarDate($row->startdate);
		$row->enddate = $this->ConvertCalendarDate($row->enddate);
		 
		 
		/* pre-save checks */
		if (!$row->check()) {
			$mainframe->enqueueMessage(JText::_('There was a problem checking the form data'), 'error');
			return false;
		}

		/* save the changes */
		if (!$row->store()) {
			$mainframe->enqueueMessage(JText::_('There was a problem storing the form data'), 'error');
			return false;
		}
		 
		$row->checkin();
		$mainframe->enqueueMessage(JText::_('The form has been saved'));

	 /* Add form table */
	 $this->AddFormTable($row->id);
	 
      return $row;
   }
   
   /**
    * Delete a competition
    */
   function getRemoveForm() {
      global $mainframe;
      $database = JFactory::getDBO();
      $cid = JRequest::getVar('cid');
      JArrayHelper::toInteger( $cid );
	  
      if (!is_array( $cid ) || count( $cid ) < 1) {
         $mainframe->enqueueMessage(JText::_('No form found to delete'));
         return false;
      }
      if (count($cid)) {
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
	private function AddFormTable($formid) {
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
	public function getVmInstalled() {
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
	public function getVmProducts() {
		$db = JFactory::getDBO();
		$q = "SELECT product_id, CONCAT(product_name, ' :: ', product_sku) AS product_name
			FROM #__vm_product
			ORDER BY product_name";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	public function getVmSettings()
	{
		$form = &$this->getDetails();
		if (!$form->virtuemartactive) {
			return false;
		}
		$res = new stdclass();
		$res->vmproductid = $form->vmproductid;
		$res->vmitemid    = $form->vmitemid;
		return $res;
	}
}
?>
