<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.application.component.model' );

/**
 */
class RedformModelSubmitters extends JModel {
	
	protected $_data = null;
	
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
	function getSubmitters() 
	{
		if (empty($this->_data))
		{
			$form_id = JRequest::getVar('form_id', 0);
			$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
			
      if ($form_id && $form_id > 0) 
      {
				$db = JFactory::getDBO();
				
				$query =  ' SELECT s.submission_date, f.formname, u.* '
								. ' FROM '.$db->nameQuote('#__rwf_submitters').' AS s '
								. ' INNER JOIN ' . $db->nameQuote('#__rwf_forms').' AS f ON s.form_id = f.id '
								. ' INNER JOIN ' . $db->nameQuote('#__rwf_forms_'.$form_id) . ' AS u ON s.answer_id = u.id '
								;
								
				$where = array();
				$where[] = "s.form_id = ".$form_id;
				if ($xref && $xref > 0) {
					$where[] = "s.xref = ".$xref;
				}
				if (count($where)) {
					$query .= 'WHERE ' . implode(' AND ', $where);
				}
				
				$query .= " ORDER BY s.submission_date DESC ";
					
				$db->setQuery($query,  $this->_limitstart, $this->_limit);
				
				if ($db->getErrorNum() > 0) {
					$this->_data = array();
				}
				
				$this->_data = $db->loadObjectList();
      }
      else {
      	$this->_data = array();
      }
		}
		return $this->_data;
	}

  function getFormsOptions()
  {
    $query = "SELECT id AS value, formname AS text FROM #__rwf_forms";
    $this->_db->setQuery($query);
    return $this->_db->loadObjectList();
  }
  
  
  function getForm($id = null)
  {
  	if ($id == null) {
  		$id = JRequest::getInt('form_id', 0);
  	}
  	if ($id) {
	    $query = ' SELECT id, formname FROM #__rwf_forms WHERE id = ' . $this->_db->Quote($id);
	    $this->_db->setQuery($query);
	    return $this->_db->loadObject();  		
  	}
  	else {
  		return false;
  	}
  }
  
	/**
	 * Show all orders for which an invitation to fill in
	 * a testimonal has been sent
	 */
	function getSubmittersExport() {
		$form_id = JRequest::getVar('form_id', false);
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
		
		$db = JFactory::getDBO();
		$query = "SELECT s.submission_date, f.formname, u.*
			FROM ".$db->nameQuote('#__rwf_submitters')." s, ".$db->nameQuote('#__rwf_forms')." f, ".$db->nameQuote('#__rwf_forms_'.$form_id)." u
				WHERE s.form_id = f.id 
				AND s.answer_id = u.id ";
			if ($form_id && $form_id > 0) {
				$query .= "AND s.form_id = ".$form_id." ";
			}
			if ($xref && $xref > 0) {
				$query .= "AND s.xref = ".$xref." ";
			}
			$query .= "ORDER BY s.submission_date DESC";
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getFields() 
	{
		$db = JFactory::getDBO();
		$form_id = JRequest::getInt('form_id', false);
		$query = ' SELECT f.id, f.field '
		       . ' FROM #__rwf_fields AS f '
		       . ' INNER JOIN #__rwf_values AS v ON v.field_id = f.id '
		       . ' WHERE v.fieldtype <> "info" '
		       ;		
		if ($form_id) $query .= ' AND form_id = ' . $db->Quote($form_id);
		$query .= "GROUP BY f.id
				ORDER BY f.ordering ";
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getPagination() {
		global $mainframe, $option;
		
		/* Lets load the pagination if it doesn't already exist */
		jimport('joomla.html.pagination');
		$this->_limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$this->_limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$this->_pagination = new JPagination( $this->getTotal(), $this->_limitstart, $this->_limit );
		return $this->_pagination;
	}
	
	/**
	 * Method to get the total number of testimonial items for the category
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal() {
		$form_id = JRequest::getVar('form_id', false);
		$xref = JRequest::getVar('xref', false);
		$filter = JRequest::getVar('filter', false);
		$db = JFactory::getDBO();
		
		if ($form_id && $form_id > 0) {
			$query = "SELECT s.submission_date, f.formname, u.*
			FROM ".$db->nameQuote('#__rwf_submitters')." s, ".$db->nameQuote('#__rwf_forms')." f, ".$db->nameQuote('#__rwf_forms_'.$form_id)." u
				WHERE s.form_id = f.id 
				AND s.answer_id = u.id ";
			if ($form_id) {
				$query .= "AND s.form_id = ".$form_id." ";
			}
			if ($xref) {
				$query .= "AND s.xref = ".$xref." ";
			}
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0) return 0;
			else return $db->getAffectedRows();
		}
	}
	
	/**
	 * Get the number of people signed up for the newsletter
	 */
	public function getNewsletterSignup() {
		$db = JFactory::getDBO();
		$cid = JRequest::getVar('cid');
		$query = "SELECT COUNT(id)
				FROM #__rwf_submitters
				WHERE form_id = ".$cid[0]."
				AND submitternewsletter = 1";
		$db->setQuery($query);
		if ($db->getErrorNum() > 0) return 0;
		else return $db->loadResult();
	}
	
	/**
    * Deletes one or more submitters
    */
   function delete($cid) 
   {
      global $mainframe;
      $database = JFactory::getDBO();
      JArrayHelper::toInteger( $cid );
	  
      if (!is_array( $cid ) || count( $cid ) < 1) {
         $mainframe->enqueueMessage(JText::_('No submitter found to delete'));
         return false;
      }
      if (count($cid)) {
      	$cids = ' answer_id IN (' . implode( ',', $cid ) .') ';

      	// first, check that there is no eventlist registrations among these 'submitter'
      	$query = ' SELECT COUNT(*) FROM #__rwf_submitters WHERE ' . $cids . ' AND xref > 0 ';
        $database->setQuery( $query );
        $res = $database->loadResult();        
        if ($res) {
        	$msg = JText::_('CANNOT DELETE REDEVENT REGISTRATION');
        	$this->setError($msg);
        	JError::raiseWarning(0, $msg);
        	return false;
        }
      	
      	/* Delete the submitters */
      	$query = ' DELETE FROM #__rwf_submitters '
      	      . ' WHERE ' . $cids
      	      . '	AND form_id = '.JRequest::getInt('form_id');
      	$database->setQuery( $query );
      	
      	if (!$database->query()) {
      		$mainframe->enqueueMessage(JText::_('A problem occured when deleting the submitter'));
          RedformHelperLog::simpleLog(JText::_('A problem occured when deleting the submitter') . ': ' . $database->getErrorMsg());
      	}
      	else {
      		/* Delete the submitters values */
      		$cids = 'id=' . implode( ' OR id=', $cid );
      		$query = "DELETE from #__rwf_forms_".JRequest::getInt('form_id')."
					WHERE (".$cids.")";
      		$database->setQuery($query);
      		if (!$database->query()) {
      			$mainframe->enqueueMessage(JText::_('A problem occured when deleting the submitter values'));
            RedformHelperLog::simpleLog(JText::_('A problem occured when deleting the submitter values') . ': ' . $database->getErrorMsg());
      		}
      		else {
      			if (count($cid) > 1) {
      				$mainframe->enqueueMessage(JText::_('Submitters have been deleted'));
      			}
      			else {
      				$mainframe->enqueueMessage(JText::_('Submitter has been deleted'));
      			}
      		}
      	}
      }
      return JText::_('Removal succesfull');
   	}
	
	/**
	 * Get the value for the submitter field
	 */
	private function QuestionValue(&$value_id) {
		$db = JFactory::getDBO();
		$q = "SELECT value
			FROM #__rwf_values
			WHERE id = ".$value_id;
		$db->setQuery($q);
		return $db->loadResult();
	}
		
	/**
	 * Get the course title
	 */
	public function getCourseTitle() 
	{
		$db = JFactory::getDBO();
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
		
		if (!$xref) {
			return false;
		}
		$q =  ' SELECT e.title, v.venue, dates, enddates, times, endtimes '
		    . ' FROM #__redevent_event_venue_xref x '
		    . '	LEFT JOIN #__redevent_events e ON e.id = x.eventid '
		    . ' LEFT JOIN #__redevent_venues v ON v.id = x.venueid '
		    . ' WHERE x.id = '.$xref
		    ;
		$db->setQuery($q);
		$course = $db->loadObject();
		$course_title = $course->title . ' / ' . $course->venue . ' / ' . $course->dates;
    if ($course->times) {
      $course_title .= ' ' . $course->times;
    }
    if ($course->enddates || $course->endtimes) {
      $course_title .= ' -';
    }
		if ($course->enddates) {
			$course_title .= ' ' . $course->enddates;
		}
    if ($course->endtimes) {
      $course_title .= ' ' . $course->endtimes;
    }
		return $course_title;
	}
}
?>
