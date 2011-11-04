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
			$integration = JRequest::getVar('integration', 0);
			
      if ($form_id && $form_id > 0) 
      {
				$db = JFactory::getDBO();
				
				$query = $this->_buildSubmittersQuery();
				
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
	
	function _buildSubmittersQuery()
	{
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
		$form_id = JRequest::getVar('form_id', 0);
		$integration = JRequest::getVar('integration', 0);
		
		$query =  ' SELECT s.submission_date, s.form_id, s.id AS sid, f.formname, u.*, s.price, s.submit_key, p.status, p.paid, s.integration, s.xref '
		        . ($integration === 'redevent' ? ', r.id as attendee_id ': '')				
						. ' FROM '.$this->_db->nameQuote('#__rwf_submitters').' AS s '
						. ' INNER JOIN ' . $this->_db->nameQuote('#__rwf_forms').' AS f ON s.form_id = f.id '
						. ' INNER JOIN ' . $this->_db->nameQuote('#__rwf_forms_'.$form_id) . ' AS u ON s.answer_id = u.id '
            . ' LEFT JOIN (SELECT MAX(id) as id, submit_key FROM #__rwf_payment GROUP BY submit_key) AS latest_payment ON latest_payment.submit_key = s.submit_key'
            . ' LEFT JOIN #__rwf_payment AS p ON p.id = latest_payment.id '
		        . ($integration === 'redevent' ? ' INNER JOIN #__redevent_register AS r ON r.submit_key = s.submit_key ': '')				
						;
						
		$where = array();
		$where[] = "s.form_id = ".$form_id;
		if ($xref && $xref > 0) {
			$where[] = "s.xref = ".$xref;
		}
		if (count($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		return $query;
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
	    $query = ' SELECT id, formname, activatepayment, currency FROM #__rwf_forms WHERE id = ' . $this->_db->Quote($id);
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
	function getSubmittersExport() 
	{
		$form_id = JRequest::getVar('form_id', false);
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false));
		
		$db = JFactory::getDBO();
		$query = ' SELECT s.submission_date, s.price, f.formname, u.*, s.price, p.status, p.paid '
		       . ' FROM #__rwf_submitters AS s '
		       . ' INNER JOIN #__rwf_forms AS f ON s.form_id = f.id '
		       . ' INNER JOIN #__rwf_forms_'.$form_id.' AS u ON s.answer_id = u.id '
		       . ' LEFT JOIN #__rwf_payment AS p ON p.submit_key = s.submit_key'
		       ;
		$where = array();
		if ($form_id && $form_id > 0) {
			$where[] = "s.form_id = ".$form_id;
		}
		if ($xref && $xref > 0) {
			$where[] = "s.xref = ".$xref;
		}
		if (count($where)) {
			$query .= ' WHERE '. implode(' AND ', $where);
		}
		$query .= " ORDER BY s.submission_date ASC ";
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getFields() 
	{
		$db = JFactory::getDBO();
		$form_id = JRequest::getInt('form_id', false);
		$query = ' SELECT f.id, f.field '
		       . '      , CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header '
		       . ' FROM #__rwf_fields AS f '
		       . ' WHERE f.fieldtype <> "info" '
		       ;		
		if ($form_id) $query .= ' AND form_id = ' . $db->Quote($form_id);
		$query .= "GROUP BY f.id
				ORDER BY f.ordering ";
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getPagination() 
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar('option');
		
		/* Lets load the pagination if it doesn't already exist */
		jimport('joomla.html.pagination');
		$this->_limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$this->_limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		$this->_pagination = new JPagination( $this->getTotal(), $this->_limitstart, $this->_limit );
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
  	if (!JRequest::getInt('form_id')) {
  		return 0;
  	}
    // Lets load the content if it doesn't already exist
    if (empty($this->_total))
    {
      $this->_total = $this->_getListCount($this->_buildSubmittersQuery());
    }

    return $this->_total;
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
	 * 
	 * @param array id of submitters records to delete
	 * @param boolean force deletion of integration rows
	 */
   function delete($cid, $force = false) 
   {
      $mainframe = JFactory::getApplication();
      $database = JFactory::getDBO();
      JArrayHelper::toInteger( $cid );
	  
      if (!is_array( $cid ) || count( $cid ) < 1) {
         $mainframe->enqueueMessage(JText::_('COM_REDFORM_No_submitter_found_to_delete'));
         return false;
      }
      
      if (count($cid)) 
      {
      	$cids = ' s.id IN (' . implode( ',', $cid ) .') ';

      	// first, check that there is no integration (xref is then > 0) among these 'submitter'
      	if (!$force)
      	{
	      	$query = ' SELECT COUNT(*) FROM #__rwf_submitters AS s WHERE ' . $cids . ' AND s.xref > 0 ';
	        $database->setQuery( $query );
	        $res = $database->loadResult();        
	        if ($res) 
	        {
	        	$msg = JText::_('COM_REDFORM_CANNOT_DELETE_REDEVENT_REGISTRATION');
	        	$this->setError($msg);
	        	JError::raiseWarning(0, $msg);
	        	return false;
	        }
      	}
      	
      	// first delete the answers
      	$query = ' DELETE a.* ' 
      	       . ' FROM #__rwf_submitters AS s '
      	       . ' INNER JOIN #__rwf_forms_'.JRequest::getInt('form_id').' AS a ON s.answer_id = a.id '
      	       . ' WHERE ' . $cids;
      	$this->_db->setQuery($query);
      	$res = $this->_db->loadObjectList();
      	
      	if (!$database->query()) 
      	{
      		$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_answers'));
          RedformHelperLog::simpleLog(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_answers') . ': ' . $database->getErrorMsg());
          return false;
      	}
      	
      	/* then delete the submitters */
      	$query = ' DELETE s.* FROM #__rwf_submitters AS s '
      	      . ' WHERE ' . $cids
      	      . '	AND s.form_id = '.JRequest::getInt('form_id');
      	$database->setQuery( $query );
      	
      	if (!$database->query()) 
      	{
      		$mainframe->enqueueMessage(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_submitter'));
          RedformHelperLog::simpleLog(JText::_('COM_REDFORM_A_problem_occured_when_deleting_the_submitter') . ': ' . $database->getErrorMsg());
          return false;
      	}
      	
      	if (count($cid) > 1) {
      		$mainframe->enqueueMessage(JText::_('COM_REDFORM_Submitters_have_been_deleted'));
      	}
      	else {
      		$mainframe->enqueueMessage(JText::_('COM_REDFORM_Submitter_has_been_deleted'));
      	}
      }
      return JText::_('COM_REDFORM_Removal_succesfull');
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
	 * Get the course
	 */
	public function getCourse() 
	{
		$db = JFactory::getDBO();
		$xref = JRequest::getVar('xref', JRequest::getVar('filter', false), 'int');
		
		if (!$xref) {
			return false;
		}
		$q =  ' SELECT e.title, v.venue, dates, enddates, times, endtimes, e.course_code '
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
		$course->course_title = $course_title;
		$course->uniqueid_prefix = $course->course_code.'-'.$xref.'-';
		return $course;
	}
}
?>
