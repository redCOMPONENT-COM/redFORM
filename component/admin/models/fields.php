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
	/** @var integer Total entries */
	protected $_total = null;
	
	/** @var integer pagination limit starter */
	protected $_limitstart = null;
	
	/** @var integer pagination limit */
	protected $_limit = null;
	   
	/**
	 * Show all fields that can be used for a value
	 */
	function getFields() {
		$db = JFactory::getDBO();
		$form_id = JRequest::getInt('form_id', false);
		
		/* Get all the fields based on the limits */
		$query = "SELECT q.*, c.formname, CONCAT(c.formname, ' :: ', q.field) AS fieldname
				FROM #__rwf_fields q, #__rwf_forms c
				WHERE q.form_id = c.id ";
		if ($form_id && $form_id > 0) {
			$query .= "AND c.id = ".$form_id." ";
		}
		$query .= "ORDER BY c.id, q.ordering";
		$db->setQuery($query, $this->_limitstart, $this->_limit);
		return $db->loadObjectList();
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
			. "\n FROM #__rwf_fields";
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}
	
	/**
    * Publish or Unpublish fields
    */
   function getPublish() {
      global $mainframe;

      $cids = JRequest::getVar('cid');
      $task = JRequest::getCmd('task');
      $state = ($task == 'publish') ? 1 : 0;
      $user = JFactory::getUser();
      $row = $this->getTable();
	  
      if ($row->Publish($cids, $state, $user->id)) {
         if ($state == 1) $mainframe->enqueueMessage(JText::_('Fields have been published'));
         else $mainframe->enqueueMessage(JText::_('Fields have been unpublished'));
      }
      else {
         if ($state == 1) $mainframe->enqueueMessage(JText::_('Fields could not be published'));
         else $mainframe->enqueueMessage(JText::_('Fields could not be unpublished'));
      }
   }
   
   /**
    * Retrieve a field to edit
    */
   function getField() {
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
    * Save a field
    */
   function getSaveField() {
      global $mainframe;
      $row = $this->getTable();
	 $oldrow = $this->getTable();
	 $field_id = JRequest::getInt('id', false);
	 /* Check if a field moved form */
	 if ($field_id)  {
		 $oldrow->load($field_id);
	 }
	 
	 /* Get the posted data */
	 $post = JRequest::get('post');
	 
	 /* Check field order */
	 $row->load($field_id);
	 if (empty($row->ordering)) $post['ordering'] = $row->getNextOrder();
	 
      if (!$row->bind($post)) {
         $mainframe->enqueueMessage(JText::_('There was a problem binding the field data'), 'error');
         return false;
      }
	  
      /* pre-save checks */
      if (!$row->check()) {
         $mainframe->enqueueMessage(JText::_('There was a problem checking the field data'), 'error');
         return false;
      }

      /* save the changes */
      if (!$row->store()) {
         $mainframe->enqueueMessage(JText::_('There was a problem storing the field data'), 'error');
         return false;
      }
	  
      $row->checkin();
      $mainframe->enqueueMessage(JText::_('The field has been saved'));
	 
	 /* Add form table */
	 $this->AddFieldTable($row, $oldrow);
	 
      return $row;
   }
   
   /**
    * Delete a field
    */
	function getRemoveField() {
		global $mainframe;
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		JArrayHelper::toInteger( $cid );
		
		if (!is_array( $cid ) || count( $cid ) < 1) {
			$mainframe->enqueueMessage(JText::_('No field found to delete'));
			return false;
		}
		
		if (count($cid)) {
			$cids = 'id=' . implode( ' OR id=', $cid );
			/* Check each field the form it belongs to and delete the column */
			$q = "SELECT field, form_id
				FROM #__rwf_fields
				WHERE ( $cids )";
			$db->setQuery($q);
			$fields = $db->loadObjectList();
			
			foreach ($fields as $key => $field) {
				$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$field->form_id)." DROP ".$db->nameQuote($field->field);
				$db->setQuery($q);
				if (!$db->query()) JError::raiseWarning('error', JText::_('Cannot remove field from old form').' '.$db->getErrorMsg());
			}
			
			/* Delete the fields */
			$query = "DELETE FROM #__rwf_fields"
			. "\n  WHERE ( $cids )";
			$db->setQuery( $query );
			if (!$db->query()) {
				$mainframe->enqueueMessage(JText::_('A problem occured when deleting the field'));
			}
			else {
				if (count($cid) > 1) $mainframe->enqueueMessage(JText::_('Fields have been deleted'));
				else $mainframe->enqueueMessage(JText::_('Field has been deleted'));
				
				/* Delete the values */
				$cids = 'field_id=' . implode( ' OR id=', $cid );
				$q = "DELETE FROM #__rwf_values
				WHERE ( $cids )";
				$db->setQuery($q);
				if (!$db->query()) {
					$mainframe->enqueueMessage(JText::_('A problem occured when deleting the field values'));
				}
				else {
					$mainframe->enqueueMessage(JText::_('Field values have been deleted'));
				}
			}
		}
	}
   
   /**
    * Reorder fields
	*/
	function getSaveOrder() {
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
	 * Adds a table if it doesn't exist yet
	 *
	 * @param object field table record object with updated value
	 * @param object previously recorded field table record object corresponding to current field id
	 */
	private function AddFieldTable($row, $oldrow) {
		$db = & JFactory::getDBO();
		/* Make sure that field name is valid */
		//TODO: couldn't this be done with just php ?
		$q = "SELECT REPLACE(LOWER(".$db->Quote($row->field)."), ' ', '')";
		$db->setQuery($q);
		$field = $db->loadResult();
		
		$q = "SELECT REPLACE(LOWER(".$db->Quote($oldrow->field)."), ' ', '')";
		$db->setQuery($q);
		$oldfield = $db->loadResult();
		
		/* Get columns from the active form */
		$q = "SHOW COLUMNS FROM ".$db->nameQuote($db->replacePrefix('#__rwf_forms_'.$row->form_id))." WHERE  ".$db->nameQuote('Field')." = ".$db->Quote($oldfield);
		$db->setQuery($q);
		$db->query();
		$result = $db->loadResult();
		
		/* Check if the name has changed */
		if ($result && $row->field != $oldrow->field) {
			$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$row->form_id)."
				CHANGE `".$oldfield."` `".$field."` TEXT";
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', JText::_('Cannot rename fieldname').' '.$db->getErrorMsg());
		}
		else {
			/* Check if the field already exists */
			if (!$result) {
				/* Field doesn't exist, need to create it */
				$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$row->form_id). " ADD `".$field."` TEXT NULL";
				$db->setQuery($q);
				if (!$db->query()) JError::raiseWarning('error', $db->getErrorMsg());
			}
		}
		
		/* Check if the field moved form */
		if ($oldrow->form_id && $row->form_id <> $oldrow->form_id) {
			$result = array();
			/* Check if the column exists on the old table */
			$q = "SHOW COLUMNS FROM ".$db->nameQuote($db->replacePrefix('#__rwf_forms_'.$oldrow->form_id))." WHERE  ".$db->nameQuote('Field')." = ".$db->Quote($field);
			$db->setQuery($q);
			$db->query();
			$result = $db->loadResult();
			
			/* Check if the field already exists */
			if ($result) {
				/* Drop the old column */
				$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$oldrow->form_id)." DROP ".$db->nameQuote($field);
				$db->setQuery($q);
				if (!$db->query()) JError::raiseWarning('error', JText::_('Cannot remove field from old form').' '.$db->getErrorMsg());
			}
		}
		
		/* Get indexes from the active form */
		$indexresult = null;
		$q = "SHOW KEYS FROM ".$db->nameQuote($db->replacePrefix('#__rwf_forms_'.$row->form_id))." WHERE key_name = ".$db->Quote($field);
		$db->setQuery($q);
		$db->query();
		$indexresult = $db->loadAssocList('Key_name');
		
		/* Check if the field has to be unique */
		$q = "ALTER TABLE ".$db->nameQuote('#__rwf_forms_'.$row->form_id);
		if ($row->unique && !isset($indexresult[$field])) {
			$q .= " ADD UNIQUE (`".$field."` (255))";
			$db->setQuery($q);
			if (!$db->query()) {
				JError::raiseWarning('error', JText::_('Cannot make the field unique').' '.$db->getErrorMsg());
				/* Remove unique status */
				$q = "UPDATE ".$db->nameQuote('#__rwf_fields')."
					SET ".$db->nameQuote('unique')." = 0
					WHERE id = ".$row->id;
				$db->setQuery($q);
				$db->query();
			}
		}
		else if (isset($indexresult[$field])) {
			$q .= " DROP INDEX `".$field."`";
			$db->setQuery($q);
			if (!$db->query()) JError::raiseWarning('error', JText::_('Cannot remove the field unique status').' '.$db->getErrorMsg());
		}
	}
	
	/**
	 * Add the field to the library
	 */
	public function getAddLibrary() {
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		$q = "INSERT INTO #__rwf_library_fields (`field`, `published`, `checked_out`, `checked_out_time`, `ordering`, `validate`, `unique`, `tooltip`)
			(SELECT `field`, `published`, 0 AS `checked_out`, `checked_out_time`, `ordering`, `validate`, `unique`, `tooltip` 
				FROM #__rwf_fields 
				WHERE id = ".$cid[0].")";
		$db->setQuery($q);
		if (!$db->query()) JError::raisewarning(0, $db->getErrorMsg());
		else {
			/* Add the field values */
			$q = "INSERT INTO #__rwf_library_values (`value`, `published`, `checked_out`, `checked_out_time`, `field_id`, `fieldtype`, `ordering`)
			(SELECT `value`, `published`, 0 AS `checked_out`, `checked_out_time`, ".$db->insertid()." AS `field_id`, `fieldtype`, `ordering` 
				FROM #__rwf_values 
				WHERE field_id = ".$cid[0].")";
			$db->setQuery($q);
			if (!$db->query()) JError::raisewarning(0, $db->getErrorMsg());
		}
	}
	
	/**
	 * Function to clean up the database of unused fields
	 */
	public function getSanitize() {
		$db = JFactory::getDBO();
		/* Get the form IDs */
		$q = "SELECT id FROM #__rwf_forms";
		$db->setQuery($q);
		$forms = $db->loadResultArray();
		
		/* Go through all the forms */
		foreach ($forms as $key => $form_id) {
			/* Load the list of fields used for this form */
			$fields = array();
			$q = "SELECT REPLACE(LOWER(field), ' ', '') AS field
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
		JError::raiseNotice(0, JText::_('SANITIZE_COMPLETE'));
	}
}
?>