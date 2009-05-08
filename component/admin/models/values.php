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
class RedformModelValues extends JModel {
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
	function getValues() {
		/* Get all the orders based on the limits */
		$q = "SELECT a.*, q.field, CONCAT(c.formname, ' :: ', q.field) AS fieldname
				FROM #__rwf_values a, #__rwf_fields q, #__rwf_forms c
				WHERE a.field_id = q.id
				AND q.form_id = c.id ";
		$form_id = JRequest::getInt('form_id', false);
		if ($form_id) $q .= "AND c.id = ".$form_id." ";
		$q .= "ORDER BY c.formname, ordering";
		return $this->_getList($q, $this->_limitstart, $this->_limit);
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
			. "\n FROM #__rwf_values";
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}
	
	/**
    * Publish or Unpublish values
    */
   function getPublish() {
      global $mainframe;

      $cids = JRequest::getVar('cid');
      $task = JRequest::getCmd('task');
      $state = ($task == 'publish') ? 1 : 0;
      $user = &JFactory::getUser();
      $row =& $this->getTable();
	  
      if ($row->Publish($cids, $state, $user->id)) {
         if ($state == 1) $mainframe->enqueueMessage(JText::_('Values have been published'));
         else $mainframe->enqueueMessage(JText::_('Values have been unpublished'));
      }
      else {
         if ($state == 1) $mainframe->enqueueMessage(JText::_('Values could not be published'));
         else $mainframe->enqueueMessage(JText::_('Values could not be unpublished'));
      }
   }
   
   /**
    * Retrieve an value to edit
    */
   function getValue() {
      $row = $this->getTable();
      $my = JFactory::getUser();
      $id = JRequest::getVar('cid');

      /* load the row from the db table */
      $row->load($id[0]);

      if ($id[0]) {
         // do stuff for existing records
         $result = $row->checkout( $my->id );
      } else {
         // do stuff for new records
         $row->published    = 1;
      }
      return $row;
   }
   
   /**
    * Save an value
    */
   function getSaveValue() {
      global $mainframe;
      $row = $this->getTable();
	  
	  /* Get the posted data */
	  $post = JRequest::get('post');
	  $row->load($post['id']);
	  if (empty($row->ordering)) $post['ordering'] = $row->getNextOrder();
	  
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
	  
      $row->checkin();
      $row->reorder();
	  
	  /* Store the mailinglists */
	  if ($post['fieldtype'] == 'email') {
		 
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